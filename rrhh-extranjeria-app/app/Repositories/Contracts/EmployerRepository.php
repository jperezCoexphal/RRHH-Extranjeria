<?php

namespace App\Repositories\Contracts;

use App\DTOs\CompanyDTO;
use App\DTOs\EmployerDTO;
use App\DTOs\FreelancerDTO;
use App\Models\Employer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface EmployerRepository
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function query(): Builder;

    public function findById(int $id): ?Employer;

    public function create(EmployerDTO $dto): Employer;

    public function update(int $id, EmployerDTO $dto): bool;

    public function delete(int $id): bool;

    public function createCompany(CompanyDTO $dto): void;

    public function updateCompany(int $employerId, CompanyDTO $dto): void;

    public function createFreelancer(FreelancerDTO $dto): void;

    public function updateFreelancer(int $employerId, FreelancerDTO $dto): void;
}