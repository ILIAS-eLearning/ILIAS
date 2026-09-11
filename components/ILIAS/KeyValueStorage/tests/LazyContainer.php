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

namespace ILIAS\Tests\KeyValueStorage;

/**
 * Behaves like the containers the component bootstrap hands to init(): a
 * factory closure goes in, the built object comes out.
 *
 * @implements \ArrayAccess<string, mixed>
 */
class LazyContainer implements \ArrayAccess
{
    /** @var array<string, mixed> */
    private array $entries = [];

    /**
     * @param array<string, mixed> $ready_made objects that are already built
     */
    public function __construct(array $ready_made = [])
    {
        $this->entries = $ready_made;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->entries[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $entry = $this->entries[$offset] ?? throw new \OutOfBoundsException('Nothing registered for ' . $offset . '.');

        return $entry instanceof \Closure ? $entry() : $entry;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->entries[] = $value;

            return;
        }

        $this->entries[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->entries[$offset]);
    }
}
