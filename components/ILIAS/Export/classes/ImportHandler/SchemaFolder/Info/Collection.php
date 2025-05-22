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

namespace ILIAS\Export\ImportHandler\SchemaFolder\Info;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\CollectionInterface as SchemaInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\HandlerInterface as SchemaInfoInterface;
use ilLogger;

class Collection implements SchemaInfoCollectionInterface
{
    /**
     * @var SchemaInfoInterface[]
     */
    protected array $elements;
    protected int $index;
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->elements = [];
        $this->index = 0;
        $this->logger = $logger;
    }

    public function withElement(
        SchemaInfoInterface $element
    ): SchemaInfoCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function getLatest(
        string $component,
        string $sub_type = ''
    ): SchemaInfoInterface|null {
        $current = null;
        foreach ($this->elements as $schema_info) {
            if (
                $schema_info->getComponent() !== $component ||
                $schema_info->getSubType() !== $sub_type
            ) {
                continue;
            }
            if (
                is_null($current) ||
                (
                    !is_null($current) &&
                    $current->getVersion()->isSmallerThan($schema_info->getVersion())
                )
            ) {
                $current = $schema_info;
            }
        }
        return $current;
    }

    public function getByVersion(
        Version $version,
        string $type,
        string $sub_type = ''
    ): SchemaInfoInterface|null {
        foreach ($this->elements as $schema_info) {
            if (
                $schema_info->getVersion()->equals($version) &&
                $schema_info->getComponent() === $type &&
                $schema_info->getSubtype() === $sub_type
            ) {
                return $schema_info;
            }
        }
        return null;
    }

    public function getByVersionOrLatest(
        Version $version,
        string $type,
        string $sub_type = ''
    ): SchemaInfoInterface|null {
        $schema_with_version = $this->getByVersion($version, $type, $sub_type);
        if (!is_null($schema_with_version)) {
            return $schema_with_version;
        }
        return $this->getLatest($type, $sub_type);
    }

    public function next(): void
    {
        $this->index++;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->index]);
    }

    public function key(): int
    {
        return $this->index;
    }

    public function current(): SchemaInfoInterface
    {
        return $this->elements[$this->index];
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
