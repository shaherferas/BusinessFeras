<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Filament\Resources\BusinessResource\RelationManagers;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function getNavigationLabel(): string
    {
        return __('filament.businesses');
    }

    public static function getModelLabel(): string
    {
        return __('filament.businesses');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.businesses');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label(__('filament.owner'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label(__('filament.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->label(__('filament.description'))
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('category_id')
                ->label(__('filament.category'))
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('phone_number')
                ->label(__('filament.phone_number'))
                ->tel()
                ->required()
                ->maxLength(30),
            Forms\Components\TextInput::make('whatsapp_number')
                ->label(__('filament.whatsapp_number'))
                ->tel()
                ->maxLength(30),
            Forms\Components\TextInput::make('latitude')
                ->label(__('filament.latitude'))
                ->required(),
            Forms\Components\TextInput::make('longitude')
                ->label(__('filament.longitude'))
                ->required(),
            Forms\Components\View::make('location_picker')
                ->view('filament.forms.components.leaflet-location-picker')
                ->dehydrated(false)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('address_text')
                ->label(__('filament.address_text'))
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('approval_status')
                ->label(__('filament.approval_status'))
                ->options([
                    'pending' => __('filament.pending'),
                    'approved' => __('filament.approved'),
                    'rejected' => __('filament.rejected')
                ])
                ->default('pending')
                ->required()
                ->live(),
            Forms\Components\Textarea::make('rejection_reason')
                ->label(__('filament.rejection_reason'))
                ->visible(fn (Forms\Get $get) => $get('approval_status') === 'rejected')
                ->required(fn (Forms\Get $get) => $get('approval_status') === 'rejected')
                ->columnSpanFull(),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label(__('filament.expires_at'))
                ->seconds(false),

            Forms\Components\Section::make(__('filament.translations'))
                ->description(__('filament.add_translations_help'))
                ->schema([
                    Forms\Components\KeyValue::make('name_translations')
                        ->label(__('filament.name_translations'))
                        ->keyLabel(__('filament.language_code'))
                        ->valueLabel(__('filament.translated_name'))
                        ->default(['en' => '', 'ar' => ''])
                        ->addActionLabel(__('filament.add_translation'))
                        ->reorderable(false)
                        ->helperText('Add translations using language codes (en, ar)')
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(false),
        ])->columns(2);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('name'), Infolists\Components\TextEntry::make('user.name')->label('Owner'),
            Infolists\Components\TextEntry::make('category.name'), Infolists\Components\TextEntry::make('approval_status')->badge(),
            Infolists\Components\TextEntry::make('rejection_reason')->placeholder('—')->columnSpanFull(),
            Infolists\Components\TextEntry::make('approved_at')->dateTime()->placeholder('—'), Infolists\Components\TextEntry::make('expires_at')->dateTime()->placeholder('—'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('filament.name'))
                ->searchable()
                ->sortable()
                ->formatStateUsing(function ($record) {
                    $locale = app()->getLocale();
                    return $record->getTranslation('name_translations', $locale) ?: $record->name;
                }),
            Tables\Columns\TextColumn::make('description')
                ->label(__('filament.description'))
                ->limit(50)
                ->formatStateUsing(function ($record) {
                    $locale = app()->getLocale();
                    return $record->getTranslation('description', $locale) ?: $record->description;
                })
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('user.name')
                ->label(__('filament.owner'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('category.name')
                ->label(__('filament.category'))
                ->searchable()
                ->sortable()
                ->formatStateUsing(function ($record) {
                    $locale = app()->getLocale();
                    return $record->category?->getTranslation('name_translations', $locale) ?: $record->category?->name;
                }),
            Tables\Columns\TextColumn::make('approval_status')
                ->label(__('filament.approval_status'))
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning'
                }),
            Tables\Columns\TextColumn::make('expires_at')
                ->label(__('filament.expires_at'))
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('approved_at')
                ->label(__('filament.approved_at'))
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\SelectFilter::make('approval_status')
                ->label(__('filament.approval_status'))
                ->options([
                    'pending' => __('filament.pending'),
                    'approved' => __('filament.approved'),
                    'rejected' => __('filament.rejected')
                ]),
            Tables\Filters\SelectFilter::make('category')
                ->label(__('filament.category'))
                ->relationship('category', 'name'),
        ])->actions([
            Action::make('approve')
                ->label(__('filament.approve'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (Business $record) => $record->approval_status !== 'approved')
                ->requiresConfirmation()
                ->action(fn (Business $record) => $record->update(['approval_status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null])),
            Action::make('reject')
                ->label(__('filament.reject'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (Business $record) => $record->approval_status !== 'rejected')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label(__('filament.rejection_reason'))
                        ->required()
                        ->maxLength(2000)
                ])
                ->action(fn (Business $record, array $data) => $record->update(['approval_status' => 'rejected', 'rejection_reason' => $data['rejection_reason'], 'approved_at' => null])),
            Action::make('overrideExpiry')
                ->label(__('filament.override_expiry'))
                ->icon('heroicon-o-calendar-days')
                ->form([
                    Forms\Components\DatePicker::make('expires_at')
                        ->label(__('filament.expires_at'))
                        ->required()
                ])
                ->action(fn (Business $record, array $data) => $record->update(['expires_at' => $data['expires_at']])),
            Tables\Actions\ViewAction::make()->label(__('filament.view')),
            Tables\Actions\EditAction::make()->label(__('filament.edit')),
        ])->bulkActions([Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make()->label(__('filament.delete'))
        ])]);
    }

    public static function getRelations(): array { return [RelationManagers\BusinessHoursRelationManager::class, RelationManagers\MediaPostsRelationManager::class, RelationManagers\FaqsRelationManager::class, RelationManagers\SocialLinksRelationManager::class, RelationManagers\ReviewsRelationManager::class]; }
    public static function getPages(): array { return ['index' => Pages\ListBusinesses::route('/'), 'create' => Pages\CreateBusiness::route('/create'), 'view' => Pages\ViewBusiness::route('/{record}'), 'edit' => Pages\EditBusiness::route('/{record}/edit')]; }
}
