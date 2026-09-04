<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'socialLinks';
    protected static ?string $title = 'Social Links';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')->options(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X / Twitter', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'website' => 'Website', 'whatsapp' => 'WhatsApp'])->required(),
            Forms\Components\TextInput::make('url')->url()->required()->maxLength(2048),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('platform')->columns([
            Tables\Columns\TextColumn::make('platform')->badge(),
            Tables\Columns\TextColumn::make('url')->limit(60)->url(fn ($record) => $record->url)->openUrlInNewTab(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
