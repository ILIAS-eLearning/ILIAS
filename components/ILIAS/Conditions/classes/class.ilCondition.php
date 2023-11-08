<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Condition class
 * @author  killing@leifos.de
 * @ingroup ServicesConditions
 */
class ilCondition
{
    protected ilConditionTrigger $trigger;
    protected string $operator;
    protected ?string $value;
    protected bool $obligatory = false;
    protected int $id;

    public function __construct(ilConditionTrigger $trigger, string $operator, ?string $value = null)
    {
        $this->trigger = $trigger;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getTrigger(): ilConditionTrigger
    {
        return $this->trigger;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function withObligatory(bool $obligatory): ilCondition
    {
        $clone = clone $this;
        $clone->obligatory = $obligatory;
        return $clone;
    }

    public function getObligatory(): bool
    {
        return $this->obligatory;
    }

    public function withId(int $id): ilCondition
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
