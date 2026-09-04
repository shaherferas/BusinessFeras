<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\Interaction;
use App\Models\MediaPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    use RespondsWithApi;

    private function owner(Request $request): void
    {
        abort_unless($request->user()->is_business_owner && $request->user()->hasRole('Business Owner'), 403, 'Business owner access is required.');
    }

    private function rules(bool $updating = false): array
    {
        return ['name' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'], 'slug' => [$updating ? 'sometimes' : 'required', 'string', 'max:255', 'unique:businesses,slug'], 'description' => 'nullable|string', 'phone_number' => [$updating ? 'sometimes' : 'required', 'string', 'max:30'], 'whatsapp_number' => 'nullable|string|max:30', 'category_id' => [$updating ? 'sometimes' : 'required', 'exists:categories,id'], 'subcategory_id' => 'nullable|exists:subcategories,id', 'latitude' => [$updating ? 'sometimes' : 'required'], 'longitude' => [$updating ? 'sometimes' : 'required'], 'address_text' => [$updating ? 'sometimes' : 'required', 'string'], 'expires_at' => 'nullable|date', 'status' => 'sometimes|in:active,expired,suspended'];
    }

    public function index(Request $r)
    {
        $this->owner($r);
        $items = $r->user()->businesses()->with('category')->latest()->paginate($r->integer('per_page', 15));

        return $this->success(BusinessResource::collection($items->items()), 'Success', 200, ['current_page' => $items->currentPage(), 'per_page' => $items->perPage(), 'total' => $items->total(), 'total_pages' => $items->lastPage()]);
    }

    public function store(Request $r)
    {
        $this->owner($r);
        $data = $r->validate($this->rules());
        $data['user_id'] = $r->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $business = Business::create($data);

        return $this->success(BusinessResource::make($business->load('category', 'subcategory')), 'Created successfully', 201);
    }

    public function update(Request $r, Business $business)
    {
        $this->owner($r);
        abort_unless($business->user_id === $r->user()->id, 403);
        $data = $r->validate($this->rules(true));
        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }$business->update($data);

        return $this->success(BusinessResource::make($business->fresh(['category', 'subcategory'])));
    }

    public function destroy(Request $r, Business $business)
    {
        $this->owner($r);
        abort_unless($business->user_id === $r->user()->id, 403);
        $business->delete();

        return $this->success(null, 'Business deleted successfully');
    }

    public function dashboard(Request $r)
    {
        $this->owner($r);
        $businesses = $r->user()->businesses();
        $ids = $businesses->pluck('id');
        $postTotals = MediaPost::whereIn('business_id', $ids)->selectRaw('COALESCE(SUM(views_count),0) views, COALESCE(SUM(likes_count + comments_count),0) engagement')->first();
        $signals = Interaction::whereIn('business_id', $ids)->count();
        $first = $businesses->orderBy('expires_at')->first();

        return $this->success(['package' => ['expires_at' => $first?->expires_at], 'stats' => ['media_views' => (int) $postTotals->views, 'listings_count' => $ids->count(), 'open_chats' => 0, 'call_map_chat_taps' => $signals, 'engagement' => (int) $postTotals->engagement], 'chart' => ['months' => collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->values(), 'values' => collect(range(5, 0))->map(fn ($i) => MediaPost::whereIn('business_id', $ids)->whereMonth('created_at', now()->subMonths($i)->month)->sum('views_count'))->values()]]);
    }

    public function mediaIndex(Request $r)
    {
        $this->owner($r);
        $items = MediaPost::whereIn('business_id', $r->user()->businesses()->pluck('id'))->when($r->query('type'), fn ($q, $type) => $q->where('type', $type))->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->latest()->paginate($r->integer('per_page', 15));

        return $this->success($items->items(), 'Success', 200, ['current_page' => $items->currentPage(), 'per_page' => $items->perPage(), 'total' => $items->total(), 'total_pages' => $items->lastPage()]);
    }

    public function mediaStore(Request $r)
    {
        $this->owner($r);
        $data = $r->validate(['business_id' => 'required|exists:businesses,id', 'type' => 'required|in:reel,story,post', 'file' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:51200', 'caption' => 'nullable|string|max:5000']);
        $business = $r->user()->businesses()->findOrFail($data['business_id']);
        $path = $r->file('file')->store('media-posts', 'public');
        $post = $business->mediaPosts()->create(['type' => $data['type'], 'file_path' => $path, 'caption' => $data['caption'] ?? null, 'moderation_status' => 'pending', 'expires_at' => $data['type'] === 'story' ? now()->addDay() : null]);

        return $this->success($post, 'Created successfully', 201);
    }

    public function mediaDestroy(Request $r, MediaPost $mediaPost)
    {
        $this->owner($r);
        abort_unless($r->user()->businesses()->whereKey($mediaPost->business_id)->exists(), 403);
        $mediaPost->delete();

        return $this->success(null,'Media deleted successfully');
    }
}
