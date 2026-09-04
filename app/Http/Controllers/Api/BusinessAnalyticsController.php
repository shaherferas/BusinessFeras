<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessAnalyticsController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->is_business_owner || ! $user->hasRole('Business Owner')) {
            return $this->error('Business owner access is required.', 403);
        }
        $businessIds = Business::query()->where('user_id', $user->id)->pluck('id');
        $topAssets = DB::table('media_posts')->whereIn('business_id', $businessIds)->select(['id', 'business_id', 'type', 'file_path', 'thumbnail_path', 'caption', 'likes_count', 'comments_count', 'views_count'])->orderByDesc('likes_count')->orderByDesc('comments_count')->orderByDesc('views_count')->limit(5)->get();
        $engagement = DB::table('post_engagements')->join('media_posts', 'post_engagements.media_post_id', '=', 'media_posts.id')->whereIn('media_posts.business_id', $businessIds)->where('post_engagements.created_at', '>=', now()->subDays(29)->startOfDay())->selectRaw('DATE(post_engagements.created_at) as date, SUM(CASE WHEN post_engagements.action_type = \'like\' THEN 1 ELSE 0 END) as likes, SUM(CASE WHEN post_engagements.action_type = \'comment\' THEN 1 ELSE 0 END) as comments')->groupBy('date')->orderBy('date')->get();
        $signals = DB::table('interactions')->whereIn('business_id', $businessIds)->selectRaw('action_type, COUNT(*) as count')->groupBy('action_type')->pluck('count', 'action_type');

        return $this->success(['top_performing_assets' => $topAssets, 'engagement_horizon' => $engagement, 'audience_action_signals' => ['call_click' => (int) ($signals['call_click'] ?? 0), 'map_click' => (int) ($signals['map_click'] ?? 0), 'message_click' => (int) ($signals['message_click'] ?? 0)]]);
    }
}
