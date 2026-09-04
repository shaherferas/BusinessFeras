<?php

namespace App\Filament\Resources\BusinessSocialLinkResource\Pages;

use App\Filament\Resources\BusinessSocialLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessSocialLinks extends ListRecords
{
    protected static string $resource = BusinessSocialLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}