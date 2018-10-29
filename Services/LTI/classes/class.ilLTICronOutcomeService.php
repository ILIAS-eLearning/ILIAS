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
	 * @inheritdoc
	 */
	public function getDefaultScheduleValue()
	{
		return 5;
	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return 'lti_outcome';
	}

	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$status = \ilCronJobResult::STATUS_NO_ACTION;

		$result = new \ilCronJobResult();
		$result->setStatus($status);

		return $result;
	}
}