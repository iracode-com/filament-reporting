<?php

namespace IracodeCom\FilamentReporting\Exports;

use Illuminate\Support\Arr;
use IracodeCom\FilamentReporting\Support\Utils;
use IracodeCom\FilamentReporting\Models\Report;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReportingExport implements FromArray, WithHeadings, WithDrawings, WithProperties, WithEvents
{

    use Exportable;

    public Report  $report;
    private string $fileName;
    private string $writerType;
    private array  $headers;

    public function __construct(Report $report)
    {
        $this->report = $report;
        $writerType   = match ($report->export_type) {
            'xlsx'  => Excel::XLSX,
            'xls'   => Excel::XLS,
            'csv'   => Excel::CSV,
            'pdf'   => Excel::HTML,
            'chart' => Excel::HTML,
        };
        $filename     = $report->title . verta()->timestamp . '.' . $writerType;

        $this->fileName($filename);
        $this->writerType($writerType);
    }

    public function array(): array
    {
        return $this->report->data ?? [];
    }

    public function headings(): array
    {
        $headings = Arr::map($this->report->header ?? [], function ($header) {
            return Utils::translate($header);
        });
        return $headings ?? [];
    }

    public function properties(): array
    {
        return [
            'creator'        => $this->report->createdBy->name,
            'lastModifiedBy' => $this->report->updatedBy->name,
            'title'          => $this->fileName,
            'description'    => $this->report->header_description,
            'subject'        => $this->fileName,
        ];
    }

    public function drawings(): Drawing
    {
        $drawing = new Drawing();
        if($this->report->logo) {
            $drawing->setName('Logo');
            $drawing->setDescription('This is my logo');
            $drawing->setPath(public_path('storage/' . $this->report->logo));
            $drawing->setHeight(40);
            $drawing->setCoordinates('A1');
        }

        return $drawing;
    }

    public function fileName($fileName): void
    {
        $this->fileName = $fileName;
    }

    public function writerType($writerType): void
    {
        $this->writerType = $writerType;
    }

    public function headers(array $headers): void
    {
        $this->headers = $headers;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->getDelegate()->setRightToLeft(true);
            },

            // AfterSheet::class  => function (AfterSheet $event) {
            //     $cellRange = 'A1:Z999'; // All headers
            //     $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setName('Segoe UI');
            // },
        ];
    }
}
