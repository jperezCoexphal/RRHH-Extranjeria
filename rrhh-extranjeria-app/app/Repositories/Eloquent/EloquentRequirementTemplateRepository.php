<?php

namespace App\Repositories\Eloquent;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Models\RequirementTemplate;
use App\Repositories\Contracts\RequirementTemplateRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentRequirementTemplateRepository implements RequirementTemplateRepository
{
    public function __construct(
        protected RequirementTemplate $template
    ) {}

    public function all(): Collection
    {
        return $this->template->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->template
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->template->newQuery();
    }

    public function findById(int $id): ?RequirementTemplate
    {
        return $this->template->find($id);
    }

    public function create(array $data): RequirementTemplate
    {
        return $this->template->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $template = $this->findById($id);

        if (! $template) {
            return false;
        }

        return $template->update($data);
    }

    public function delete(int $id): bool
    {
        $template = $this->findById($id);

        if (! $template) {
            return false;
        }

        return $template->delete();
    }

    public function getTemplatesForFile(
        ApplicationType $applicationType,
        ImmigrationFileStatus $status
    ): Collection {
        return $this->template
            ->forApplicationType($applicationType)
            ->triggeredByStatus($status)
            ->orderBy('name')
            ->get();
    }

    public function getMandatoryTemplates(
        ApplicationType $applicationType,
        ImmigrationFileStatus $status
    ): Collection {
        return $this->template
            ->forApplicationType($applicationType)
            ->triggeredByStatus($status)
            ->mandatory()
            ->orderBy('name')
            ->get();
    }
}
