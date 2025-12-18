<?php

namespace App\Services;

use App\DTOs\CompanyDTO;
use App\DTOs\EmployerDTO;
use App\DTOs\FreelancerDTO;
use App\Enums\LegalForm;
use App\Models\Employer;
use App\Repositories\Contracts\EmployerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class EmployerService
{
    public function __construct(protected EmployerRepository $repository) {}

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

        return $query->with(['company', 'freelancer'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Employer
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Employer
    {
        DB::beginTransaction();
        $employerDTO = EmployerDTO::fromRequest($data);
        $employer = $this->repository->create($employerDTO);
        $legalForm = LegalForm::from($data['legal_form']);

        // dd($employer->id);
        try{

        if ($legalForm === LegalForm::EI || $legalForm === LegalForm::ERL) {
            $freelancerDTO = FreelancerDTO::fromRequest($data, $employer->id);
            $this->repository->createFreelancer($freelancerDTO);
        } else {
            $companyDTO = CompanyDTO::fromRequest($data, $employer->id);
            $this->repository->createCompany($companyDTO);
        }
        DB::commit();
    }catch(\Exception $e){
        DB::rollBack();
        // dd('cagaste');
    }

        return $employer->fresh(['company', 'freelancer']);
    }

    public function update(int $id, array $data): bool
    {
        $employerDTO = EmployerDTO::fromRequest($data);
        $updated = $this->repository->update($id, $employerDTO);

        if ($updated) {
            $legalForm = LegalForm::from($data['legal_form']);

            if ($legalForm === LegalForm::EI || $legalForm === LegalForm::ERL) {
                $freelancerDTO = FreelancerDTO::fromRequest($data, $id);
                $this->repository->updateFreelancer($id, $freelancerDTO);
            } else {
                $companyDTO = CompanyDTO::fromRequest($data, $id);
                $this->repository->updateCompany($id, $companyDTO);
            }
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}