<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaPostResource\Pages;
use App\Models\MediaPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MediaPostResource extends Resource
{
    protected static ?string $model = MediaPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function getNavigationLabel(): string
    {
        return __('filament.media');
    }

    public static function getModelLabel(): string
    {
        return __('filament.media_post');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.media_posts');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\BelongsToSelect::make('business_id')
                ->label(__('filament.business'))
                ->relationship('business', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('type')
                ->label(__('filament.media_type'))
                ->options([
                    'reel' => __('filament.reel'),
                    'story' => __('filament.story'),
                    'post' => __('filament.post'),
                ])
                ->required(),
            Forms\Components\FileUpload::make('file_path')
                ->label(__('filament.file'))
                ->image()
                ->directory('media-posts')
                ->maxSize(10240) // 10MB
                ->required(),
            Forms\Components\FileUpload::make('thumbnail_path')
                ->label(__('filament.thumbnail'))
                ->image()
                ->directory('media-thumbnails')
                ->maxSize(1024) // 1MB
                ->required(),
            Forms\Components\Textarea::make('caption')
                ->label(__('filament.caption'))
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('moderation_status')
                ->label(__('filament.moderation_status'))
                ->options([
                    'pending' => __('filament.pending'),
                    'approved' => __('filament.approved'),
                    'hidden' => __('filament.hidden'),
                ])
                ->required()
                ->default('pending'),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label(__('filament.expires_at'))
                ->seconds(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label(__('filament.preview'))
                    ->disk(config('filesystems.default'))
                    ->height(60)
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->defaultImageUrl(fn ($record) => $record->file_path ? Storage::disk(config('filesystems.default'))->url($record->file_path) : null),
                Tables\Columns\TextColumn::make('business.name')
                    ->label(__('filament.business'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.media_type'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'reel' => 'info',
                        'story' => 'warning',
                        'post' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => __("filament.{$state}")),
                Tables\Columns\TextColumn::make('caption')
                    ->label(__('filament.caption'))
                    ->limit(60)
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('moderation_status')
                    ->label(__('filament.moderation_status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'hidden' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => __("filament.{$state}")),
                Tables\Columns\TextColumn::make('likes_count')
                    ->label(__('filament.likes'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label(__('filament.comments'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label(__('filament.views'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('filament.expires_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('filament.media_type'))
                    ->options([
                        'reel' => __('filament.reel'),
                        'story' => __('filament.story'),
                        'post' => __('filament.post'),
                    ]),
                Tables\Filters\SelectFilter::make('moderation_status')
                    ->label(__('filament.moderation_status'))
                    ->options([
                        'pending' => __('filament.pending'),
                        'approved' => __('filament.approved'),
                        'hidden' => __('filament.hidden'),
                    ]),
                Tables\Filters\SelectFilter::make('business_id')
                    ->label(__('filament.business'))
                    ->relationship('business', 'name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('summary')
                    ->label(__('filament.media_summary'))
                    ->icon('heroicon-o-chart-bar')
                    ->modalContent(fn () => view('filament.partials.media-summary', [
                        'counts' => $this->getOwnerRecord()
                            ->mediaPosts()
                            ->selectRaw('type, COUNT(*) as total')
                            ->groupBy('type')
                            ->pluck('total', 'type'),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('filament.close')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('filament.edit')),
                Tables\Actions\DeleteAction::make()->label(__('filament.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('filament.delete')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaPosts::route('/'),
            'create' => Pages\CreateMediaPost::route('/create'),
            'edit' => Pages\EditMediaPost::route('/{record}/edit'),
        ];
    }
}