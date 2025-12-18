<?php

namespace App\DTOs;

use App\Enums\LegalForm;

class EmployerDTO
{
    public function __construct(
        public readonly LegalForm $legal_form,
        public readonly string $fiscal_name,
        public readonly string $nif,
        public readonly string $ccc,
        public readonly string $cnae,
        public readonly bool $is_associated,
        public readonly ?string $comercial_name = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromRequest(array $data): self
    {
      
        return new self(
            legal_form: LegalForm::from($data['legal_form']),
            fiscal_name: $data['fiscal_name'],
            nif: $data['nif'],
            ccc: $data['ccc'],
            cnae: $data['cnae'],
            is_associated: $data['is_associated'] ?? false,
            comercial_name: $data['comercial_name'] ?? null,
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
