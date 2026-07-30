<?php

namespace App\Imports;

use App\Models\TcmData;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class TcmDataImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable;

    /** @var array<int, Failure> */
    private array $failures = [];

    /** @var array<int, Throwable> */
    private array $errors = [];

    public function __construct(private int $projectId, private int $userId)
    {
    }

    public function model(array $row): TcmData
    {
        return new TcmData([
            'project_id' => $this->projectId,
            'recorded_by' => $this->userId,
            'respondent_id' => $row['respondent_id'],
            'origin_location' => $row['origin_location'] ?? null,
            'distance' => $row['distance'],
            'transportation_cost' => $row['transportation_cost'],
            'time_cost' => $row['time_cost'],
            'visit_frequency' => $row['visit_frequency'],
            'respondent_category' => $row['respondent_category'] ?? null,
            'notes' => $row['notes'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'respondent_id' => ['required', 'integer', 'min:1', Rule::unique('tcm_data')->where('project_id', $this->projectId)],
            'distance' => ['required', 'numeric', 'min:0'],
            'transportation_cost' => ['required', 'numeric', 'min:0'],
            'time_cost' => ['required', 'numeric', 'min:0'],
            'visit_frequency' => ['required', 'integer', 'min:1'],
        ];
    }

    public function onError(Throwable $e): void
    {
        $this->errors[] = $e;
    }

    public function onFailure(Failure ...$failures): void
    {
        array_push($this->failures, ...$failures);
    }

    /** @return array<int, Failure> */
    public function failures(): array
    {
        return $this->failures;
    }
}
