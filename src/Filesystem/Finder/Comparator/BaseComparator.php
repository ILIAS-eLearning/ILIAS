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

namespace ILIAS\Filesystem\Finder\Comparator;

use InvalidArgumentException;

/**
 * Class Base
 * @package ILIAS\Filesystem\Finder\Comparator
 * @author  Michael Jansen <mjansen@databay.de>
 */
abstract class BaseComparator
{
    private string $target = '';
    private string $operator = '==';

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): void
    {
        if ($operator === '') {
            $operator = '==';
        }

        if (!in_array($operator, ['>', '<', '>=', '<=', '==', '!='])) {
            throw new InvalidArgumentException(sprintf('Invalid operator "%s".', $operator));
        }

        $this->operator = $operator;
    }

    public function test(string $test): bool
    {
        return match ($this->operator) {
            '>' => $test > $this->target,
            '>=' => $test >= $this->target,
            '<' => $test < $this->target,
            '<=' => $test <= $this->target,
            '!=' => $test !== $this->target,
            default => $test === $this->target,
        };
    }
}
