<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition set
 * Note: This object currently focuses on repository objects as targets. It does not make use of the
 * SHARED_CONDITIONS mode (ref_handling will be 1 for these items).
 * @author  killing@leifos.de
 * @ingroup ServicesConditions
 */
class ilConditionSet
{
    /**
     * @var bool
     */
    protected ?bool $hidden_status;

    /**
     * @var bool
     */
    protected ?bool $all_obligatory;

    /**
     * @var ilCondition[]
     */
    protected array $conditions;

    /**
     * @var int
     */
    protected ?int $num_obligatory;

    /**
     * Constructor
     * @param ilCondition[]
     */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * Get conditions
     * @return ilCondition[] conditions
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Set hidden status (trigger objects should be hidden in presentation)
     */
    public function withHiddenStatus(bool $hidden_status) : ilConditionSet
    {
        $clone = clone $this;
        $clone->hidden_status = $hidden_status;
        return $clone;
    }

    public function getHiddenStatus() : ?bool
    {
        return $this->hidden_status;
    }

    /**
     * Set all conditions being obligatory (standard behaviour)
     */
    public function withAllObligatory() : ilConditionSet
    {
        $clone = clone $this;
        $clone->all_obligatory = true;
        return $clone;
    }

    public function getAllObligatory() : ?bool
    {
        return $this->all_obligatory;
    }

    /**
     * Set number of obligatory conditions
     */
    public function withNumObligatory(int $num_obligatory) : ilConditionSet
    {
        $clone = clone $this;
        $clone->num_obligatory = $num_obligatory;
        return $clone;
    }

    /**
     * Get number of obligatory conditions
     */
    public function getNumObligatory() : ?int
    {
        return $this->num_obligatory;
    }
}
