<?php

namespace App\DTOs;

class ForeignerExtraDataDTO
{
    public function __construct(
        public readonly int $foreigner_id,
        public readonly ?string $father_name = null,
        public readonly ?string $mother_name = null,
        public readonly ?string $legal_guardian_name = null,
        public readonly ?string $legal_guardian_identity_number = null,
        public readonly ?string $guardianship_title = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
    ) {}

    public static function fromRequest(array $data, int $foreignerId): self
    {
        return new self(
            foreigner_id: $foreignerId,
            father_name: $data['father_name'] ?? null,
            mother_name: $data['mother_name'] ?? null,
            legal_guardian_name: $data['legal_guardian_name'] ?? null,
            legal_guardian_identity_number: $data['legal_guardian_identity_number'] ?? null,
            guardianship_title: $data['guardianship_title'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'foreigner_id' => $this->foreigner_id,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'legal_guardian_name' => $this->legal_guardian_name,
            'legal_guardian_identity_number' => $this->legal_guardian_identity_number,
            'guardianship_title' => $this->guardianship_title,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}
