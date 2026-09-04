<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessListingRequest;
use App\Http\Requests\UpsertBusinessFaqsRequest;
use App\Http\Requests\UpsertBusinessHoursRequest;
use App\Http\Requests\UpsertBusinessSocialLinksRequest;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\BusinessFaqResource;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{
    use RespondsWithApi;

    private function owned(Request $request, Business $business): Business
    {
        abort_unless($business->user_id === $request->user()->id, 403, 'You do not own this business.');
        return $business;
    }

    public function store(StoreBusinessListingRequest $request)
    {
        $data = $request->validated();
        $amenities = $data['amenity_ids'] ?? [];
        unset($data['amenity_ids']);
        $data['user_id'] = $request->user()->id;
        $data['approval_status'] = 'pending';
        $data['approved_at'] = null;
        $data['rejection_reason'] = null;

        $business = DB::transaction(function () use ($data, $amenities) {
            $business = Business::create($data);
            $business->amenities()->sync($amenities);
            return $business;
        });

        return $this->success(BusinessResource::make($business->load(['category', 'subcategory', 'amenities'])), 'Listing submitted for approval.', 201);
    }

    public function upsertHours(UpsertBusinessHoursRequest $request, Business $business)
    {
        $business = $this->owned($request, $business);
        DB::transaction(function () use ($business, $request) {
            foreach ($request->validated('hours') as $hour) {
                $business->businessHours()->updateOrCreate(['day_of_week' => $hour['day_of_week']], $hour);
            }
        });
        return $this->success($business->businessHours()->orderBy('day_of_week')->get());
    }

    public function upsertFaqs(UpsertBusinessFaqsRequest $request, Business $business)
    {
        $business = $this->owned($request, $business);
        DB::transaction(function () use ($business, $request) {
            $kept = [];
            foreach ($request->validated('faqs') as $faq) {
                $record = $business->faqs()->updateOrCreate(['id' => $faq['id'] ?? null], $faq);
                $kept[] = $record->id;
            }
            $business->faqs()->whereNotIn('id', $kept)->delete();
        });
        return $this->success(BusinessFaqResource::collection($business->faqs()->get()));
    }

    public function upsertSocialLinks(UpsertBusinessSocialLinksRequest $request, Business $business)
    {
        $business = $this->owned($request, $business);
        DB::transaction(function () use ($business, $request) {
            $business->socialLinks()->delete();
            $business->socialLinks()->createMany($request->validated('links'));
        });
        return $this->success($business->socialLinks()->get());
    }

    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:255'], 'category_id' => ['nullable', 'integer', 'exists:categories,id'], 'latitude' => ['nullable', 'numeric', 'required_with:longitude,radius_km'], 'longitude' => ['nullable', 'numeric', 'required_with:latitude,radius_km'], 'radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:200'], 'sort' => ['nullable', 'in:distance,rating,newest'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $query = Business::query()->active()->where('approval_status', 'approved')->with('category')->when($data['q'] ?? null, fn ($q, $term) => $q->where(fn ($nested) => $nested->where('name', 'like', "%{$term}%")->orWhere('address_text', 'like', "%{$term}%")))->when($data['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id));
        if (isset($data['latitude'], $data['longitude'], $data['radius_km'])) $query->withinRadius((float) $data['latitude'], (float) $data['longitude'], (float) $data['radius_km']);
        match ($data['sort'] ?? 'newest') {'rating' => $query->orderByDesc('average_rating'), default => $query->latest()};
        $items = $query->paginate($data['per_page'] ?? 15);
        return $this->success(BusinessResource::collection($items->items()), 'Success', 200, ['current_page' => $items->currentPage(), 'per_page' => $items->perPage(), 'total' => $items->total(), 'total_pages' => $items->lastPage()]);
    }

    public function map(Request $request)
    {
        $data = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric'], 'radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:200'], 'category_id' => ['nullable', 'integer', 'exists:categories,id']]);
        $items = Business::query()->active()->where('approval_status', 'approved')->with('category')->withinRadius((float) $data['latitude'], (float) $data['longitude'], (float) ($data['radius_km'] ?? 10))->when($data['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))->get(['id', 'name', 'slug', 'category_id', 'latitude', 'longitude', 'address_text', 'average_rating']);
        return $this->success(BusinessResource::collection($items));
    }
}
