<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessSocialLinkResource\Pages;
use App\Models\BusinessSocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessSocialLinkResource extends Resource
{
    protected static ?string $model = BusinessSocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function getNavigationLabel(): string
    {
        return __('filament.social_links');
    }

    public static function getModelLabel(): string
    {
        return __('filament.social_link');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.social_links');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\BelongsToSelect::make('business_id')
                ->label(__('filament.business'))
                ->relationship('business', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('platform')
                ->label(__('filament.platform'))
                ->options([
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram',
                    'x' => 'X / Twitter',
                    'linkedin' => 'LinkedIn',
                    'youtube' => 'YouTube',
                    'tiktok' => 'TikTok',
                    'website' => 'Website',
                    'whatsapp' => 'WhatsApp',
                ])
                ->required(),
            Forms\Components\TextInput::make('url')
                ->label(__('filament.url'))
                ->url()
                ->required()
                ->maxLength(2048),
            Forms\Components\TextInput::make('sort_order')
                ->label(__('filament.sort_order'))
                ->numeric()
                ->default(0)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('platform')
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label(__('filament.business'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform')
                    ->label(__('filament.platform'))
                    ->badge(),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('filament.url'))
                    ->limit(60)
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('filament.sort_order'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->label(__('filament.platform'))
                    ->options([
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'x' => 'X / Twitter',
                        'linkedin' => 'LinkedIn',
                        'youtube' => 'YouTube',
                        'tiktok' => 'TikTok',
                        'website' => 'Website',
                        'whatsapp' => 'WhatsApp',
                    ]),
                Tables\Filters\SelectFilter::make('business_id')
                    ->label(__('filament.business'))
                    ->relationship('business', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessSocialLinks::route('/'),
            'create' => Pages\CreateBusinessSocialLink::route('/create'),
            'edit' => Pages\EditBusinessSocialLink::route('/{record}/edit'),
        ];
    }
}