<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\MediaPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    use RespondsWithApi;

    public function reels(Request $request)
    {
        $data = $request->validate(['type' => ['nullable', 'in:reel,post,story'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:50']]);
        $posts = MediaPost::query()->with('business:id,name,slug')->where('moderation_status', 'approved')
            ->whereHas('business', fn ($query) => $query->active()->where('approval_status', 'approved'))
            ->when($data['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('id')->cursorPaginate($data['per_page'] ?? 15);

        return $this->success($posts->items(), 'Success', 200, ['next_cursor' => optional($posts->nextCursor())->encode(), 'prev_cursor' => optional($posts->previousCursor())->encode(), 'per_page' => $posts->perPage()]);
    }

    public function toggleLike(Request $request, MediaPost $mediaPost)
    {
        abort_unless($mediaPost->moderation_status === 'approved' && ! $mediaPost->trashed(), 404);
        $result = DB::transaction(function () use ($request, $mediaPost) {
            $like = $mediaPost->likes()->where('user_id', $request->user()->id)->first();
            if ($like) { $like->delete(); $mediaPost->decrement('likes_count'); return false; }
            $mediaPost->likes()->create(['user_id' => $request->user()->id]); $mediaPost->increment('likes_count'); return true;
        });
        return $this->success(['liked' => $result, 'likes_count' => $mediaPost->fresh()->likes_count]);
    }

    public function comment(Request $request, MediaPost $mediaPost)
    {
        abort_unless($mediaPost->moderation_status === 'approved' && ! $mediaPost->trashed(), 404);
        $data = $request->validate(['content' => ['required', 'string', 'max:2000']]);
        $comment = DB::transaction(function () use ($request, $mediaPost, $data) { $comment = $mediaPost->comments()->create(['user_id' => $request->user()->id, 'content' => $data['content']]); $mediaPost->increment('comments_count'); return $comment; });
        return $this->success($comment->load('user:id,name,avatar_url'), 'Created successfully', 201);
    }
}
