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

namespace ILIAS\Export\ImportHandler\Schema\Info;

use ILIAS\Export\ImportHandler\I\Schema\Info\CollectionInterface as ilImportHandlerSchemaInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\Info\HandlerInterface as ilImportHandlerSchemaInfoInterface;
use ilLogger;

class Collection implements ilImportHandlerSchemaInfoCollectionInterface
{
    /**
     * @var ilImportHandlerSchemaInfoInterface[]
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
        ilImportHandlerSchemaInfoInterface $element
    ): ilImportHandlerSchemaInfoCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function sortByVersion(): void
    {
        uasort($this->elements, function (ilImportHandlerSchemaInfoInterface $a, ilImportHandlerSchemaInfoInterface $b): int {
            if ($a->getVersion()->equals($b->getVersion())) {
                return 0;
            }
            if ($a->getVersion()->isGreaterThan($b->getVersion())) {
                return 1;
            }
            return -1;
        });
    }

    public function getByType(
        string $component,
        string $sub_type = ''
    ): ilImportHandlerSchemaInfoCollectionInterface {
        $collection = clone $this;
        $new_elements = [];
        foreach ($collection->elements as $schema_info) {
            if ($schema_info->getComponent() === $component && $schema_info->getSubtype() === $sub_type) {
                $new_elements[] = $schema_info;
                $this->logger->info('Found version: ' . $schema_info->getFile()->getFilename());
            }
        }
        $collection->elements = $new_elements;
        return $collection;
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

    public function current(): ilImportHandlerSchemaInfoInterface
    {
        return $this->elements[$this->index];
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
