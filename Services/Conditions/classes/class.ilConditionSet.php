<?php declare(strict_types=1);

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
 * Condition set
 * Note: This object currently focuses on repository objects as targets. It does not make use of the
 * SHARED_CONDITIONS mode (ref_handling will be 1 for these items).
 * @author  killing@leifos.de
 * @ingroup ServicesConditions
 */
class ilConditionSet
{
    protected ?bool $hidden_status = null;
    protected ?bool $all_obligatory = null;
    /**
     * @var ilCondition[]
     */
    protected array $conditions;
    protected ?int $num_obligatory = null;

    /**
     * @param ilCondition[]
     */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return ilCondition[]
     */
    public function getConditions() : array
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
