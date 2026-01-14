<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CompanyDTO;
use App\DTOs\EmployerDTO;
use App\DTOs\FreelancerDTO;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Freelancer;
use App\Repositories\Contracts\EmployerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployerRepository implements EmployerRepository
{
    public function __construct(
        protected Employer $employer,
        protected Company $company,
        protected Freelancer $freelancer
    ) {}

    public function all(): Collection
    {
        return $this->employer->with(['company', 'freelancer'])->get();
    }


    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->employer->with(['company', 'freelancer'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->employer->newQuery();
    }

    public function findById(int $id): ?Employer
    {
        return $this->employer->with(['company', 'freelancer'])->find($id);
    }

    public function create(EmployerDTO $dto): Employer
    {   
        return $this->employer->create($dto->toArray());
    }

    public function update(int $id, EmployerDTO $dto): bool
    {
        $employer = $this->findById($id);

        if (!$employer) {
            return false;
        }

        return $employer->update($dto->toArray());
    }

    public function delete(int $id): bool
    {
        $employer = $this->findById($id);

        if (!$employer) {
            return false;
        }

        return $employer->delete();
    }

    public function createCompany(CompanyDTO $dto): void
    {
        $this->company->create($dto->toArray());
    }

    public function updateCompany(int $employerId, CompanyDTO $dto): void
    {
        $this->company->updateOrCreate(
            ['employer_id' => $employerId],
            $dto->toArray()
        );
    }

    public function createFreelancer(FreelancerDTO $dto): void
    {
        // dd($dto);
        $this->freelancer->create($dto->toArray());
    }

    public function updateFreelancer(int $employerId, FreelancerDTO $dto): void
    {
        $this->freelancer->updateOrCreate(
            ['employer_id' => $employerId],
            $dto->toArray()
        );
    }
}