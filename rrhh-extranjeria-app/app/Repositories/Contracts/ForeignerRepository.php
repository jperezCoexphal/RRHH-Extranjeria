<?php

namespace App\Repositories\Contracts;

use App\DTOs\ForeignerDTO;
use App\DTOs\ForeignerExtraDataDTO;
use App\Models\Foreigner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ForeignerRepository
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function query(): Builder;

    public function findById(int $id): ?Foreigner;

    public function create(ForeignerDTO $dto): Foreigner;

    public function update(int $id, ForeignerDTO $dto): bool;

    public function delete(int $id): bool;

    public function createExtraData(ForeignerExtraDataDTO $dto): void;

    public function updateExtraData(int $foreignerId, ForeignerExtraDataDTO $dto): void;
}
