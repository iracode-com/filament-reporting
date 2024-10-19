<?php

namespace IracodeCom\FilamentReporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $table    = 'ir_reports';
    protected $fillable = [
        'created_by',
        'updated_by',
        'reportable_type',
        'reportable_resource',
        'title',
        'header',
        'data',
        'grouping_rows',
        'query',
        'font',
        'export_type',
        'header_description',
        'report_date',
        'logo',
        'footer_description',
        'description',
        'records_count',
        'step',
    ];

    protected function casts(): array
    {
        return [
            'data'          => 'array',
            'header'        => 'array',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
