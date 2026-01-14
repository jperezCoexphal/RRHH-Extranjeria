<?php

namespace App\Repositories\Contracts;

use App\DTOs\InmigrationFileDTO;
use App\Enums\ImmigrationFileStatus;
use App\Models\InmigrationFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface InmigrationFileRepository
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function query(): Builder;

    public function findById(int $id): ?InmigrationFile;

    public function findByIdWithRelations(int $id): ?InmigrationFile;

    public function create(InmigrationFileDTO $dto): InmigrationFile;

    public function update(int $id, InmigrationFileDTO $dto): bool;

    public function updateStatus(int $id, ImmigrationFileStatus $status): bool;

    public function delete(int $id): bool;

    public function findByCampaign(string $campaign): Collection;

    public function findByStatus(ImmigrationFileStatus $status): Collection;

    public function getWithOverdueRequirements(): Collection;
}
