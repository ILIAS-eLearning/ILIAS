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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Serializer;

/**
 * Simple serializer that creates a JSON string in memory. Due to its simplicity, it is not suitable for large datasets.
 */
class JSONMemorySerializer implements Serializer
{
    /**
     * @var list<array{name: string, data: array}> Stack of for nested structure
     */
    private array $stack = [];

    /**
     * @inheritDoc
     */
    public function open(string $path): static
    {
        $clone = clone $this;
        $clone->stack = [
            ['name' => '', 'data' => []],
        ];
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function startGroup(string $name): void
    {
        $this->stack[] = ['name' => $name, 'data' => []];
    }

    /**
     * @inheritDoc
     */
    public function endGroup(string $name): void
    {
        $frame = array_pop($this->stack);
        if ($frame['name'] !== $name) {
            throw new \LogicException(
                "Group name mismatch: expected end of '{$frame['name']}', got '{$name}'"
            );
        }
        $top = &$this->stack[array_key_last($this->stack)];
        $top['data'][$name] = $frame['data'];
    }

    /**
     * @inheritDoc
     */
    public function group(string $name, callable $callback): void
    {
        $this->startGroup($name);
        $callback();
        $this->endGroup($name);
    }

    /**
     * @inheritDoc
     */
    public function append(string $name, array $data): void
    {
        $top = &$this->stack[array_key_last($this->stack)];
        if (array_key_exists($name, $top['data'])) {
            $existing = $top['data'][$name];
            if (is_array($existing) && array_is_list($existing)) {
                $top['data'][$name][] = $data;
            } else {
                $top['data'][$name] = [$existing, $data];
            }
        } else {
            $top['data'][$name] = $data;
        }
    }

    /**
     * @inheritDoc
     */
    public function write(): string
    {
        if ($this->stack === []) {
            return '{}';
        }
        $root = $this->stack[0]['data'];
        $json = json_encode($root, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $this->stack = [
            ['name' => '', 'data' => []],
        ];
        return $json;
    }
}
