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
		
		if ($a_component == "Modules/OrgUnit") {
			if (in_array($a_event, array("delete", "initDefaultRoles","update"))) {
				return;
			}
			
			$a_parameter["user_obj"] = new ilObjUser($a_parameter["user_id"]);
		}
		global $ilAppEventHandler;
		$ilAppEventHandler->raise(
			'Services/UserHistorizing', 'usrhist_called', $a_parameter
		);
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
		//global $ilLog;
		if ($event === 'deleteUser') {
			return array( 'deleted' => 1);
		}
		
		$entry_date = self::$ilUserHistorizingHelper->getEntryDateOf($parameter['user_obj']);
		$exit_date = self::$ilUserHistorizingHelper->getExitDateOf($parameter['user_obj']);
		if ($entry_date != null)
		{
			$entry_date = $entry_date->get(IL_CAL_DATE);
		}
		if ($exit_date != null)
		{
			$exit_date = $exit_date->get(IL_CAL_DATE);
		}

		$certification_begins = self::$ilUserHistorizingHelper->getBeginOfCertificationPeriodOf($parameter['user_obj']);
		if ($certification_begins)
		{
			$certification_begins = $certification_begins->get(IL_CAL_DATE);
		}

		$org_units_above = self::$ilUserHistorizingHelper->getOrgUnitsAboveOf($parameter['user_obj']);
		
		$exit_date_wbd = self::$ilUserHistorizingHelper->getExitDateWBDOf($parameter['user_obj']);
		if ($exit_date_wbd != null)
		{
			$exit_date_wbd = $exit_date_wbd->get(IL_CAL_DATE);
		}





		/** @var ilObjUser $parameter */

		/*
			!!! also update 
			Services/UserHistorizing/classes/class.ilUserHistorizing.php
		*/
		$data_payload = array(
			'firstname'						=> $parameter['user_obj']->getFirstname(),
			'lastname'						=> $parameter['user_obj']->getLastname(),
			'gender'						=> $parameter['user_obj']->getGender(),
			'birthday'						=> $parameter['user_obj']->getBirthday(),
			'org_unit'						=> self::$ilUserHistorizingHelper->getOrgUnitOf($parameter['user_obj']),
			//'position_key'					=> self::$ilUserHistorizingHelper->getPositionKeyOf($parameter['user_obj']),
			'entry_date'					=> $entry_date,
			'exit_date'						=> $exit_date,
			'bwv_id'						=> self::$ilUserHistorizingHelper->getBWVIdOf($parameter['user_obj']),
			'okz'							=> self::$ilUserHistorizingHelper->getOKZOf($parameter['user_obj']),
			'begin_of_certification'		=> $certification_begins,
			'deleted'						=> 0,
			'email'							=> self::$ilUserHistorizingHelper->getEMailOf($parameter['user_obj']),
			//new 2014-10-14:
			'wbd_agent_status'				=> self::$ilUserHistorizingHelper->getWBDAgentStatusOf($parameter['user_obj']),
			'wbd_type'						=> self::$ilUserHistorizingHelper->getWBDTypeOf($parameter['user_obj']), 
			'wbd_email'						=> self::$ilUserHistorizingHelper->getWBDEMailOf($parameter['user_obj']),
			//new 2014-11-17:
			'job_number' 					=> self::$ilUserHistorizingHelper->getJobNumberOf($parameter['user_obj']), 
			'adp_number'					=> self::$ilUserHistorizingHelper->getADPNumberOf($parameter['user_obj']), 
			'position_key'					=> self::$ilUserHistorizingHelper->getPositionKeyOf($parameter['user_obj']), 
			'org_unit_above1'				=> $org_units_above[0],
			'org_unit_above2'				=> $org_units_above[1],
			
			//new 2014-11-30:
			'is_vfs'						=> self::$ilUserHistorizingHelper->isVFSOf($parameter['user_obj']),
			'is_active'						=> self::$ilUserHistorizingHelper->isActiveUser($parameter['user_obj']),

			//new 2015-06-05
			'exit_date_wbd'					=> $exit_date_wbd,
			'next_wbd_action'				=> self::$ilUserHistorizingHelper->getNextWBDAction($parameter['user_obj']),
			'login'							=> self::$ilUserHistorizingHelper->getLogin($parameter['user_obj'])
		);
		/*
		'street'	 	
		'zipcode'		
		'city'			
		'phone_nr'		
		'mobile_phone_nr'
		*/
		$address_data = self::$ilUserHistorizingHelper->getAddressDataOf($parameter['user_obj']);
		$data_payload = array_merge($data_payload, $address_data);
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