<?php

namespace App\Exports;

use App\Models\CvmData;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CvmDataExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private int $projectId)
    {
    }

    public function query(): Builder
    {
        return CvmData::query()->where('project_id', $this->projectId)->orderBy('respondent_id');
    }

    public function headings(): array
    {
        return [
            'respondent_id', 'willing_to_pay', 'wtp', 'reason_if_unwilling',
            'household_size', 'household_income', 'education_level',
            'respondent_location', 'notes',
        ];
    }

    public function map($row): array
    {
        return [
            $row->respondent_id,
            $row->willing_to_pay ? 1 : 0,
            $row->wtp,
            $row->reason_if_unwilling,
            $row->household_size,
            $row->household_income,
            $row->education_level,
            $row->respondent_location,
            $row->notes,
        ];
    }
}
