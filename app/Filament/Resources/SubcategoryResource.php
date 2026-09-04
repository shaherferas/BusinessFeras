<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubcategoryResource\Pages;
use App\Filament\Resources\SubcategoryResource\RelationManagers;
use App\Models\Subcategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubcategoryResource extends Resource
{
    protected static ?string $model = Subcategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('filament.subcategories');
    }

    public static function getModelLabel(): string
    {
        return __('filament.subcategory');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.subcategories');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('filament.category'))
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.name'))
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->label(__('filament.slug'))
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament.is_active'))
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('filament.category'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->searchable()
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament.is_active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('filament.edit')),
                Tables\Actions\DeleteAction::make()->label(__('filament.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('filament.delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubcategories::route('/'),
            'create' => Pages\CreateSubcategory::route('/create'),
            'edit' => Pages\EditSubcategory::route('/{record}/edit'),
        ];
    }
}
