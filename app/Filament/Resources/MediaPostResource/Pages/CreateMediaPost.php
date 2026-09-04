<?php

namespace App\Filament\Resources\MediaPostResource\Pages;

use App\Filament\Resources\MediaPostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaPost extends CreateRecord
{
    protected static string $resource = MediaPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}