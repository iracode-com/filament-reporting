<?php

namespace IracodeCom\FilamentReporting\Resources\ReportResource\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\SelectAction;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Illuminate\Support\Arr;
use IracodeCom\FilamentReporting\Exports\ReportingExport;
use IracodeCom\FilamentReporting\Models\Report;
use IracodeCom\FilamentReporting\Resources\ReportResource;
use IracodeCom\FilamentReporting\Support\Utils;

class Reporting extends Page implements HasForms, HasTable, HasInfolists
{
    use InteractsWithForms,
        InteractsWithTable,
        InteractsWithInfolists;

    public ?array       $data      = [];
    public static array $models    = [];
    public Report       $record;
    public string       $model;
    public string       $activeTab = 'filtering';

    protected static string $resource = ReportResource::class;
    protected static string $view     = 'filament-reporting::pages.reporting';


    public function getTitle(): string|Htmlable
    {
        return __('Filtering');
    }

    public static function getModels(): array
    {
        $resources = Filament::getResources();
        $models    = Arr::map($resources, fn($resource) => Arr::prepend([], app($resource)::getModel()));
        $models    = Arr::flatten($models);
        $names     = Arr::map($models, fn($model) => __(str($model)->afterLast('\\')->value()));

        return array_combine($models, $names);
    }

