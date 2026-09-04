<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\ModerationReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReportResource extends Resource
{
    protected static ?string $model = ModerationReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';

    public static function getNavigationLabel(): string
    {
        return __('filament.reports');
    }

    public static function getModelLabel(): string
    {
        return __('filament.report');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.reports');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reportable_type')
                ->label(__('filament.reportable_type'))
                ->required()
                ->disabledOn('edit'),
            Forms\Components\TextInput::make('reportable_id')
                ->label(__('filament.reportable_id'))
                ->numeric()
                ->required()
                ->disabledOn('edit'),
            Forms\Components\Select::make('reported_by_user_id')
                ->label(__('filament.reporter'))
                ->relationship('reporter', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Textarea::make('reason')
                ->label(__('filament.reason'))
                ->required()
                ->maxLength(2000)
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->label(__('filament.status'))
                ->options([
                    'open' => __('filament.pending'),
                    'reviewed' => __('filament.reviewed'),
                    'resolved' => __('filament.resolved')
                ])
                ->default('open')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reportable_type')
                ->label(__('filament.content_type'))
                ->formatStateUsing(fn (?string $state) => class_basename($state))
                ->searchable(),
            Tables\Columns\TextColumn::make('reportable_id')
                ->label(__('filament.content_id'))
                ->sortable(),
            Tables\Columns\TextColumn::make('reporter.name')
                ->label(__('filament.reporter'))
                ->searchable(),
            Tables\Columns\TextColumn::make('reason')
                ->label(__('filament.reason'))
                ->limit(60)
                ->wrap(),
            Tables\Columns\TextColumn::make('status')
                ->label(__('filament.status'))
                ->badge()
                ->formatStateUsing(fn (string $state) => $state === 'open' ? __('filament.pending') : ucfirst($state))
                ->color(fn (string $state) => match ($state) {
                    'resolved' => 'success', 'reviewed' => 'warning', default => 'danger'
                }),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('filament.created_at'))
                ->dateTime()
                ->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')
                ->label(__('filament.status'))
                ->options([
                    'open' => __('filament.pending'),
                    'reviewed' => __('filament.reviewed'),
                    'resolved' => __('filament.resolved')
                ]),
            Tables\Filters\SelectFilter::make('reportable_type')
                ->label(__('filament.content_type'))
                ->options([
                    'App\\Models\\Business' => __('filament.business'),
                    'App\\Models\\MediaPost' => __('filament.media_post'),
                    'App\\Models\\Review' => __('filament.review')
                ]),
        ])->actions([
            Action::make('resolveKeep')
                ->label(__('filament.dismiss_report'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (ModerationReport $record) => $record->status !== 'resolved')
                ->requiresConfirmation()
                ->action(fn (ModerationReport $record) => DB::transaction(fn () => $record->update(['status' => 'resolved']))),
            Action::make('resolveRemove')
                ->label(__('filament.remove_content'))
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->visible(fn (ModerationReport $record) => $record->status !== 'resolved')
                ->requiresConfirmation()
                ->modalDescription(__('filament.remove_content_description'))
                ->action(function (ModerationReport $record): void {
                    DB::transaction(function () use ($record): void {
                        $content = $record->reportable;
                        if ($content instanceof Model) {
                            if (method_exists($content, 'delete')) $content->delete();
                            elseif (in_array('moderation_status', $content->getFillable(), true)) $content->update(['moderation_status' => 'hidden']);
                        }
                        $record->update(['status' => 'resolved']);
                    });
                }),
            Tables\Actions\EditAction::make()->label(__('filament.edit')),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label(__('filament.delete'))
            ])
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('reporter.name')->label('Reporter'),
            Infolists\Components\TextEntry::make('reason')->columnSpanFull(),
            Infolists\Components\TextEntry::make('reportable_type')->formatStateUsing(fn (?string $state) => class_basename($state)),
            Infolists\Components\TextEntry::make('reportable.caption')->label('Reported text')->placeholder('—')->columnSpanFull(),
            Infolists\Components\ImageEntry::make('reportable.file_path')->label('Reported image/video thumbnail')->disk('public')->visible(fn (ModerationReport $record) => $record->reportable instanceof \App\Models\MediaPost && !str_ends_with(strtolower((string) $record->reportable->file_path), '.mp4')),
            Infolists\Components\TextEntry::make('reportable.file_path')->label('Video file')->url(fn (ModerationReport $record) => $record->reportable instanceof \App\Models\MediaPost ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->reportable->file_path) : null)->openUrlInNewTab()->visible(fn (ModerationReport $record) => $record->reportable instanceof \App\Models\MediaPost && str_ends_with(strtolower((string) $record->reportable->file_path), '.mp4')),
        ])->columns(2);
    }

    public static function getPages(): array { return ['index' => Pages\ListReports::route('/'), 'create' => Pages\CreateReport::route('/create'), 'edit' => Pages\EditReport::route('/{record}/edit')]; }
}
