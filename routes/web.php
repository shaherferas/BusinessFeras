<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/admin/switch-locale', function (Request $request) {
    $locale = $request->input('locale', 'en');

    if (in_array($locale, ['en', 'ar'])) {
        session(['filament_locale' => $locale]);
    }

    return redirect()->back();
})->name('filament.admin.switch-locale')->middleware(['web']);
