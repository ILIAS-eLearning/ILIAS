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

declare(strict_types=0);

namespace ILIAS\Tracking\View\PropertyList;

use ILIAS\Tracking\View\PropertyList\PropertyListInterface;

class PropertyList implements PropertyListInterface
{
    protected int $index;
    /**
     * @var string[] $values
     */
    protected array $values;
    /**
     * @var string[] $keys
     */
    protected array $keys;

    /**
     * @param array<string,string> $properties
     */
    public function __construct(
        array $properties
    ) {
        $this->index = 0;
        $this->values = array_values($properties);
        $this->keys = array_keys($properties);
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
        return isset($this->keys[$this->index]);
    }

    public function key(): string
    {
        return $this->keys[$this->index];
    }

    public function current(): string
    {
        return $this->values[$this->index];
    }

    public function count(): int
    {
        return count($this->keys);
    }
}
