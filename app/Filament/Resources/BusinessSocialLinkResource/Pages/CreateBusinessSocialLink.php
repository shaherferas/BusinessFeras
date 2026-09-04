<?php

namespace App\Filament\Resources\BusinessSocialLinkResource\Pages;

use App\Filament\Resources\BusinessSocialLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessSocialLink extends CreateRecord
{
    protected static string $resource = BusinessSocialLinkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}