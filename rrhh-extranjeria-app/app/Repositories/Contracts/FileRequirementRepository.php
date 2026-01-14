<?php

namespace App\Repositories\Contracts;

use App\DTOs\FileRequirementDTO;
use App\Enums\TargetEntity;
use App\Models\FileRequirement;
use Illuminate\Support\Collection;

interface FileRequirementRepository
{
    public function findById(int $id): ?FileRequirement;

    public function create(FileRequirementDTO $dto): FileRequirement;

    public function createMany(array $dtos): Collection;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    /**
     * Obtiene todos los requisitos de un expediente
     */
    public function getByInmigrationFile(int $inmigrationFileId): Collection;

    /**
     * Obtiene los requisitos pendientes de un expediente
     */
    public function getPendingByInmigrationFile(int $inmigrationFileId): Collection;

    /**
     * Obtiene los requisitos obligatorios incompletos de un expediente
     */
    public function getIncompleteMandatory(int $inmigrationFileId): Collection;

    /**
     * Obtiene los requisitos vencidos de un expediente
     */
    public function getOverdueByInmigrationFile(int $inmigrationFileId): Collection;

    /**
     * Obtiene requisitos por entidad objetivo
     */
    public function getByEntity(int $inmigrationFileId, TargetEntity $entity): Collection;

    /**
     * Marca un requisito como completado
     */
    public function markAsCompleted(int $id): bool;

    /**
     * Verifica si todos los requisitos obligatorios están completados
     */
    public function allMandatoryCompleted(int $inmigrationFileId): bool;
}
