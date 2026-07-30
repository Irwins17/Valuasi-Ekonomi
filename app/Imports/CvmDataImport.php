<?php

namespace App\Imports;

use App\Models\CvmData;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class CvmDataImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable;

    /** @var array<int, Failure> */
    private array $failures = [];

    /** @var array<int, Throwable> */
    private array $errors = [];

    public function __construct(private int $projectId, private int $userId)
    {
    }

    public function model(array $row): CvmData
    {
        $willingToPay = (bool) ($row['willing_to_pay'] ?? true);

        return new CvmData([
            'project_id' => $this->projectId,
            'recorded_by' => $this->userId,
            'respondent_id' => $row['respondent_id'],
            'willing_to_pay' => $willingToPay,
            'wtp' => $willingToPay ? ($row['wtp'] ?? null) : null,
            'reason_if_unwilling' => ! $willingToPay ? ($row['reason_if_unwilling'] ?? null) : null,
            'household_size' => $row['household_size'] ?? null,
            'household_income' => $row['household_income'] ?? null,
            'education_level' => $row['education_level'] ?? null,
            'respondent_location' => $row['respondent_location'] ?? null,
            'notes' => $row['notes'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'respondent_id' => ['required', 'integer', 'min:1', Rule::unique('cvm_data')->where('project_id', $this->projectId)],
            'wtp' => ['required_if:willing_to_pay,1', 'nullable', 'numeric', 'min:0'],
            'household_size' => ['nullable', 'integer', 'min:1'],
            'household_income' => ['nullable', 'numeric', 'min:0'],
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
