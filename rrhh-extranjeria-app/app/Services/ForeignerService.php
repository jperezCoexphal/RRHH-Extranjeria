<?php

namespace App\Services;

use App\DTOs\ForeignerDTO;
use App\DTOs\ForeignerExtraDataDTO;
use App\Models\Foreigner;
use App\Repositories\Contracts\ForeignerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pipeline\Pipeline;

class ForeignerService
{
    public function __construct(protected ForeignerRepository $repository) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getFiltered(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = app(Pipeline::class)
            ->send($this->repository->query())
            ->through($filters)
            ->thenReturn();

        return $query->with('extraData')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Foreigner
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Foreigner
    {
        $foreignerDTO = ForeignerDTO::fromRequest($data);
        $foreigner = $this->repository->create($foreignerDTO);

        if (!empty($data['father_name']) || !empty($data['mother_name']) || !empty($data['legal_guardian_name'])) {
            $extraDataDTO = ForeignerExtraDataDTO::fromRequest($data, $foreigner->id);
            $this->repository->createExtraData($extraDataDTO);
        }

        return $foreigner->fresh('extraData');
    }

    public function update(int $id, array $data): bool
    {
        $foreignerDTO = ForeignerDTO::fromRequest($data);
        $updated = $this->repository->update($id, $foreignerDTO);

        if ($updated) {
            $extraDataDTO = ForeignerExtraDataDTO::fromRequest($data, $id);
            $this->repository->updateExtraData($id, $extraDataDTO);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
