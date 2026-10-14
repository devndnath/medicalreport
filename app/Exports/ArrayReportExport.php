<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Generic reusable exporter: pass any report's already-computed rows (as
 * plain arrays) plus the column headings, and it becomes a downloadable
 * .xlsx. Every report controller builds its rows from real database
 * queries first; this class never generates numbers on its own.
 *
 * Laravel Excel v4 requires native return types on these concern methods.
 */
class ArrayReportExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(protected array $rows, protected array $headings, protected string $title = 'Report') {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
