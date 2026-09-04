<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationLabel(): string
    {
        return __('filament.categories');
    }

    public static function getModelLabel(): string
    {
        return __('filament.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.categories');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('filament.name'))
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')
                ->label(__('filament.slug'))
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Forms\Components\FileUpload::make('icon')
                ->label(__('filament.icon'))
                ->image()
                ->directory('category-icons')
                ->imageEditor(),
            Forms\Components\Select::make('parent_id')
                ->label(__('filament.parent'))
                ->relationship('parent', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Toggle::make('is_active')
                ->label(__('filament.is_active'))
                ->default(true)
                ->required(),
            Forms\Components\Section::make(__('filament.translations'))
                ->description('Add translations for this category in different languages')
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
        return $table->columns([
            Tables\Columns\ImageColumn::make('icon')->circular(),
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
            Tables\Columns\TextColumn::make('parent.name')
                ->label(__('filament.parent'))
                ->placeholder('Root')
                ->sortable(),
            Tables\Columns\IconColumn::make('is_active')
                ->label(__('filament.is_active'))
                ->boolean()
                ->sortable(),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('filament.updated_at'))
                ->dateTime()
                ->sortable(),
        ])->filters([
            Tables\Filters\TernaryFilter::make('is_active')->label(__('filament.is_active')),
            Tables\Filters\SelectFilter::make('parent_id')->relationship('parent', 'name')->label(__('filament.parent')),
        ])->actions([
            Tables\Actions\EditAction::make()->label(__('filament.edit')),
            Tables\Actions\DeleteAction::make()->label(__('filament.delete'))
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label(__('filament.delete'))
            ])
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListCategories::route('/'), 'create' => Pages\CreateCategory::route('/create'), 'edit' => Pages\EditCategory::route('/{record}/edit')];
    }
}
