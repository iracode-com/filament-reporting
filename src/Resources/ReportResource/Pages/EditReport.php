<?php

namespace IracodeCom\FilamentReporting\Resources\ReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use IracodeCom\FilamentReporting\Resources\ReportResource;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
