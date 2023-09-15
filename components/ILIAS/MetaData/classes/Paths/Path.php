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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;

class Path implements PathInterface, \Stringable
{
    /**
     * @var StepInterface[]
     */
    protected array $steps;
    protected bool $is_relative;
    protected bool $leads_to_one;

    public function __construct(
        bool $is_relative,
        bool $leads_to_one,
        StepInterface ...$steps
    ) {
        $this->is_relative = $is_relative;
        $this->leads_to_one = $leads_to_one;
        $this->steps = $steps;
    }

    /**
     * @return StepInterface[]
     */
    public function steps(): \Generator
    {
        yield from $this->steps;
    }

    public function isRelative(): bool
    {
        return $this->is_relative;
    }

    public function leadsToExactlyOneElement(): bool
    {
        return $this->leads_to_one;
    }

    public function toString(): string
    {
        $string = '';

        if ($this->leadsToExactlyOneElement()) {
            $string .= Token::LEADS_TO_EXACTLY_ONE->value;
        }
        if ($this->isRelative()) {
            $string .= Token::START_AT_CURRENT->value;
        } else {
            $string .= Token::START_AT_ROOT->value;
        }
        foreach ($this->steps() as $step) {
            $string .= Token::SEPARATOR->value;
            $string .= $this->stepToString($step);
        }

        return $string;
    }

    protected function stepToString(StepInterface $step): string
    {
        $string = $step->name();
        if ($string instanceof StepToken) {
            $string = $string->value;
        }
        foreach ($step->filters() as $filter) {
            $string .= Token::FILTER_SEPARATOR->value .
                $filter->type()->value .
                Token::FILTER_VALUE_SEPARATOR->value;

            $string .= implode(
                Token::FILTER_VALUE_SEPARATOR->value,
                iterator_to_array($filter->values())
            );
        }
        return $string;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
