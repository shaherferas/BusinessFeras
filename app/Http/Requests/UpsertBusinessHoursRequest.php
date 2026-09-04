<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpsertBusinessHoursRequest extends FormRequest { public function authorize(): bool{return $this->user()?->is_business_owner && $this->user()->hasRole('Business Owner');} public function rules(): array{return ['hours'=>['required','array','size:7'],'hours.*.day_of_week'=>['required','integer','between:0,6','distinct'],'hours.*.is_closed'=>['required','boolean'],'hours.*.opens_at'=>['nullable','date_format:H:i'],'hours.*.closes_at'=>['nullable','date_format:H:i']];} }
