<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('filament.users');
    }

    public static function getModelLabel(): string
    {
        return __('filament.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.name'))
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label(__('filament.email'))
                    ->email(),
                Forms\Components\TextInput::make('phone_number')
                    ->label(__('filament.phone_number'))
                    ->tel()
                    ->required(),
                Forms\Components\DateTimePicker::make('whatsapp_verified_at')
                    ->label('WhatsApp Verified At'),
                Forms\Components\TextInput::make('password')
                    ->label(__('filament.password'))
                    ->password()
                    ->required(),
                Forms\Components\TextInput::make('avatar_url')
                    ->label('Avatar URL'),
                Forms\Components\Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Forms\Components\Toggle::make('is_business_owner')
                    ->label('Business Owner')
                    ->required(),
                Forms\Components\Select::make('active_mode')
                    ->label('Active Mode')
                    ->options(['user'=>'User','business'=>'Business'])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('filament.phone_number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp_verified_at')
                    ->label('WhatsApp Verified At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avatar_url')
                    ->label('Avatar URL')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_business_owner')
                    ->label('Business Owner')
                    ->boolean(),
                Tables\Columns\TextColumn::make('active_mode')
                    ->label('Active Mode')
                    ->searchable(),
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
                Tables\Filters\TernaryFilter::make('is_business_owner')
                    ->label('Business Owner'),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->relationship('roles','name'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
