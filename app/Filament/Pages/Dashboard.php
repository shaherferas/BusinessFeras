<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = '';
    protected static ?string $navigationLabel = '';

    public static function getNavigationLabel(): string
    {
        return __('filament.dashboard');
    }

    public function getTitle(): string
    {
        return __('filament.dashboard');
    }

    public function getHeading(): string
    {
        return __('filament.dashboard');
    }
}
