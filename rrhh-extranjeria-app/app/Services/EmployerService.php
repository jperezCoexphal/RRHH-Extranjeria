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
        

        try {
            $employerDTO = EmployerDTO::fromRequest($data);
            
            $employer = $this->repository->create($employerDTO);
           
            $legalForm = LegalForm::from($data['legal_form']);

            if ($legalForm === LegalForm::EI || $legalForm === LegalForm::ERL) {
                $freelancerDTO = FreelancerDTO::fromRequest($data, $employer->id);
                $this->repository->createFreelancer($freelancerDTO);
            } else {
                $companyDTO = CompanyDTO::fromRequest($data, $employer->id);
                $this->repository->createCompany($companyDTO);
            }

            

            // Crear direccion si se proporciona
            if (!empty($data['street_name']) && !empty($data['country_id'])) {
                $employer->address()->create([
                    'street_name' => $data['street_name'],
                    'number' => $data['number'] ?? null,
                    'floor_door' => $data['floor_door'] ?? null,
                    'postal_code' => $data['postal_code'] ?? '00000',
                    'country_id' => $data['country_id'],
                    'province_id' => $data['province_id'] ?? null,
                    'municipality_id' => $data['municipality_id'] ?? null,
                ]);
            }

            DB::commit();

            return $employer->fresh(['company', 'freelancer', 'address']);
        } catch (\Exception $e) {
            dd("Sa pifiao: " . $e->getMessage());
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $employerDTO = EmployerDTO::fromRequest($data);
            $updated = $this->repository->update($id, $employerDTO);

            if ($updated) {
                $legalForm = LegalForm::from($data['legal_form']);
                $employer = $this->repository->findById($id);

                if ($legalForm === LegalForm::EI || $legalForm === LegalForm::ERL) {
                    $freelancerDTO = FreelancerDTO::fromRequest($data, $id);
                    $this->repository->updateFreelancer($id, $freelancerDTO);
                } else {
                    $companyDTO = CompanyDTO::fromRequest($data, $id);
                    $this->repository->updateCompany($id, $companyDTO);
                }

                // Actualizar direccion
                if (!empty($data['street_name']) && !empty($data['country_id'])) {
                    $employer->address()->updateOrCreate(
                        ['addressable_id' => $id, 'addressable_type' => Employer::class],
                        [
                            'street_name' => $data['street_name'],
                            'number' => $data['number'] ?? null,
                            'floor_door' => $data['floor_door'] ?? null,
                            'postal_code' => $data['postal_code'] ?? '00000',
                            'country_id' => $data['country_id'],
                            'province_id' => $data['province_id'] ?? null,
                            'municipality_id' => $data['municipality_id'] ?? null,
                        ]
                    );
                }
            }

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}