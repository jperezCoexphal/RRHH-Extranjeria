<?php

namespace App\DTOs;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Carbon\Carbon;

class ForeignerDTO
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $passport,
        public readonly string $nie,
        public readonly Gender $gender,
        public readonly Carbon $birthdate,
        public readonly string $nationality,
        public readonly MaritalStatus $marital_status,
        public readonly ?string $niss = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            passport: $data['passport'],
            nie: $data['nie'],
            gender: Gender::from($data['gender']),
            birthdate: Carbon::parse($data['birthdate']),
            nationality: $data['nationality'],
            marital_status: MaritalStatus::from($data['marital_status']),
            niss: $data['niss'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'passport' => $this->passport,
            'nie' => $this->nie,
            'gender' => $this->gender->value,
            'birthdate' => $this->birthdate->format('Y-m-d'),
            'nationality' => $this->nationality,
            'marital_status' => $this->marital_status->value,
            'niss' => $this->niss,
        ];
    }
}
