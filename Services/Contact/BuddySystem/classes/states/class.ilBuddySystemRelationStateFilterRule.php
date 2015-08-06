<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelation
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemRelationStateFilterRule
{
	/**
	 * @var ilBuddySystemRelation
	 */
	protected $relation;

	/**
	 * @param ilBuddySystemRelation $relation
	 */
	public function __construct(ilBuddySystemRelation $relation)
	{
		$this->relation = $relation;
	}

	/**
	 * @return ilBuddySystemCollection|ilBuddySystemRelationState[]
	 */
	public function getStates()
	{
		return $this->relation->getState()->getPossibleTargetStates()->filter($this);
	}

	/**
	 * @return bool
	 */
	abstract public function matches();

	/**
	 * @param ilBuddySystemRelationState $state
	 * @return boolean
	 */
	abstract public function __invoke(ilBuddySystemRelationState $state);
}