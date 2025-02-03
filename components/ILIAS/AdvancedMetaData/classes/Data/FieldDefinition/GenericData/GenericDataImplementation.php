<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;
use ILIAS\AdvancedMetaData\Data\PersistenceTrackingDataImplementation;

class GenericDataImplementation extends PersistenceTrackingDataImplementation implements GenericData
{
    protected ?int $id;

    public function __construct(
        protected Type $type,
        protected int $record_id,
        protected string $import_id,
        protected string $title,
        protected string $description,
        protected int $position,
        protected bool $searchable,
        protected bool $required,
        protected array $field_values,
        ?int $id = null
    ) {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function isPersisted(): bool
    {
        return !is_null($this->id());
    }

    protected function getSubData(): \Generator
    {
        yield from [];
    }


    public function getRecordID(): int
    {
        return $this->record_id;
    }

    public function setRecordID(int $id): void
    {
        if ($id === $this->record_id) {
            return;
        }
        $this->record_id = $id;
        $this->markAsChanged();
    }

    public function getImportID(): string
    {
        return $this->import_id;
    }

    public function setImportID(string $id): void
    {
        if ($id === $this->import_id) {
            return;
        }
        $this->import_id = $id;
        $this->markAsChanged();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        if ($title === $this->title) {
            return;
        }
        $this->title = $title;
        $this->markAsChanged();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        if ($description === $this->description) {
            return;
        }
        $this->description = $description;
        $this->markAsChanged();
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        if ($position === $this->position) {
            return;
        }
        $this->position = $position;
        $this->markAsChanged();
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function setSearchable(bool $searchable): void
    {
        if ($searchable === $this->searchable) {
            return;
        }
        $this->searchable = $searchable;
        $this->markAsChanged();
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        if ($required === $this->required) {
            return;
        }
        $this->required = $required;
        $this->markAsChanged();
    }

    public function getFieldValues(): array
    {
        return $this->field_values;
    }

    public function setFieldValues(array $values): void
    {
        if ($values === $this->field_values) {
            return;
        }
        $this->field_values = $values;
        $this->markAsChanged();
    }
}
