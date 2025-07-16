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

namespace ILIAS\Export\ExportHandler\Consumer\ExportConfig;

use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfigCollection;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\HandlerInterface as ExportConfigInterface;

class Collection implements ExportConfigCollection
{
    /**
     * @var ExportConfigInterface[]
     */
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function withElement(
        ExportConfigInterface $export_config
    ): ExportConfigCollection {
        $clone = clone $this;
        $clone->elements[] = $export_config;
        return $clone;
    }

    public function getElementByClassName(
        string $class_name
    ): ExportConfigInterface|null {
        $class_name = trim($class_name);
        foreach ($this->elements as $export_config) {
            if (strcmp(strtolower($class_name), strtolower($export_config::class)) === 0) {
                return $export_config;
            }
        }
        return null;
    }

    public function getElementByComponent(
        string $component
    ): ExportConfigInterface|null {
        # Expected component string structure: 'components/ILIAS/COPage'
        $component_parts = explode('/', $component);
        if (count($component_parts) === 3) {
            $class_name = 'il' . $component_parts[2] . 'ExportConfig';
            return $this->getElementByClassName($class_name);
        }
        return null;
    }

    public function current(): ExportConfigInterface
    {
        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
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

    public function count(): int
    {
        return count($this->elements);
    }
}
