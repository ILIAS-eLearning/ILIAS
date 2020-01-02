<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition class
 *
 * @author killing@leifos.de
 * @ingroup ServicesConditions
 */
class ilCondition
{
    /**
     * @var ilConditionTrigger
     */
    protected $trigger;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $obligatory;

    /**
     * @var int
     */
    protected $id;

    /**
     * Constructor
     */
    public function __construct(ilConditionTrigger $trigger, $operator, $value = null)
    {
        $this->trigger = $trigger;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Get trigger
     *
     * @return ilConditionTrigger trigger
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * Get operator
     *
     * @return string operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get value
     *
     * @return string value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set obligatory
     *
     * @param bool $obligatory obligatory
     * @return self
     */
    public function withObligatory($obligatory)
    {
        $clone = clone $this;
        $clone->obligatory = $obligatory;
        return $clone;
    }

    /**
     * Get obligatory
     *
     * @return bool obligatory
     */
    public function getObligatory()
    {
        return $this->obligatory;
    }

    /**
     * Set id
     *
     * @param int $id id
     * @return self
     */
    public function withId($id)
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    /**
     * Get id
     *
     * @return int id
     */
    public function getId()
    {
        return $this->id;
    }
}
