<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Help adapter for booking manager
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingHelpAdapter
{
	/**
	 * @var ilObjBookingPool
	 */
	protected $pool;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * Constructor
	 * @param ilObjBookingPool $pool
	 * @param ilHelpGUI $help
	 */
	public function __construct(ilObjBookingPool $pool, ilHelpGUI $help)
	{
		$this->pool = $pool;
		$this->help = $help;
	}

	/**
	 * @param string $a_id
	 */
	public function setHelpId(string $a_id)
	{
		$ilHelp = $this->help;

		$object_subtype = ($this->pool->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE)
			? '-schedule'
			: '-nonschedule';

		$ilHelp->setScreenIdComponent('book');
		$ilHelp->setScreenId('object'.$object_subtype);
		$ilHelp->setSubScreenId($a_id);
	}
}