<?php

namespace App\DTOs;

use App\Enums\LegalForm;

class EmployerDTO
{
    public function __construct(
        public readonly LegalForm $legal_form,
        public readonly string $nif,
        public readonly bool $is_associated,
        public readonly ?string $fiscal_name = null,
        public readonly ?string $comercial_name = null,
        public readonly ?string $ccc = null,
        public readonly ?string $cnae = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            legal_form: LegalForm::from($data['legal_form']),
            nif: $data['nif'],
            is_associated: (bool) ($data['is_associated'] ?? false),
            fiscal_name: $data['fiscal_name'] ?? null,
            comercial_name: $data['comercial_name'] ?? null,
            ccc: $data['ccc'] ?? null,
            cnae: $data['cnae'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'legal_form' => $this->legal_form->value,
            'comercial_name' => $this->comercial_name,
            'fiscal_name' => $this->fiscal_name,
            'nif' => $this->nif,
            'ccc' => $this->ccc,
            'cnae' => $this->cnae,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_associated' => $this->is_associated,
        ];
    }
}
