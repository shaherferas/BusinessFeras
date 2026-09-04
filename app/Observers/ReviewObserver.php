<?php
namespace App\Observers;
use App\Models\Review;
class ReviewObserver { public function saved(Review $review): void {$this->recalculate($review);} public function deleted(Review $review): void {$this->recalculate($review);} public function restored(Review $review): void {$this->recalculate($review);} private function recalculate(Review $review): void {$review->business()->update(['average_rating'=>$review->business->reviews()->where('moderation_status','approved')->avg('rating') ?? 0]);} }
