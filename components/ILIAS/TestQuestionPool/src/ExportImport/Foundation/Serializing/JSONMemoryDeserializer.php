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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;

/**
 * Simple deserializer that reads a JSON string from memory and invokes the registered handlers for each group found in
 * the source.
 */
class JSONMemoryDeserializer implements Deserializer
{
    private array $decoded = [];

    /** @var array<string, callable(array): void> $handler */
    private array $handler = [];

    /**
     * @inheritDoc
     */
    public function open(string $json): static
    {
        $clone = clone $this;
        $clone->decoded = json_decode($json, true);
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function addHandler(string $group, callable $handler): void
    {
        $this->handler[$group] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        foreach ($this->decoded as $key => $value) {
            if (is_array($value)) {
                $head = $value[array_key_first($value)];
                $value = array_is_list($head) ? $head : [$head];
            }

            if (isset($this->handler[$key])) {
                $this->handler[$key]($value);
            }
        }
    }
}
