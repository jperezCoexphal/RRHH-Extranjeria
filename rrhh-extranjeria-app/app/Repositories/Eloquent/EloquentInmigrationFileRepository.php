<?php

namespace App\Repositories\Eloquent;

use App\DTOs\InmigrationFileDTO;
use App\Enums\ImmigrationFileStatus;
use App\Models\InmigrationFile;
use App\Repositories\Contracts\InmigrationFileRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentInmigrationFileRepository implements InmigrationFileRepository
{
    public function __construct(
        protected InmigrationFile $inmigrationFile
    ) {}

    public function all(): Collection
    {
        return $this->inmigrationFile
            ->with(['employer', 'foreigner', 'editor'])
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->inmigrationFile
            ->with(['employer', 'foreigner', 'editor'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->inmigrationFile->newQuery();
    }

    public function findById(int $id): ?InmigrationFile
    {
        return $this->inmigrationFile->find($id);
    }

    public function findByIdWithRelations(int $id): ?InmigrationFile
    {
        return $this->inmigrationFile
            ->with([
                'employer.company',
                'employer.freelancer',
                'foreigner.extraData',
                'foreigner.nationality',
                'foreigner.birthCountry',
                'editor',
                'requirements',
                'workAddress.municipality',
                'workAddress.province',
                'workAddress.country',
            ])
            ->find($id);
    }

    public function create(InmigrationFileDTO $dto): InmigrationFile
    {
        return $this->inmigrationFile->create($dto->toArray());
    }

    public function update(int $id, InmigrationFileDTO $dto): bool
    {
        $file = $this->findById($id);

        if (! $file) {
            return false;
        }

        return $file->update($dto->toArray());
    }

    public function updateStatus(int $id, ImmigrationFileStatus $status): bool
    {
        $file = $this->findById($id);

        if (! $file) {
            return false;
        }

        return $file->update(['status' => $status->value]);
    }

    public function delete(int $id): bool
    {
        $file = $this->findById($id);

        if (! $file) {
            return false;
        }

        return $file->delete();
    }

    public function findByCampaign(string $campaign): Collection
    {
        return $this->inmigrationFile
            ->with(['employer', 'foreigner', 'editor'])
            ->where('campaign', $campaign)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByStatus(ImmigrationFileStatus $status): Collection
    {
        return $this->inmigrationFile
            ->with(['employer', 'foreigner', 'editor'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWithOverdueRequirements(): Collection
    {
        return $this->inmigrationFile
            ->with(['employer', 'foreigner', 'requirements'])
            ->withOverdueRequirements()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
