<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessHoursRelationManager extends RelationManager
{
    protected static string $relationship = 'businessHours';
    protected static ?string $title = 'Business Hours';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('day_of_week')->options([0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'])->required(),
            Forms\Components\Toggle::make('is_closed')->live(),
            Forms\Components\TimePicker::make('opens_at')->seconds(false)->required(fn (Forms\Get $get) => ! $get('is_closed')),
            Forms\Components\TimePicker::make('closes_at')->seconds(false)->required(fn (Forms\Get $get) => ! $get('is_closed')),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('day_of_week')->columns([
            Tables\Columns\TextColumn::make('day_of_week')->formatStateUsing(fn (int $state) => [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'][$state]),
            Tables\Columns\TextColumn::make('opens_at')->time('H:i')->placeholder('—'),
            Tables\Columns\TextColumn::make('closes_at')->time('H:i')->placeholder('—'),
            Tables\Columns\IconColumn::make('is_closed')->boolean(),
        ])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
