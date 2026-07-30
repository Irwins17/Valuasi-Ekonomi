<?php

namespace App\Exports;

use App\Models\TcmData;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TcmDataExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private int $projectId)
    {
    }

    public function query(): Builder
    {
        return TcmData::query()->where('project_id', $this->projectId)->orderBy('respondent_id');
    }

    public function headings(): array
    {
        return [
            'respondent_id', 'origin_location', 'distance', 'transportation_cost',
            'time_cost', 'visit_frequency', 'total_travel_cost', 'consumer_surplus',
            'respondent_category', 'notes',
        ];
    }

    public function map($row): array
    {
        return [
            $row->respondent_id,
            $row->origin_location,
            $row->distance,
            $row->transportation_cost,
            $row->time_cost,
            $row->visit_frequency,
            $row->total_travel_cost,
            $row->consumer_surplus,
            $row->respondent_category,
            $row->notes,
        ];
    }
}
