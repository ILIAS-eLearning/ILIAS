<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserCourseStatusHistorizingAppEventHandler
 *
 * This class receives and handles events for the user-course-status historizing.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilUserCourseStatusHistorizingAppEventListener
{
	/** @var  ilUserHistorizingHelper $ilUserHistorizingHelper */
	protected static $ilUserHistorizingHelper;

	/** @var  ilUserCourseStatusHistorizingHelper $ilUserCourseStatusHistorizingHelper */
	protected static $ilUserCourseStatusHistorizingHelper;

	/** @var  ilCourseHistorizingHelper $ilCourseHistorizingHelper */
	protected static $ilCourseHistorizingHelper;

	/** @var  ilUserCourseStatusHistorizing $ilUserCourseStatusHistorizing */
	protected static $ilUserCourseStatusHistorizing;

	/**
	 * Handles raised events for ilUserCourseStatusHistorizing.
	 * 
	 * This method initializes the class, dispatches to helper methods and triggers historizing.
	 *
	 * @static
	 * 
	 * @param	string	$a_component	Component which has thrown the event to be handled.
	 * @param	string	$a_event		Name of the event
	 * @param 	mixed	$a_parameter	Parameters for the event
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		self::initEventHandler();
		
		// Normalize events parameters
		if ($a_event == "addParticipant" || $a_event == "deleteParticipant") {
			$a_parameter["crs_id"] = $a_parameter["obj_id"];
		}
		if ($a_event == "setStatusAndPoints") {
			$a_parameter["crs_id"] = $a_parameter["crs_obj_id"];
			$a_parameter["usr_id"] = $a_parameter["user_id"];
		}
		// TODO: normalized data from bill here.

		self::$ilUserCourseStatusHistorizing->updateHistorizedData(
			self::getCaseId($a_event, $a_parameter), 
			self::getStateData($a_event, $a_parameter), 
			self::getRecordCreator($a_event, $a_parameter), 
			self::getCreationTimestamp($a_event, $a_parameter), 
			false // Not a mass-action
		);
	}

	/**
	 * Initializes the static members of the class.
	 * 
	 * @static
	 */
	protected static function initEventHandler()
	{
		if (!self::$ilUserCourseStatusHistorizing)
		{
			require_once 'class.ilUserCourseStatusHistorizing.php';
			self::$ilUserCourseStatusHistorizing = new ilUserCourseStatusHistorizing();
		}

		if (!self::$ilUserCourseStatusHistorizingHelper)
		{
			require_once 'class.ilUserCourseStatusHistorizingHelper.php';
			self::$ilUserCourseStatusHistorizingHelper = ilUserCourseStatusHistorizingHelper::getInstance();
		}

		if(!self::$ilUserHistorizingHelper)
		{
			require_once '../../UserHistorizing/classes/class.ilUserHistorizingHelper.php';
			self::$ilUserHistorizingHelper = ilUserHistorizingHelper::getInstance();
		}

		if(!self::$ilCourseHistorizingHelper)
		{
			require_once '../../CourseHistorizing/classes/class.ilCourseHistorizingHelper.php';
			self::$ilCourseHistorizingHelper = ilCourseHistorizingHelper::getInstance();
		}

	}

	/**
	 * Returns the correct case ID for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @param 	string 	$event 		Name of the event
	 * @param 	mixed 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the case id. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getCaseId($event, $parameter)
	{
		/** @var ilObjUser $parameter */
		return array( 
			'usr_id' => $parameter["usr_id"], 
			'crs_id' => $parameter["crs_id"] 
		);
	}

	/**
	 * Returns the full state data for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @TODO	bill_id needs clarification, see below.
	 * @TODO	It is necessary to fixate, that $user_id and $course_id are available with all events parameters.
	 * 
	 * @param 	string 	$event 		Name of the event
	 * @param 	mixed 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the cases data state. (@see ilUserCourseStatusHistorizing, ilHistorizingStorage)
	 */
	protected static function getStateData($event, $parameter)
	{
		$user_id = $parameter["usr_id"];
		$course_id = $parameter["crs_id"];
		$data_payload = array(
			'credit_points'						=> self::$ilUserCourseStatusHistorizingHelper->getCreditPointsOf($user_id, $course_id),
			'bill_id'							=> self::$ilUserCourseStatusHistorizingHelper->getBillIdOf($user_id, $course_id),
			'booking_status'					=> self::$ilUserCourseStatusHistorizingHelper->getBookingStatusOf($user_id, $course_id),
			'participation_status'				=> self::$ilUserCourseStatusHistorizingHelper->getParticipationStatusOf($user_id, $course_id),
			'okz'								=> self::$ilUserHistorizingHelper->getOKZOf($user_id),
			'certificate'						=> self::$ilUserCourseStatusHistorizingHelper->hasCertificate ->  getCertificateOf($user_id, $course_id),
			'begin_date'						=> self::$ilCourseHistorizingHelper->getBeginOf($course_id)->get(IL_CAL_DATE),
			'end_date'							=> self::$ilCourseHistorizingHelper->getEndOf($course_id)->get(IL_CAL_DATE),
			'overnights'						=> self::$ilUserCourseStatusHistorizingHelper->getOvernightsOf($user_id, $course_id),
			'function'							=> self::$ilUserCourseStatusHistorizingHelper->getFunctionOf($user_id, $course_id)
		);

		return $data_payload;
	}

	/**
	 * Returns the correct record creator for the new record to be created.
	 *
	 * Parameters are handed in to achieve uniform method signatures and there is a possible
	 * perspective to use them, but in this implementation, they are unused.
	 * 
	 * @static
	 * 
	 * @param	string	$event		Name of the event
	 * @param 	mixed	$parameter	Parameters for the event
	 * 
	 * @return 	string 	Record creator identifier. (@see ilUserCourseStatusHistorizing, ilHistorizingStorage)
	 */
	protected static function getRecordCreator($event, $parameter)
	{
		/** @var ilObjUser $ilUser */
		global $ilUser;
		return $ilUser->getId();
	}

	/**
	 * Returns the correct creation timestamp for the new record to be created.
	 *
	 * Parameters are handed in to achieve uniform method signatures and there is a possible
	 * perspective to use them, but in this implementation, they are unused.
	 * 
	 * @static
	 * 
	 * @param	string	$event		Name of the event
	 * @param 	mixed	$parameter	Parameters for the event
	 * 
	 * @return 	string 	UNIX-Timestamp. (@see ilUserCourseStatusHistorizing, ilHistorizingStorage)
	 */
	protected static function getCreationTimestamp($event, $parameter)
	{
		return time();
	}
}