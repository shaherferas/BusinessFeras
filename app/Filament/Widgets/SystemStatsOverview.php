<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\ModerationReport;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pending = Business::query()->where('approval_status', 'pending')->count();

        return [
            Stat::make(__('filament.widgets.total_active_users'), User::query()->count()),
            Stat::make(__('filament.widgets.pending_business_approvals'), $pending)->color($pending > 0 ? 'warning' : 'success'),
            Stat::make(__('filament.widgets.unresolved_moderation_reports'), ModerationReport::query()->whereIn('status', ['open', 'reviewed'])->count())->color('danger'),
        ];
    }
}
