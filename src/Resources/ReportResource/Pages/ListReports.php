<?php

namespace IracodeCom\FilamentReporting\Resources\ReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use IracodeCom\FilamentReporting\Resources\ReportResource;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


}
