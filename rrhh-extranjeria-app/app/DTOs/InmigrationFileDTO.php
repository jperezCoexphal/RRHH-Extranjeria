<?php

namespace App\DTOs;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\WorkingDayType;
use Carbon\Carbon;

class InmigrationFileDTO
{
    public function __construct(
        public readonly string $campaign,
        public readonly string $file_code,
        public readonly string $file_title,
        public readonly ApplicationType $application_type,
        public readonly ImmigrationFileStatus $status,
        public readonly string $job_title,
        public readonly Carbon $start_date,
        public readonly int $employer_id,
        public readonly int $foreigner_id,
        public readonly int $editor_id,
        public readonly ?Carbon $end_date = null,
        public readonly ?float $salary = null,
        public readonly ?WorkingDayType $working_day_type = null,
        public readonly ?float $working_hours = null,
        public readonly ?int $probation_period = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            campaign: $data['campaign'],
            file_code: $data['file_code'],
            file_title: $data['file_title'],
            application_type: ApplicationType::from($data['application_type']),
            status: ImmigrationFileStatus::from($data['status'] ?? 'borrador'),
            job_title: $data['job_title'],
            start_date: Carbon::parse($data['start_date']),
            employer_id: (int) $data['employer_id'],
            foreigner_id: (int) $data['foreigner_id'],
            editor_id: (int) $data['editor_id'],
            end_date: isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            salary: isset($data['salary']) ? (float) $data['salary'] : null,
            working_day_type: isset($data['working_day_type'])
                ? WorkingDayType::from($data['working_day_type'])
                : null,
            working_hours: isset($data['working_hours']) ? (float) $data['working_hours'] : null,
            probation_period: isset($data['probation_period']) ? (int) $data['probation_period'] : null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'campaign' => $this->campaign,
            'file_code' => $this->file_code,
            'file_title' => $this->file_title,
            'application_type' => $this->application_type->value,
            'status' => $this->status->value,
            'job_title' => $this->job_title,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'salary' => $this->salary,
            'working_day_type' => $this->working_day_type?->value,
            'working_hours' => $this->working_hours,
            'probation_period' => $this->probation_period,
            'employer_id' => $this->employer_id,
            'foreigner_id' => $this->foreigner_id,
            'editor_id' => $this->editor_id,
        ];
    }
}
