<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpsertBusinessSocialLinksRequest extends FormRequest { public function authorize(): bool{return $this->user()?->is_business_owner && $this->user()->hasRole('Business Owner');} public function rules(): array{return ['links'=>['required','array','max:20'],'links.*.platform'=>['required','in:facebook,instagram,x,linkedin,youtube,tiktok,website,whatsapp','distinct'],'links.*.url'=>['required','url','max:2048'],'links.*.sort_order'=>['nullable','integer','min:0']];} }
