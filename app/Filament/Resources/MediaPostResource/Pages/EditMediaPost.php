<?php

namespace App\Filament\Resources\MediaPostResource\Pages;

use App\Filament\Resources\MediaPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMediaPost extends EditRecord
{
    protected static string $resource = MediaPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}