<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Concerns\RespondsWithApi; use App\Http\Controllers\Controller; use App\Models\Business; use Illuminate\Http\Request;
class ReviewController extends Controller { use RespondsWithApi; public function store(Request $r,Business $business){abort_unless($business->active()->whereKey($business->id)->exists(),404);$data=$r->validate(['rating'=>['required','integer','between:1,5'],'comment'=>['nullable','string','max:2000']]);$review=$business->reviews()->updateOrCreate(['user_id'=>$r->user()->id],$data+['moderation_status'=>'approved']);return $this->success($review->load('user:id,name,avatar_url'),'Created successfully',201);} }
