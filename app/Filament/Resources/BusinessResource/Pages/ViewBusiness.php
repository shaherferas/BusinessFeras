<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ViewBusiness extends ViewRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label(__('filament.approve'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->approval_status !== 'approved')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update([
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'rejection_reason' => null,
                ])),
            Actions\Action::make('reject')
                ->label(__('filament.reject'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn () => $this->record->approval_status !== 'rejected')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label(__('filament.rejection_reason'))
                        ->required()
                        ->maxLength(2000),
                ])
                ->action(fn (array $data) => $this->record->update([
                    'approval_status' => 'rejected',
                    'rejection_reason' => $data['rejection_reason'],
                    'approved_at' => null,
                ])),
            Actions\Action::make('overrideExpiry')
                ->label(__('filament.override_expiry'))
                ->icon('heroicon-o-calendar-days')
                ->form([
                    Forms\Components\DatePicker::make('expires_at')
                        ->label(__('filament.expires_at'))
                        ->required(),
                ])
                ->action(fn (array $data) => $this->record->update(['expires_at' => $data['expires_at']])),
            Actions\Action::make('changeOwnerLogin')
                ->label(__('filament.change_owner_login'))
                ->icon('heroicon-o-user-circle')
                ->color('warning')
                ->visible(fn () => $this->record->user !== null)
                ->modalHeading(__('filament.change_owner_login'))
                ->modalDescription(fn () => __('filament.change_owner_login_for', [
                    'name' => $this->record->user->name,
                ]))
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label(__('filament.email'))
                        ->email()
                        ->maxLength(255)
                        ->rule(fn () => Rule::unique('users', 'email')->ignore($this->record->user?->id))
                        ->placeholder(fn () => $this->record->user?->email),
                    Forms\Components\TextInput::make('phone_number')
                        ->label(__('filament.phone_number'))
                        ->tel()
                        ->maxLength(30)
                        ->rule(fn () => Rule::unique('users', 'phone_number')->ignore($this->record->user?->id))
                        ->placeholder(fn () => $this->record->user?->phone_number),
                    Forms\Components\TextInput::make('password')
                        ->label(__('filament.new_password'))
                        ->password()
                        ->revealable()
                        ->minLength(8)
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText(__('filament.leave_blank_to_keep')),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->label(__('filament.confirm_password'))
                        ->password()
                        ->revealable()
                        ->dehydrated(false)
                        ->visible(fn (Forms\Get $get) => filled($get('password')))
                        ->same('password'),
                ])
                ->action(function (array $data) {
                    $owner = $this->record->user;
                    if (! $owner) {
                        return;
                    }

                    $updates = array_filter([
                        'email' => $data['email'] ?? null,
                        'phone_number' => $data['phone_number'] ?? null,
                    ], fn ($value) => filled($value));

                    if (filled($data['password'] ?? null)) {
                        $updates['password'] = Hash::make($data['password']);
                    }

                    if (empty($updates)) {
                        Notification::make()
                            ->title(__('filament.no_changes_made'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $owner->update($updates);

                    Notification::make()
                        ->title(__('filament.owner_login_updated'))
                        ->success()
                        ->send();
                }),
            Actions\EditAction::make(),
        ];
    }
}
