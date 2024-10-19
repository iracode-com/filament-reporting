<?php

namespace IracodeCom\FilamentReporting\Resources;

use IracodeCom\FilamentReporting\Resources\ReportResource\Pages;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use IracodeCom\FilamentReporting\Models\Report;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon                  = 'heroicon-o-document-text';


    public static function getLabel(): ?string
    {
        return __('Reporting');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Reporting');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('reportable_resource')->hint(new HtmlString(Blade::render('<x-filament::loading-indicator wire:loading class="h-5 w-5"/>')))->label(__('Module'))->options(function () {
                    $models = Arr::map(Filament::getResources(), fn($resource) => Arr::prepend([], app($resource)::getModel()));
                    $models = Arr::flatten($models);
                    $names  = Arr::map($models, fn($model) => __(str($model)->afterLast('\\')->value()));
                    return array_combine(Filament::getResources(), $names);
                })->afterStateUpdated(fn(Forms\Set $set, $state) => $state ? $set('reportable_type', app($state)::getModel()) : null)->searchable()->preload()->reactive(),
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Hidden::make('reportable_type')->required(),
                Forms\Components\Hidden::make('created_by')->formatStateUsing(fn(?Model $record) => $record->created_by ?? auth()->id()),
                Forms\Components\Hidden::make('updated_by')->formatStateUsing(fn() => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('reportable_type')->label(__('Model'))->searchable(),
                // Tables\Columns\TextColumn::make('reportable_resource')->label(__('Module'))->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('createdBy.name')->label(__('Creator'))->sortable()
                    // ->url(fn(Model $record) => UserResource::getUrl('edit', ['record' => $record->created_by]))
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updatedBy.name')->label(__('Updater'))->sortable()
                    // ->url(fn(Model $record) => UserResource::getUrl('edit', ['record' => $record->updated_by]))
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')->jalaliDateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->jalaliDateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')->jalaliDateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('reporting')
                    ->icon('heroicon-o-document-chart-bar')
                    ->url(fn(?Model $record) => static::getUrl('reporting', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'     => Pages\ListReports::route('/'),
            'reporting' => Pages\Reporting::route('/{record}/reporting'),
            // 'create' => Pages\CreateReport::route('/create'),
            // 'edit'   => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
