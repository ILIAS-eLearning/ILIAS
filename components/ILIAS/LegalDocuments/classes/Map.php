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

namespace ILIAS\LegalDocuments;

use Closure;
use Exception;

class Map
{
    /**
     * @param array<string, list|array<string, mixed>> $map
     */
    public function __construct(
        private readonly array $map = [],
    ) {
    }

    public function add(string $name, $item): static
    {
        return $this->map($name, fn($list) => [...$list, $item]);
    }

    public function set(string $name, string $key, $item): static
    {
        return $this->map($name, fn(array $map) => [...$map, $key => $item]);
    }

    public function has(string $name, string $key): bool
    {
        return isset($this->map[$name][$key]);
    }

    public function append(self $other): self
    {
        array_map(function ($mine, $other) {
            $mine ??= [];
            $other ??= [];
            if (count($mine) + count($other) !== count(array_merge($mine, $other))) {
                throw new Exception('Cannot append maps. Keys must be distinct.');
            }
        }, $this->map, $other->value());
        return new self(array_merge_recursive($this->map, $other->value()));
    }

    public function value(): array
    {
        return $this->map;
    }

    /**
     * @param Closure(array): array $proc
     */
    private function map(string $key, Closure $proc): static
    {
        return new self([...$this->map, $key => $proc($this->map[$key] ?? [])]);
    }
}
