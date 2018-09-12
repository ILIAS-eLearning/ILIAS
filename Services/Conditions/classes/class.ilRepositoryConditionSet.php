<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition set for repository object
 *
 * Note: Currently one target ref id can have only one condition set in ILIAS.
 * Note: This object currently focuses on repository objects as targets. It does not make use of the SHARED_CONDITIONS mode (ref_handling will be 1 for these items).
 *
 * @author @leifos.de
 * @ingroup
 */
class ilRepositoryConditionSet
{
	/**
	 * @var bool
	 */
	protected $hidden_status;

	/**
	 * @var bool
	 */
	protected $all_obligatory;

	/**
	 * @var ilCondition[]
	 */
	protected $conditions;

	/**
	 * @var int
	 */
	protected $num_obligatory;

	/**
	 * Constructor
	 */
	public function __construct(array $conditions)
	{
	}

	/**
	 * Set hidden status
	 *
	 * @param bool $hidden_status hidden status
	 * @return self
	 */
	public function withHiddenStatus($hidden_status) {
		$clone = clone $this;
		$clone->hidden_status = $hidden_status;
		return $clone;
	}

	/**
	 * Get hidden status
	 *
	 * @return bool hidden status
	 */
	public function getHiddenStatus() {
		return $this->hidden_status;
	}

	/**
	 * Set all conditions being obligatory (standard behaviour)
	 */
	public function withAllObligatory() {
		$clone = clone $this;
		$clone->all_obligatory = true;
		return $clone;
	}

	/**
	 * Get with all obligatory
	 *
	 * @return bool with all obligatory
	 */
	public function getAllObligatory() {
		return $this->all_obligatory;
	}

	/**
	 * Set number of obligatory conditions
	 *
	 * @param int $num_obligatory number of obligatory conditions
	 * @return self
	 */
	public function withNumObligatory($num_obligatory) {
		$clone = clone $this;
		$clone->num_obligatory = $num_obligatory;
		return $clone;
	}
	
	/**
	 * Get number of obligatory conditions
	 *
	 * @return int number of obligatory conditions
	 */
	public function getNumObligatory() {
		return $this->num_obligatory;
	}
	
}