<?php

declare(strict_types=1);

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

namespace ILIAS\ResourceStorage\Collection;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class ResourceCollection
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class ResourceCollection
{
    public const NO_SPECIFIC_OWNER = -1;

    private ResourceCollectionIdentification $identification;
    private array $resource_identifications = [];
    private int $owner = self::NO_SPECIFIC_OWNER;
    private string $title;

    public function __construct(
        ResourceCollectionIdentification $identification,
        int $owner,
        string $title
    ) {
        $this->identification = $identification;
        $this->owner = $owner;
        $this->title = 'default';//$title;
    }

    public function getIdentification(): ResourceCollectionIdentification
    {
        return $this->identification;
    }

    public function hasSpecificOwner(): bool
    {
        return $this->owner !== self::NO_SPECIFIC_OWNER;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function add(ResourceIdentification $identification): void
    {
        $this->resource_identifications[] = $identification;
    }

    public function remove(ResourceIdentification $identification): void
    {
        $this->resource_identifications = array_filter(
            $this->resource_identifications,
            function (ResourceIdentification $i) use ($identification) {
                return $i->serialize() !== $identification->serialize();
            }
        );
    }

    public function isIn(ResourceIdentification $identification): bool
    {
        foreach ($this->resource_identifications as $i) {
            if ($i->serialize() === $identification->serialize()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ResourceIdentification[]
     */
    public function getResourceIdentifications(): array
    {
        return $this->resource_identifications;
    }

    public function count(): int
    {
        return count($this->resource_identifications);
    }

    public function clear(): void
    {
        $this->resource_identifications = [];
    }
}
