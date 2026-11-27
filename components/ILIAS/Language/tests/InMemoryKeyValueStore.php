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

namespace ILIAS\Language\Tests;

use ILIAS\KeyValueStorage\Store;
use ILIAS\Refinery\Transformation;

final class InMemoryKeyValueStore implements Store
{
    /** @var array<string, mixed> */
    public array $values = [];

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key, Transformation $transformation): mixed
    {
        return $transformation->transform($this->values[$key] ?? null);
    }

    public function getMany(array $transformations): array
    {
        return array_map(
            fn(string $key): mixed => $this->get($key, $transformations[$key]),
            array_combine(array_keys($transformations), array_keys($transformations))
        );
    }

    public function keys(): array
    {
        $keys = array_map('strval', array_keys($this->values));
        sort($keys, SORT_STRING);

        return $keys;
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->values[$key]);
    }

    public function clear(): void
    {
        $this->values = [];
    }
}
