<?php

namespace App\Filament\Pages;

use App\Models\Business;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;

class BusinessRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.business-requests';

    public static function getNavigationLabel(): string
    {
        return __('filament.business_requests');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.business_management');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Business::query()->with(['user', 'category'])->whereNotNull('approval_status'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.owner'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('filament.category'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('filament.phone_number'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label(__('filament.approval_status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success', 'rejected' => 'danger', default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.requested_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label(__('filament.request_status'))
                    ->options([
                        'pending' => __('filament.pending'),
                        'approved' => __('filament.approved'),
                        'rejected' => __('filament.rejected'),
                    ])->default('pending'),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('filament.category'))
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('filament.approve'))
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Business $record) => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.approve_business_request'))
                    ->action(fn (Business $record) => $record->update(['approval_status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null])),
                Tables\Actions\Action::make('reject')
                    ->label(__('filament.reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (Business $record) => $record->approval_status === 'pending')
                    ->modalHeading(__('filament.reject_business_request'))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label(__('filament.rejection_reason'))
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(fn (Business $record, array $data) => $record->update(['approval_status' => 'rejected', 'rejection_reason' => $data['rejection_reason'], 'approved_at' => null])),
                Tables\Actions\ViewAction::make()
                    ->label(__('filament.view'))
                    ->url(fn (Business $record) => \App\Filament\Resources\BusinessResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
