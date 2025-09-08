<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivityExport implements FromCollection, WithHeadings
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?array $clusters;

    public function __construct(?string $startDate = null, ?string $endDate = null, ?array $clusters = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->clusters = $clusters;
    }

    // ✅ Method wajib dari FromCollection
    public function collection()
    {
        $query = Activity::query();

        if ($this->startDate) {
            $query->whereDate('start_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('end_date', '<=', $this->endDate);
        }

        if (!empty($this->clusters)) {
            $query->whereIn('cluster', $this->clusters);
        }

        return $query->select(
            'initial',
            'nama',
            'cluster',
            'role',
            'aplikasi',
            'activity',
            'start_date',
            'end_date'
        )->get();
    }

    // ✅ Method wajib dari WithHeadings
    public function headings(): array
    {
        return [
            'Initial',
            'Nama',
            'Cluster',
            'Role',
            'Aplikasi',
            'Activity',
            'Start Date',
            'End Date',
        ];
    }
}
