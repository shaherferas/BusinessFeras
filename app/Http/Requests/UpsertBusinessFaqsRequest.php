<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpsertBusinessFaqsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_business_owner && $this->user()->hasRole('Business Owner');
    }

    public function rules(): array
    {
        return [
            'faqs' => ['required', 'array', 'max:50'],
            'faqs.*.id' => ['nullable', 'integer'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.question_translations' => ['nullable', 'array'],
            'faqs.*.question_translations.*' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string'],
            'faqs.*.answer_translations' => ['nullable', 'array'],
            'faqs.*.answer_translations.*' => ['nullable', 'string'],
            'faqs.*.sort_order' => ['nullable', 'integer', 'min:0']
        ];
    }
}
