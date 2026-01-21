<?php

namespace App\Repositories\Eloquent;

use App\Enums\ApplicationType;
use App\Enums\PdfTemplateType;
use App\Models\PdfTemplate;
use App\Repositories\Contracts\PdfTemplateRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentPdfTemplateRepository implements PdfTemplateRepository
{
    public function __construct(
        protected PdfTemplate $template
    ) {}

    public function all(): Collection
    {
        return $this->template
            ->orderBy('document_type')
            ->orderBy('name')
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->template
            ->orderBy('document_type')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function query(): Builder
    {
        return $this->template->newQuery();
    }

    public function findById(int $id): ?PdfTemplate
    {
        return $this->template->find($id);
    }

    public function create(array $data): PdfTemplate
    {
        return $this->template->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $template = $this->findById($id);

        if (!$template) {
            return false;
        }

        return $template->update($data);
    }

    public function delete(int $id): bool
    {
        $template = $this->findById($id);

        if (!$template) {
            return false;
        }

        return $template->delete();
    }

    public function getActiveByType(PdfTemplateType $type): Collection
    {
        return $this->template
            ->active()
            ->forDocumentType($type)
            ->orderBy('name')
            ->get();
    }

    public function getDefaultTemplate(
        PdfTemplateType $documentType,
        ?ApplicationType $applicationType = null
    ): ?PdfTemplate {
        return $this->template
            ->defaultFor($documentType, $applicationType)
            ->first();
    }

    public function getByApplicationType(ApplicationType $applicationType): Collection
    {
        return $this->template
            ->active()
            ->forApplicationType($applicationType)
            ->orderBy('document_type')
            ->orderBy('name')
            ->get();
    }

    public function searchByName(string $search): Collection
    {
        return $this->template
            ->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orderBy('name')
            ->get();
    }

    public function updateFieldMapping(int $id, array $fieldMapping): bool
    {
        $template = $this->findById($id);

        if (!$template) {
            return false;
        }

        return $template->update(['field_mapping' => $fieldMapping]);
    }

    public function setAsDefault(int $id): bool
    {
        $template = $this->findById($id);

        if (!$template) {
            return false;
        }

        return $template->setAsDefault();
    }
}
