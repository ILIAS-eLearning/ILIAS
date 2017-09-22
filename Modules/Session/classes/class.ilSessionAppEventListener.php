<?php
// cat-tms-patch start
require_once('./Modules/Session/classes/class.ilObjSession.php');

/**
 * Class ilSessionAppEventListener
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilSessionAppEventListener {

	protected static $ref_ids = array();


	/**
	 * Handle an event in a listener.
	 *
	 * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
	 * @param    string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @param    array $a_parameter  parameter array (assoc), array("name" => ..., "phone_office" => ...)
	 */
	static function handleEvent($a_component, $a_event, $a_parameter) {
		switch ($a_component) {
			case 'Modules/Course':
				switch ($a_event) {
					case 'update':
						self::updateSessionAppointments($a_parameter['object']);
						break;
				}
				break;
		}
	}

	/**
	 * Update sessions relative to course
	 *
	 * @param 	ilObjCourse 		$crs
	 * @return 	void
	 */
	protected static function updateSessionAppointments(ilObjCourse $crs)
	{
		$crs_start = $crs->getCourseStart();
		$sessions = self::getSessionsOfCourse($crs->getRefId());

		foreach ($sessions as $session)
		{
			$appointment 	= $session->getFirstAppointment();
			$start_time 	= $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i:s", "UTC");
			$end_time 		= $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i:s", "UTC");
			$offset 		= $appointment->getDaysOffset();

			$start_date 	= self::createDateTime(date("Y-m-d"), $start_time);
			$end_date 		= self::createDateTime(date("Y-m-d"), $end_time);

			if($crs_start)
			{
				$crs_start->increment(ilDateTime::DAY, --$offset);

				$date 		= $crs_start->get(IL_CAL_FKT_DATE, "Y-m-d");
				$start_date = self::createDateTime($date, $start_time);
				$end_date 	= self::createDateTime($date, $end_time);
			}

			$appointment->setStart($start_date);
			$appointment->setEnd($end_date);
			$appointment->update();
		}
	}

	/**
	 * Find sessions underneath course 
	 *
	 * @param 	int 			$crs_ref_id
	 * @return 	ilObjSession[]
	 */
	protected static function getSessionsOfCourse($crs_ref_id)
	{
		global $DIC;

		$g_tree 	= $DIC->repositoryTree();
		$ret 		= array();
		$sessions 	= $g_tree->getChildsByType($crs_ref_id, "sess");

		foreach($sessions as $session)
		{
			$ret[] = ilObjectFactory::getInstanceByRefId($session['ref_id']);
		}

		return $ret;
	}

	/**
	 * Creates a DateTime object in UTC timezone
	 *
	 * @param 	string 		$date
	 * @param 	string 		$time
	 * @return 	ilDateTime
	 */
	protected static function createDateTime($date, $time)
	{
		return new ilDateTime($date." ".$time, IL_CAL_DATETIME, 'UTC');
	}
}
// cat-tms-patch end
