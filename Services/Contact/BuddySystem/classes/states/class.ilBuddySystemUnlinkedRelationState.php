<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/class.ilAbstractBuddySystemRelationState.php';

/**
 * Class ilBuddySystemUnlinkedRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemUnlinkedRelationState extends ilAbstractBuddySystemRelationState
{
	/**
	 *  {@inheritDoc}
	 */
	public function isInitial()
	{
		return true;
	}

	/**
	 *  {@inheritDoc}
	 */
	public function getName()
	{
		return 'Unlinked';
	}

	/**
	 *  {@inheritDoc}
	 */
	public function getAction()
	{
		return 'unlink';
	}

	/**
	 * @return ilBuddySystemRelationState[]
	 */
	public function getPossibleTargetStates()
	{
		return array(
			new ilBuddySystemRequestedRelationState()
		);
	}

	/**
	 * @param ilBuddySystemRelation
	 */
	public function request(ilBuddySystemRelation $relation)
	{
		$relation->setState(new ilBuddySystemRequestedRelationState());
	}
}