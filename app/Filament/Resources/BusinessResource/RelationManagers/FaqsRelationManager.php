<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FaqsRelationManager extends RelationManager
{
    protected static string $relationship = 'faqs';
    protected static ?string $title = 'FAQs';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('question')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('answer')->required()->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->required(),

            Forms\Components\Section::make('Translations')
                ->description('Add translations for this FAQ in different languages')
                ->schema([
                    Forms\Components\KeyValue::make('question_translations')
                        ->label('Question Translations')
                        ->keyLabel('Language Code')
                        ->valueLabel('Translated Question')
                        ->default(['en' => '', 'ar' => ''])
                        ->addActionLabel('Add Translation')
                        ->reorderable(false)
                        ->helperText('Add question translations using language codes (en, ar)')
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('answer_translations')
                        ->label('Answer Translations')
                        ->keyLabel('Language Code')
                        ->valueLabel('Translated Answer')
                        ->default(['en' => '', 'ar' => ''])
                        ->addActionLabel('Add Translation')
                        ->reorderable(false)
                        ->helperText('Add answer translations using language codes (en, ar)')
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('question')->columns([
            Tables\Columns\TextColumn::make('question')
                ->searchable()
                ->limit(60)
                ->formatStateUsing(function ($record) {
                    $locale = app()->getLocale();
                    return $record->getTranslation('question', $locale);
                }),
            Tables\Columns\TextColumn::make('answer')
                ->limit(40)
                ->formatStateUsing(function ($record) {
                    $locale = app()->getLocale();
                    return $record->getTranslation('answer_translations', $locale);
                }),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
