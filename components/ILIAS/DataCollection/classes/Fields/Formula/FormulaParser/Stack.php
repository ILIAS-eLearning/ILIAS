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

namespace ILIAS\components\DataCollection\Fields\Formula\FormulaParser;

class Stack
{
    protected array $stack = [];

    /**
     * @param float|int|string $elem
     */
    public function push($elem): void
    {
        $this->stack[] = $elem;
    }

    /**
     * @return float|int|string|null
     */
    public function pop()
    {
        if (!$this->isEmpty()) {
            $last_index = count($this->stack) - 1;
            $elem = $this->stack[$last_index];
            unset($this->stack[$last_index]);
            $this->stack = array_values($this->stack); // re-index

            return $elem;
        }

        return null;
    }

    /**
     * @return float|int|string|null
     */
    public function top()
    {
        if (!$this->isEmpty()) {
            return $this->stack[count($this->stack) - 1];
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return !(bool) count($this->stack);
    }

    public function reset(): void
    {
        $this->stack = [];
    }

    public function count(): int
    {
        return count($this->stack);
    }
}
