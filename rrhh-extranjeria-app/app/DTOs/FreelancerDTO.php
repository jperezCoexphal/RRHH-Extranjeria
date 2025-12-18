<?php

namespace App\DTOs;

use Carbon\Carbon;

class FreelancerDTO
{
    public function __construct(
        public readonly int $employer_id,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $niss,
        public readonly Carbon $birthdate,
    ) {}

    public static function fromRequest(array $data, int $employerId): self
    {
        // dd($employerId);
        return new self(
            employer_id: $employerId,
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            niss: $data['niss'],
            birthdate: Carbon::parse($data['birthdate']),
        );
    }

    public function toArray(): array
    {
        return [
            'employer_id' => $this->employer_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'niss' => $this->niss,
            'birthdate' => $this->birthdate->format('Y-m-d'),
        ];
    }
}
