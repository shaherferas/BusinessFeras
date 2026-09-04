<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->getTranslation('name_translations', app()->getLocale()),
            'slug' => $this->slug,
            'description' => $this->getTranslation('description', app()->getLocale()),
            'phone_number' => $this->phone_number,
            'whatsapp_number' => $this->whatsapp_number,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address_text' => $this->address_text,
            'expires_at' => $this->expires_at,
            'status' => $this->status,
            'average_rating' => $this->average_rating,
            'approval_status' => $this->approval_status,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'subcategory' => SubcategoryResource::make($this->whenLoaded('subcategory')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'faqs' => BusinessFaqResource::collection($this->whenLoaded('faqs')),
        ];
    }
}
