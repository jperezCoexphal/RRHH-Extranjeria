<?php

namespace App\Repositories\Contracts;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Models\RequirementTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface RequirementTemplateRepository
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function query(): Builder;

    public function findById(int $id): ?RequirementTemplate;

    public function create(array $data): RequirementTemplate;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    /**
     * Obtiene las plantillas aplicables para un tipo de solicitud y estado
     */
    public function getTemplatesForFile(
        ApplicationType $applicationType,
        ImmigrationFileStatus $status
    ): Collection;

    /**
     * Obtiene solo las plantillas obligatorias
     */
    public function getMandatoryTemplates(
        ApplicationType $applicationType,
        ImmigrationFileStatus $status
    ): Collection;
}
