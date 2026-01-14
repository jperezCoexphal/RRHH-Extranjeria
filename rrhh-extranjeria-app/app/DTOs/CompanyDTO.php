<?php

namespace App\DTOs;

class CompanyDTO
{
    public function __construct(
        public readonly int $employer_id,
        public readonly string $representative_name,
        public readonly ?string $representative_title = null,
        public readonly ?string $representantive_identity_number = null,
    ) {}

    public static function fromRequest(array $data, int $employerId): self
    {
        return new self(
            employer_id: $employerId,
            representative_name: $data['representative_name'],
            representative_title: $data['representative_title'] ?? null,
            representantive_identity_number: $data['representantive_identity_number'] ?? $data['representative_identity_number'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'employer_id' => $this->employer_id,
            'representative_name' => $this->representative_name,
            'representative_title' => $this->representative_title,
            'representantive_identity_number' => $this->representantive_identity_number,
        ];
    }
}
