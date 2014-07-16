<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserHistorizingAppEventHandler
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilUserHistorizingAppEventListener
{
	/** @var  ilUserHistorizingHelper $ilUserHistorizingHelper */
	protected static $ilUserHistorizingHelper;

	/** @var  ilUserHistorizing $ilUserHistorizing */
	protected static $ilUserHistorizing;

	/**
	 * Handles raised events for ilUserHistorizing.
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

		self::$ilUserHistorizing->updateHistorizedData(
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
		if (!self::$ilUserHistorizing)
		{
			require_once 'class.ilUserHistorizing.php';
			self::$ilUserHistorizing = new ilUserHistorizing();
		}

		if(!self::$ilUserHistorizingHelper)
		{
			require_once 'class.ilUserHistorizingHelper.php';
			self::$ilUserHistorizingHelper = ilUserHistorizingHelper::getInstance();
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
		if ($event === "deleteUser") {
			return array( 'user_id' => $parameter['usr_id']);
		}
		else {
			/** @var ilObjUser $parameter */
			return array( 'user_id' => $parameter['user_obj']->getId() );
		}
	}

	/**
	 * Returns the full state data for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @param 	string 	$event 		Name of the event
	 * @param 	mixed 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the cases data state. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getStateData($event, $parameter)
	{
		if ($event === 'deleteUser') {
			return array( 'deleted' => 1);
		}
		
		$entry_date = self::$ilUserHistorizingHelper->getEntryDateOf($parameter['user_obj'])->get(IL_CAL_DATE);
		$exit_date = self::$ilUserHistorizingHelper->getExitDateOf($parameter['user_obj']);
		if ($exit_date != null)
		{
			$exit_date = $exit_date->get(IL_CAL_DATE);
		}

		$certification_begins = self::$ilUserHistorizingHelper->getBeginOfCertificationPeriodOf($parameter['user_obj'])->get(IL_CAL_DATE);

		/** @var ilObjUser $parameter */
		$data_payload = array(
			'firstname'							=> $parameter['user_obj']->getFirstname(),
			'lastname'							=> $parameter['user_obj']->getLastname(),
			'gender'							=> $parameter['user_obj']->getGender(),
			'birthday'							=> $parameter['user_obj']->getBirthday(),
			'org_unit'							=> self::$ilUserHistorizingHelper->getOrgUnitOf($parameter['user_obj']),
			'position_key'						=> self::$ilUserHistorizingHelper->getPositionKeyOf($parameter['user_obj']),
			'entry_date'						=> $entry_date,
			'exit_date'							=> $exit_date,
			'bwv_id'							=> self::$ilUserHistorizingHelper->getBWVIdOf($parameter['user_obj']),
			'okz'								=> self::$ilUserHistorizingHelper->getOKZOf($parameter['user_obj']),
			'begin_of_certification'			=> $certification_begins,
			'deleted'							=> 0
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