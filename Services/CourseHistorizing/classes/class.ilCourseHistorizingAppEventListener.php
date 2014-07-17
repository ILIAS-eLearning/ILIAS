<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseHistorizingAppEventHandler
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilCourseHistorizingAppEventListener
{
	/** @var  ilCourseHistorizingHelper $ilCourseHistorizingHelper */
	protected static $ilCourseHistorizingHelper;

	/** @var  ilCourseHistorizing $ilCourseHistorizing */
	protected static $ilCourseHistorizing;

	/**
	 * Handles raised events for ilCourseHistorizing.
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

		if( $a_component == 'Modules/Course' && $a_event == 'create' )
		{
			self::$ilCourseHistorizing->updateHistorizedData(
				self::getCaseId($a_event, $a_parameter['object']), 
				self::getStateData($a_event, $a_parameter['object']), 
				self::getRecordCreator($a_event, $a_parameter['object']), 
				self::getCreationTimestamp($a_event, $a_parameter['object']), 
				false // Not a mass-action
			);
		}

		if( $a_component == 'Modules/Course' && $a_event == 'update' )
		{
			self::$ilCourseHistorizing->updateHistorizedData(
				self::getCaseId($a_event, $a_parameter['object']),
				self::getStateData($a_event, $a_parameter['object']),
				self::getRecordCreator($a_event, $a_parameter['object']),
				self::getCreationTimestamp($a_event, $a_parameter['object']),
				false // Not a mass-action
			);
		}

		if( $a_component == 'Modules/Course' && $a_event == 'addParticipant' )
		{
			ilObjectFactory::getClassByType('crs');
			$object = new ilObjCourse($a_parameter['obj_id'],false);
			self::$ilCourseHistorizing->updateHistorizedData(
				self::getCaseId($a_event, $object),
				self::getStateData($a_event, $object),
				self::getRecordCreator($a_event, $object),
				self::getCreationTimestamp($a_event, $object),
				false // Not a mass-action
			);
		}

	}

	/**
	 * Initializes the static members of the class.
	 * 
	 * @static
	 */
	protected static function initEventHandler()
	{
		if (!self::$ilCourseHistorizing)
		{
			require_once 'class.ilCourseHistorizing.php';
			self::$ilCourseHistorizing = new ilCourseHistorizing();
		}

		if(!self::$ilCourseHistorizingHelper)
		{
			require_once 'class.ilCourseHistorizingHelper.php';
			self::$ilCourseHistorizingHelper = ilCourseHistorizingHelper::getInstance();
		}
	}

	/**
	 * Returns the correct case ID for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @param 	string 			$event 		Name of the event
	 * @param 	ilObjCourse 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the case id. (@see ilCourseHistorizing, ilHistorizingStorage)
	 */
	protected static function getCaseId($event, $parameter)
	{
		return array( 'crs_id' => $parameter->getId() );
	}

	/**
	 * Returns the full state data for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @param 	string 			$event 		Name of the event
	 * @param 	ilObjCourse 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the cases data state. (@see ilCourseHistorizing, ilHistorizingStorage)
	 */
	protected static function getStateData($event, $parameter)
	{
		$begin_date = self::$ilCourseHistorizingHelper->getBeginOf($parameter)->get(IL_CAL_DATE);
		$end_date = self::$ilCourseHistorizingHelper->getEndOf($parameter);

		$data_payload = array(
			'custom_id'							=> self::$ilCourseHistorizingHelper->getCustomIdOf($parameter),
			'title'								=> $parameter->getTitle(),
			'template_title'					=> self::$ilCourseHistorizingHelper->getTemplateTitleOf($parameter),
			'type'								=> self::$ilCourseHistorizingHelper->getTypeOf($parameter),
			'topic_set'							=> self::$ilCourseHistorizingHelper->getTopicOf($parameter),
			'begin_date'						=> $begin_date,
			'end_date'							=> $end_date,
			'hours'								=> self::$ilCourseHistorizingHelper->getHoursOf($parameter),
			'is_expert_course'					=> self::$ilCourseHistorizingHelper->isExpertCourse($parameter),
			'venue'								=> self::$ilCourseHistorizingHelper->getVenueOf($parameter),
			'provider'							=> self::$ilCourseHistorizingHelper->getProviderOf($parameter),
			'tutor'								=> self::$ilCourseHistorizingHelper->getTutorOf($parameter),
			'max_credit_points'					=> self::$ilCourseHistorizingHelper->getMaxCreditPointsOf($parameter),
			'fee'								=> self::$ilCourseHistorizingHelper->getFeeOf($parameter),
			'is_template'						=> self::$ilCourseHistorizingHelper->getIsTemplate($parameter)
		);

		return $data_payload;
	}

	/**
	 * Returns the correct record creator for the new record to be created.
	 *
	 * @static
	 * 
	 * @param	string	$event		Name of the event
	 * @param 	mixed	$parameter	Parameters for the event
	 * 
	 * @return 	string 	Record creator identifier. (@see ilUserHistorizing, ilHistorizingStorage)
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
	 * @static
	 * 
	 * @param	string	$event		Name of the event
	 * @param 	mixed	$parameter	Parameters for the event
	 * 
	 * @return 	string 	UNIX-Timestamp. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getCreationTimestamp($event, $parameter)
	{
		return time();
	}
}