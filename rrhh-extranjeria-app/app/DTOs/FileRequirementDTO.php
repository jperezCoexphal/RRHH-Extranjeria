<?php

namespace App\DTOs;

use App\Enums\TargetEntity;
use Carbon\Carbon;

class FileRequirementDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $inmigration_file_id,
        public readonly bool $is_mandatory,
        public readonly ?string $description = null,
        public readonly ?TargetEntity $target_entity = null,
        public readonly ?string $observation = null,
        public readonly ?Carbon $due_date = null,
        public readonly ?int $requirement_template_id = null,
        public readonly ?int $id = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            inmigration_file_id: (int) $data['inmigration_file_id'],
            is_mandatory: (bool) ($data['is_mandatory'] ?? false),
            description: $data['description'] ?? null,
            target_entity: isset($data['target_entity'])
                ? TargetEntity::from($data['target_entity'])
                : null,
            observation: $data['observation'] ?? null,
            due_date: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            requirement_template_id: $data['requirement_template_id'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    /**
     * Crea un DTO desde una plantilla de requisito
     */
    public static function fromTemplate(
        \App\Models\RequirementTemplate $template,
        int $inmigrationFileId,
        ?Carbon $baseDate = null
    ): self {
        $dueDate = null;
        if ($template->days_to_expire !== null) {
            $baseDate = $baseDate ?? now();
            $dueDate = $baseDate->copy()->addDays($template->days_to_expire);
        }

        return new self(
            name: $template->name,
            inmigration_file_id: $inmigrationFileId,
            is_mandatory: $template->is_mandatory,
            description: $template->description,
            target_entity: $template->target_entity,
            due_date: $dueDate,
            requirement_template_id: $template->id,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'target_entity' => $this->target_entity?->value,
            'observation' => $this->observation,
            'due_date' => $this->due_date?->toDateString(),
            'is_completed' => false,
            'is_mandatory' => $this->is_mandatory,
            'inmigration_file_id' => $this->inmigration_file_id,
            'requirement_template_id' => $this->requirement_template_id,
        ];
    }
}
