<?php

namespace App\Repositories\Eloquent;

use App\DTOs\FileRequirementDTO;
use App\Enums\TargetEntity;
use App\Models\FileRequirement;
use App\Repositories\Contracts\FileRequirementRepository;
use Illuminate\Support\Collection;

class EloquentFileRequirementRepository implements FileRequirementRepository
{
    public function __construct(
        protected FileRequirement $requirement
    ) {}

    public function findById(int $id): ?FileRequirement
    {
        return $this->requirement
            ->with(['inmigrationFile', 'template'])
            ->find($id);
    }

    public function create(FileRequirementDTO $dto): FileRequirement
    {
        return $this->requirement->create($dto->toArray());
    }

    public function createMany(array $dtos): Collection
    {
        $created = collect();

        foreach ($dtos as $dto) {
            $created->push($this->create($dto));
        }

        return $created;
    }

    public function update(int $id, array $data): bool
    {
        $requirement = $this->requirement->find($id);

        if (! $requirement) {
            return false;
        }

        return $requirement->update($data);
    }

    public function delete(int $id): bool
    {
        $requirement = $this->requirement->find($id);

        if (! $requirement) {
            return false;
        }

        return $requirement->delete();
    }

    public function getByInmigrationFile(int $inmigrationFileId): Collection
    {
        return $this->requirement
            ->with('template')
            ->where('inmigration_file_id', $inmigrationFileId)
            ->orderBy('target_entity')
            ->orderBy('name')
            ->get();
    }

    public function getPendingByInmigrationFile(int $inmigrationFileId): Collection
    {
        return $this->requirement
            ->with('template')
            ->where('inmigration_file_id', $inmigrationFileId)
            ->pending()
            ->orderBy('due_date')
            ->orderBy('name')
            ->get();
    }

    public function getIncompleteMandatory(int $inmigrationFileId): Collection
    {
        return $this->requirement
            ->where('inmigration_file_id', $inmigrationFileId)
            ->mandatory()
            ->pending()
            ->orderBy('target_entity')
            ->orderBy('name')
            ->get();
    }

    public function getOverdueByInmigrationFile(int $inmigrationFileId): Collection
    {
        return $this->requirement
            ->where('inmigration_file_id', $inmigrationFileId)
            ->overdue()
            ->orderBy('due_date')
            ->get();
    }

    public function getByEntity(int $inmigrationFileId, TargetEntity $entity): Collection
    {
        return $this->requirement
            ->where('inmigration_file_id', $inmigrationFileId)
            ->forEntity($entity)
            ->orderBy('name')
            ->get();
    }

    public function markAsCompleted(int $id): bool
    {
        $requirement = $this->requirement->find($id);

        if (! $requirement) {
            return false;
        }

        return $requirement->markAsCompleted();
    }

    public function allMandatoryCompleted(int $inmigrationFileId): bool
    {
        return $this->requirement
            ->where('inmigration_file_id', $inmigrationFileId)
            ->mandatory()
            ->pending()
            ->doesntExist();
    }
}
