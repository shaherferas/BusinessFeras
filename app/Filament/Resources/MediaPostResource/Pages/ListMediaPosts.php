<?php

namespace App\Filament\Resources\MediaPostResource\Pages;

use App\Filament\Resources\MediaPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediaPosts extends ListRecords
{
    protected static string $resource = MediaPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}