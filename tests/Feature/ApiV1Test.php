<?php

namespace Tests\Feature;

use App\Events\BusinessExpired;
use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['Super Admin', 'Business Owner', 'End User'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_email_registration_creates_user_and_sends_otp_response(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Aly',
            'email' => 'aly@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])
            ->assertCreated()
            ->assertJsonPath('status', 201)
            ->assertJsonPath('message', 'OTP sent via email');

        $this->assertDatabaseHas('users', ['email' => 'aly@example.com']);
    }

    public function test_email_login_sends_otp_response(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('Secret123'),
        ]);
        $user->assignRole('End User');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@example.com',
            'password' => 'Secret123',
        ])
            ->assertStatus(201)
            ->assertJsonPath('message', 'OTP sent via email');
    }

    public function test_email_otp_verification_returns_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $user->assignRole('End User');

        Cache::put('email_otp:login:user@example.com', hash('sha256', '123456'), now()->addMinutes(5));

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'user@example.com',
            'code' => '123456',
            'purpose' => 'login',
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_owner_can_only_manage_own_businesses(): void
    {
        $owner = User::factory()->create(['is_business_owner' => true]);
        $owner->assignRole('Business Owner');

        $other = User::factory()->create(['is_business_owner' => true]);
        $other->assignRole('Business Owner');

        $category = Category::create(['name' => 'Food', 'slug' => 'food', 'is_active' => true]);

        $business = Business::create([
            'user_id' => $other->id,
            'name' => 'Other',
            'slug' => 'other',
            'phone_number' => '+201000000111',
            'category_id' => $category->id,
            'latitude' => 1,
            'longitude' => 1,
            'address_text' => 'Address',
        ]);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/business/businesses/{$business->id}")
            ->assertForbidden();
    }

    public function test_expiry_command_expires_business_and_dispatches_event(): void
    {
        Event::fake([BusinessExpired::class]);

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Retail', 'slug' => 'retail', 'is_active' => true]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Expired',
            'slug' => 'expired',
            'phone_number' => '+201000000333',
            'category_id' => $category->id,
            'latitude' => 1,
            'longitude' => 1,
            'address_text' => 'Address',
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('business:check-expirations')
            ->assertSuccessful();

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'expired',
        ]);

        Event::assertDispatched(BusinessExpired::class);
    }

    public function test_public_listings_only_include_approved_active_businesses(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Health', 'slug' => 'health', 'is_active' => true]);

        Business::create(['user_id' => $user->id, 'name' => 'Approved', 'slug' => 'approved', 'phone_number' => '+201000000444', 'category_id' => $category->id, 'latitude' => 33.5, 'longitude' => 36.2, 'address_text' => 'A', 'approval_status' => 'approved']);
        Business::create(['user_id' => $user->id, 'name' => 'Pending', 'slug' => 'pending', 'phone_number' => '+201000000445', 'category_id' => $category->id, 'latitude' => 33.5, 'longitude' => 36.2, 'address_text' => 'A', 'approval_status' => 'pending']);

        $this->getJson('/api/v1/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Approved');
    }
}
