<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ForeignerDTO;
use App\DTOs\ForeignerExtraDataDTO;
use App\Models\Foreigner;
use App\Models\ForeignerExtraData;
use App\Repositories\Contracts\ForeignerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentForeignerRepository implements ForeignerRepository
{
    public function __construct(
        protected Foreigner $foreigner,
        protected ForeignerExtraData $foreignerExtraData
    ) {}

    public function all(): Collection
    {
        return $this->foreigner->with('extraData')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->foreigner->with('extraData')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->foreigner->newQuery();
    }

    public function findById(int $id): ?Foreigner
    {
        return $this->foreigner->with('extraData')->find($id);
    }

    public function create(ForeignerDTO $dto): Foreigner
    {
        return $this->foreigner->create($dto->toArray());
    }

    public function update(int $id, ForeignerDTO $dto): bool
    {
        $foreigner = $this->findById($id);

        if (!$foreigner) {
            return false;
        }

        return $foreigner->update($dto->toArray());
    }

    public function delete(int $id): bool
    {
        $foreigner = $this->findById($id);

        if (!$foreigner) {
            return false;
        }

        return $foreigner->delete();
    }

    public function createExtraData(ForeignerExtraDataDTO $dto): void
    {
        $this->foreignerExtraData->create($dto->toArray());
    }

    public function updateExtraData(int $foreignerId, ForeignerExtraDataDTO $dto): void
    {   $this->foreigner->find($foreignerId)->touch();
        $this->foreignerExtraData->updateOrCreate(
            ['foreigner_id' => $foreignerId],
            $dto->toArray()
        );
    }
}
