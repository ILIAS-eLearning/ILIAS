<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilLTICronOutcomeService extends ilCronJob
{
	/**
	 * @inheritDoc
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}

	/**
	 * return int
	 */
	public function getDefaultScheduleValue()
	{
		return 5;
	}

	public function getId()
	{
		return 'lti_outcome';
	}

	public function hasAutoActivation()
	{
		return false;
	}

	public function hasFlexibleSchedule()
	{
		return true;
	}

	public function run()
	{
		
	}

}
?>