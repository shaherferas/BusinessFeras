<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BusinessAnalyticsController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('toggle-role', [AuthController::class, 'toggleRole']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
Route::prefix('v1/business')->middleware('auth:sanctum')->group(function () {
    Route::get('analytics', BusinessAnalyticsController::class);
    Route::get('dashboard', [BusinessController::class, 'dashboard']);
    Route::get('businesses', [BusinessController::class, 'index']);
    Route::post('businesses', [BusinessController::class, 'store']);
    Route::put('businesses/{business}', [BusinessController::class, 'update']);
    Route::delete('businesses/{business}', [BusinessController::class, 'destroy']);
    Route::get('media', [BusinessController::class, 'mediaIndex']);
    Route::post('media', [BusinessController::class, 'mediaStore']);
    Route::delete('media/{mediaPost}',[BusinessController::class, 'mediaDestroy']);
});

Route::get('v1/listings', [ListingController::class, 'index']);
Route::get('v1/listings/map', [ListingController::class, 'map']);
Route::get('v1/social/reels', [SocialController::class, 'reels']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('v1/social/{mediaPost}/like', [SocialController::class, 'toggleLike']);
    Route::post('v1/social/{mediaPost}/comments', [SocialController::class, 'comment']);
    Route::get('v1/chats', [ChatController::class, 'index']);
    Route::get('v1/chats/{conversation}', [ChatController::class, 'show']);
    Route::post('v1/listings/{business}/reviews', [ReviewController::class, 'store']);
});
Route::prefix('v1/business')->middleware('auth:sanctum')->group(function () {
    Route::post('listings', [ListingController::class, 'store']);
    Route::put('listings/{business}/hours', [ListingController::class, 'upsertHours']);
    Route::put('listings/{business}/faqs', [ListingController::class, 'upsertFaqs']);
    Route::put('listings/{business}/social-links', [ListingController::class, 'upsertSocialLinks']);
});
