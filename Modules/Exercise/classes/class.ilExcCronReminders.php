<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for exercise reminders
 *
 * @author Jesús López <lopez@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCronReminders extends ilCronJob
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;


	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
	}

	public function getId()
	{
		return "exc_reminders";
	}

	public function getTitle()
	{
		$lng = $this->lng;

		$lng->loadLanguageModule("exc");

		return $lng->txt("exc_reminders_cron");
	}

	public function getDescription()
	{
		$lng = $this->lng;

		$lng->loadLanguageModule("exc");

		return $lng->txt("exc_reminders_cron_info");
	}

	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}

	public function getDefaultScheduleValue()
	{
		return;
	}

	public function hasAutoActivation()
	{
		return true;
	}

	public function hasFlexibleSchedule()
	{
		return true;
	}

	public function run()
	{
		include_once "Modules/Exercise/classes/class.ilExAssignmentReminder.php";

		$cron_status = ilCronJobResult::STATUS_NO_ACTION;

		$reminder = new ilExAssignmentReminder();

		$result = $reminder->sendReminders();

		/**
		 * TODO
		 * WORKING HERE
		 */

		$cron_result = new ilCronJobResult();
		$cron_result->setStatus($cron_status);

		return $cron_result;
	}
}

?>