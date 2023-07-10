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

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;

/**
 * @author Stephan Kergomard
 */
class ilObjectCoreProperties
{
    private const FIELDS = [
        'object_id' => 'int',
        'owner' => 'int',
        'create_date' => 'DateTimeImmutable',
        'update_date' => 'DateTimeImmutable',
        'import_id' => 'text',
        'type' => 'text'
    ];
    private ?int $object_id = null;
    private ?string $type = null;
    private ?int $owner = null;
    private ?DateTimeImmutable $create_date = null;
    private ?DateTimeImmutable $update_date = null;
    private ?string $import_id = '';

    /**
     *
     * @param array<mixed> $data
     */
    public function __construct(
        private ilObjectPropertyTitleAndDescription $property_title_and_description,
        private ilObjectPropertyIsOnline $property_is_online,
        private ilObjectPropertyTileImage $property_tile_image,
        array $data = null
    ) {
        if ($this->checkDataArray($data)) {
            $this->setValuesByArray($data);
        }
    }

    public function getObjectId(): ?int
    {
        return $this->object_id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getOwner(): ?int
    {
        return $this->owner;
    }

    public function withOwner(int $owner): self
    {
        $clone = clone $this;
        $clone->owner = $owner;
        return $clone;
    }

    public function getCreateDate(): ?DateTimeImmutable
    {
        return $this->create_date;
    }

    public function getLastUpdateDate(): ?DateTimeImmutable
    {
        return $this->update_date;
    }

    public function withLastUpdateDate(DateTimeImmutable $update_date): self
    {
        $clone = clone $this;
        $clone->update_date = $update_date;
        return $clone;
    }

    public function getImportId(): string
    {
        return $this->import_id ?? '';
    }

    public function getPropertyTitleAndDescription(): ilObjectPropertyTitleAndDescription
    {
        return $this->property_title_and_description;
    }

    public function withPropertyTitleAndDescription(ilObjectPropertyTitleAndDescription $property_title_and_description): self
    {
        $clone = clone $this;
        $clone->property_title_and_description = $property_title_and_description;
        return $clone;
    }

    public function getPropertyIsOnline(): ilObjectPropertyIsOnline
    {
        return $this->property_is_online;
    }

    public function withPropertyIsOnline(ilObjectPropertyIsOnline $property_is_online): self
    {
        $clone = clone $this;
        $clone->property_is_online = $property_is_online;
        return $clone;
    }

    public function getPropertyTileImage(): ilObjectPropertyTileImage
    {
        return $this->property_tile_image;
    }

    public function withPropertyTileImage(ilObjectPropertyTileImage $property_tile_image): self
    {
        $clone = clone $this;
        $clone->property_tile_image = $property_tile_image;
        return $clone;
    }


    /**
     *
     * @param array<mixed> $data
     */
    protected function checkDataArray(?array $data): bool
    {
        if ($data === null) {
            return false;
        }

        if (array_diff_key(self::FIELDS, $data)
            || array_diff_key($data, self::FIELDS)) {
            return false;
        }

        if ($data['object_id'] === null || $data['owner'] === null) {
            return false;
        }

        if (!is_int($data['object_id']) || !is_int($data['owner'])) {
            return false;
        }

        return $this->checkTypesOfDataArray($data);
    }

    /**
     *
     * @param array<mixed> $data
     */
    protected function setValuesByArray(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function checkTypesOfDataArray(array $data): bool
    {
        foreach (self::FIELDS as $key => $value) {
            if ($data[$key] === null) {
                continue;
            }
            if ($value === 'int' && !is_int($data[$key])) {
                return false;
            }
            if ($value === 'text' && !is_string($data[$key])) {
                return false;
            }
            if ($value === 'bool' && !is_bool($data[$key])) {
                return false;
            }
            if ($value === 'DateTimeImmutable' && !$data[$key] instanceof DateTimeImmutable) {
                return false;
            }
        }
        return true;
    }
}