    public function mount(Report $record): void
    {
        $this->record = $record;
        $this->model  = $record->reportable_type;
        $this->form->fill($record->toArray());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->model::query())
            ->columns($this->getColumns())
            ->groups($this->getGroupColumns())
            ->filters([
                $this->getQueryBuilderFilter()
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                SelectAction::make('model')->options(static::getModels()),
                Action::make('create')->action(fn() => $this->submitFilters()),
                Action::make('export')
                    ->label(__('Export'))
                    ->color('success')
                    ->action(function () {
                        if ($this->activeTab == 'preview') {
                            return (new ReportingExport($this->record))->download();
                        }
                        return Notification::make()->title('برای گفتن خروجی وارد تب پیشنمایش شوید.')->danger()->send();
                    })
                    ->icon('heroicon-o-arrow-up-tray'),
            ])
            ->defaultGroup($this->getDefaultGroupingRow())
            ->checkIfRecordIsSelectableUsing(fn() => $this->activeTab == 'filtering')
            ->persistFiltersInSession()
            ->deferFilters()
            ->deferLoading()
            ->defaultPaginationPageOption(25);
    }

    public function getDefaultGroupingRow()
    {
        return $this->record->grouping_rows
            ? Group::make($this->record->grouping_rows)
            : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('تنظیمات خروجی')->schema([
                    Select::make('font')->label('فونت')
                        ->options([
                            'iransans'  => 'ایران سنس',
                            'yekanbakh' => 'یکان باخ',
                            'iranyekan' => 'ایران یکان',
                            'bnazanin'  => 'بی نازنین',
                            'vazir'     => 'وزیر'
                        ])->default('iransans'),
                    Select::make('export_type')->label('نوع خروجی')
                        ->options([
                            'xlsx'  => 'اکسل XLSX',
                            'xls'   => 'اکسل XLS',
                            'csv'   => 'CSV',
                            'pdf'   => 'PDF',
                            'chart' => 'نمودار'
                        ])->default('xlsx'),
                    Fieldset::make('اطلاعات هدر')->schema([
                        DatePicker::make('report_date')->label('تاریخ گزارش')->jalali()->prefixIcon('heroicon-o-calendar'),
                        FileUpload::make('logo')->image()->imageEditor(),
                        Textarea::make('header_description')->label('متن دلخواه')->columnSpanFull(),
                    ]),
                    Fieldset::make('اطلاعات فوتر')->schema([
                        Textarea::make('footer_description')->label('متن دلخواه')->columnSpanFull(),
                    ]),
                ])->columns()
            ])
            ->statePath('data');
    }

    public function getColumns(): array
    {
        $columns       = Utils::getModelColumns($this->model);
        $relationships = Utils::getModelRelationships($this->model);
        $modelItems    = ['columns' => $columns, 'relationships' => $relationships];

        $queryBuilder = [];
        foreach ($modelItems['columns'] as $item) {
            $column = TextColumn::make($item['name'])->toggleable();

            if ($item['type_name'] == 'json') {
                continue;
            }

            if (in_array($item['name'], Utils::getForeignKeys($this->model))) {
                continue;
            }

            $queryBuilder[] = match ($item['type_name']) {
                'int', 'bigint', 'smallint', 'mediumint', 'decimal', 'float', 'real', 'double' => $column->numeric(),
                'varchar', 'text', 'char', 'tinytext', 'mediumtext', 'longtext'                => $column->searchable(),
                'date', 'datetime', 'timestamp', 'time', 'year'                                => $column->jalaliDateTime(),
                default                                                                        => $column,
            };
        }

        foreach ($modelItems['relationships']['relations'] as $key => $item) {
            foreach ($item as $column) {
                $name           = $key . '.' . $column;
                $label          = Utils::translate($key) . ' - ' . Utils::translate($column);
                $queryBuilder[] = TextColumn::make($name)
                    ->label($label)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable();
            }
        }


        return $queryBuilder;
    }

    public function getGroupColumns(): array
    {
        $columns       = Utils::getModelColumns($this->model);
        $relationships = Utils::getModelRelationships($this->model);
        $modelItems    = ['columns' => $columns, 'relationships' => $relationships];

        $groups = [];
        foreach ($modelItems['columns'] as $item) {
            $column   = Group::make($item['name'])->label(Utils::translate($item['name']));
            $groups[] = $column;
        }

        return $groups;
    }

    public function getQueryBuilderFilter(): QueryBuilder
    {
        $columns       = Utils::getModelColumns($this->model);
        $relationships = Utils::getModelRelationships($this->model);
        $modelItems    = ['columns' => $columns, 'relationships' => $relationships];

        $queryBuilder = [];
        foreach ($modelItems['columns'] as $item) {

            $queryBuilder[] = match ($item['type_name']) {
                'int', 'bigint', 'smallint', 'mediumint', 'decimal', 'float', 'real', 'double' => QueryBuilder\Constraints\NumberConstraint::make($item['name']),
                'date', 'datetime', 'timestamp', 'time', 'year'                                => QueryBuilder\Constraints\DateConstraint::make($item['name']),
                'boolean', 'tinyint'                                                           => QueryBuilder\Constraints\BooleanConstraint::make($item['name']),
                default                                                                        => QueryBuilder\Constraints\TextConstraint::make($item['name']),
            };
        }

        foreach ($modelItems['relationships']['relations'] as $key => $item) {
            foreach ($item as $column) {
                $name           = $key . '.' . $column;
                $label          = Utils::translate($key) . ' - ' . Utils::translate($column);
                $queryBuilder[] = QueryBuilder\Constraints\TextConstraint::make($name)
                    ->label($label)
                    ->relationship(name: $key, titleAttribute: $column)
                    ->pushOperators([
                        QueryBuilder\Constraints\Operators\IsFilledOperator::make(),
                        QueryBuilder\Constraints\BooleanConstraint\Operators\IsTrueOperator::make(),
                        QueryBuilder\Constraints\BooleanConstraint\Operators\IsTrueOperator::make(),
                        QueryBuilder\Constraints\DateConstraint\Operators\IsAfterOperator::make(),
                        QueryBuilder\Constraints\DateConstraint\Operators\IsBeforeOperator::make(),
                        QueryBuilder\Constraints\DateConstraint\Operators\IsDateOperator::make(),
                        QueryBuilder\Constraints\DateConstraint\Operators\IsMonthOperator::make(),
                        QueryBuilder\Constraints\DateConstraint\Operators\IsYearOperator::make(),
                        // QueryBuilder\Constraints\NumberConstraint\Operators\IsMaxOperator::make(),
                        // QueryBuilder\Constraints\NumberConstraint\Operators\IsMinOperator::make(),
                        // QueryBuilder\Constraints\NumberConstraint\Operators\EqualsOperator::make(),
                        // QueryBuilder\Constraints\SelectConstraint\Operators\IsOperator::make(),
                    ]);
            }
        }


        foreach ($modelItems['relationships']['relations'] as $key => $item) {
            $queryBuilder[] = RelationshipConstraint::make($key)
                ->multiple()
                ->emptyable()
                ->selectable(
                    IsRelatedToOperator::make()
                        ->titleAttribute('name')
                        ->searchable()
                        ->preload()
                        ->multiple(),
                );
        }

        return QueryBuilder::make()->constraints($queryBuilder);
    }

    public function submitFilters(): void
    {
        $visibleColumns = array_keys($this->table->getVisibleColumns());
        $records        = $this->table->getRecords()->toArray()['data'];
        $onlyRecords    = Arr::map($records, fn($record) => Arr::only($record, $visibleColumns));

        $this->record->update([
            'data'          => $onlyRecords,
            'header'        => $visibleColumns,
            'grouping_rows' => $this->table->getGrouping()?->getId(),
            'records_count' => count($onlyRecords),
            'step'          => 1
        ]);

        Notification::make()->title(__('Saved.'))->success()->send();
        $this->activeTab = 'settings';
    }

    public function submitSettings(): void
    {
        $this->record->update([
            ...$this->form->getState(),
            'step' => 2
        ]);

        Notification::make()->title(__('Saved.'))->success()->send();
        $this->activeTab = 'preview';
    }

    public function updatedModel(): void
    {
        $this->resetTable();
    }
}
