<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessListingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->is_business_owner && $this->user()->hasRole('Business Owner'); }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:businesses,slug'],
            'name_translations' => ['nullable', 'array'],
            'name_translations.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'address_text' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
            'amenity_ids' => ['sometimes', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id']
        ];
    }
}
