<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AmenityResource\Pages;
use App\Models\Amenity;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AmenityResource extends Resource
{
    protected static ?string $model = Amenity::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    public static function getNavigationLabel(): string
    {
        return __('filament.amenities');
    }

    public static function getModelLabel(): string
    {
        return __('filament.amenity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.amenities');
    }

    public static function form(Form $form): Form { return $form->schema([
        Forms\Components\TextInput::make('name')
            ->label(__('filament.name'))
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
        Forms\Components\TextInput::make('slug')
            ->label(__('filament.slug'))
            ->required()
            ->unique(ignoreRecord: true),
        Forms\Components\FileUpload::make('icon')
            ->label(__('filament.icon'))
            ->image()
            ->directory('amenity-icons'),
        Forms\Components\Toggle::make('is_active')
            ->label(__('filament.is_active'))
            ->default(true)
            ->required(),
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
    ]); }

    public static function table(Table $table): Table { return $table->columns([
        Tables\Columns\ImageColumn::make('icon')
            ->label(__('filament.icon'))
            ->circular(),
        Tables\Columns\TextColumn::make('name')
            ->label(__('filament.name'))
            ->searchable()
            ->sortable()
            ->formatStateUsing(function ($record) {
                $locale = app()->getLocale();
                return $record->getTranslation('name_translations', $locale) ?: $record->name;
            }),
        Tables\Columns\TextColumn::make('slug')
            ->label(__('filament.slug'))
            ->searchable(),
        Tables\Columns\IconColumn::make('is_active')
            ->label(__('filament.is_active'))
            ->boolean(),
    ])->filters([
        Tables\Filters\TernaryFilter::make('is_active')
            ->label(__('filament.is_active'))
    ])->actions([
        Tables\Actions\EditAction::make()->label(__('filament.edit')),
        Tables\Actions\DeleteAction::make()->label(__('filament.delete'))
    ])->bulkActions([
        Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make()->label(__('filament.delete'))
        ])
    ]); }
    public static function getPages(): array { return ['index' => Pages\ListAmenities::route('/'), 'create' => Pages\CreateAmenity::route('/create'), 'edit' => Pages\EditAmenity::route('/{record}/edit')]; }
}
