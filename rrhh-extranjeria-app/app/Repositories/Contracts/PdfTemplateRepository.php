<?php

namespace App\Repositories\Contracts;

use App\Enums\ApplicationType;
use App\Enums\PdfTemplateType;
use App\Models\PdfTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface PdfTemplateRepository
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function query(): Builder;

    public function findById(int $id): ?PdfTemplate;

    public function create(array $data): PdfTemplate;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    /**
     * Obtiene las plantillas activas de un tipo específico
     */
    public function getActiveByType(PdfTemplateType $type): Collection;

    /**
     * Obtiene la plantilla predeterminada para un tipo de documento y solicitud
     */
    public function getDefaultTemplate(
        PdfTemplateType $documentType,
        ?ApplicationType $applicationType = null
    ): ?PdfTemplate;

    /**
     * Obtiene plantillas por tipo de solicitud
     */
    public function getByApplicationType(ApplicationType $applicationType): Collection;

    /**
     * Busca plantillas por nombre
     */
    public function searchByName(string $search): Collection;

    /**
     * Actualiza el mapeo de campos de una plantilla
     */
    public function updateFieldMapping(int $id, array $fieldMapping): bool;

    /**
     * Establece una plantilla como predeterminada
     */
    public function setAsDefault(int $id): bool;
}
