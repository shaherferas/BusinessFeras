<?php
namespace App\Http\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class UserResource extends JsonResource { public function toArray(Request $request): array { return ['id'=>$this->id,'name'=>$this->name,'email'=>$this->email,'phone_number'=>$this->phone_number,'whatsapp_verified_at'=>$this->whatsapp_verified_at,'avatar_url'=>$this->avatar_url,'is_business_owner'=>$this->is_business_owner,'active_mode'=>$this->active_mode,'roles'=>$this->whenLoaded('roles', fn()=>$this->getRoleNames())]; } }
