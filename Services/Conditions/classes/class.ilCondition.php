<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    protected ?bool $obligatory = null;
    protected int $id;

    /**
     * Constructor
     */
    public function __construct(ilConditionTrigger $trigger, string $operator, ?string $value = null)
    {
        $this->trigger = $trigger;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getTrigger() : ilConditionTrigger
    {
        return $this->trigger;
    }

    public function getOperator() : string
    {
        return $this->operator;
    }

    public function getValue() : ?string
    {
        return $this->value;
    }

    public function withObligatory(bool $obligatory) : ilCondition
    {
        $clone = clone $this;
        $clone->obligatory = $obligatory;
        return $clone;
    }

    public function getObligatory() : ?bool
    {
        return $this->obligatory;
    }

    public function withId(int $id) : ilCondition
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getId() : int
    {
        return $this->id;
    }
}
