<?php

namespace App\Filament\Resources\BusinessSocialLinkResource\Pages;

use App\Filament\Resources\BusinessSocialLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessSocialLink extends EditRecord
{
    protected static string $resource = BusinessSocialLinkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}