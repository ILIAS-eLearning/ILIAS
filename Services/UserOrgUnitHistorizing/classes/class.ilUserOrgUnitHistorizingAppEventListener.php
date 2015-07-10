<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserOrgUnitHistorizingAppEventHandler
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 *	based on work of Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilUserOrgUnitHistorizingAppEventListener {
	/** @var  ilUserrgUnitHistorizingHelper $ilUserrgUnitHistorizingHelper */
	protected static $ilUserOrgUnitHistorizingHelper;

	/** @var  ilUserrgUnitHistorizing $ilUserHistorizing */
	protected static $ilUserOrgUnitHistorizing;

	static $relevant_events = array(
		'Modules/OrgUnit' 
			=> array(
			'delete',
			'assignUsersToEmployeeRole',
			'assignUsersToSuperiorRole',
			'deassignUserFromEmployeeRole',
			'deassignUserFromSuperiorRole'),
		'Services/User' 
			=> array('deleteUser')
				);

	/**
	 * Handles raised events for ilUserrgUnitHistorizing.
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

		if(in_array($a_event, self::$relevant_events[$a_component])) {
			var_dump($a_event);
			echo "<br>";
			var_dump($a_component);

			self::$ilUserOrgUnitHistorizing->updateHistorizedData(
				self::getCaseId($a_component, $a_event, $a_parameter), 
				self::getStateData($a_component, $a_event, $a_parameter), 
				self::getRecordCreator($a_event, $a_parameter), 
				self::getCreationTimestamp($a_event, $a_parameter), 
				self::massAction($a_component, $a_event)
			);
		}
	}

	/**
	 * Initializes the static members of the class.
	 * 
	 * @static
	 */
	protected static function initEventHandler() {
		if (!self::$ilUserOrgUnitHistorizing)
		{
			require_once 'class.ilUserOrgUnitHistorizing.php';
			self::$ilUserOrgUnitHistorizing = new ilUserOrgUnitHistorizing();
		}

		if(!self::$ilUserOrgUnitHistorizingHelper)
		{
			require_once 'class.ilUserOrgUnitHistorizingHelper.php';
			self::$ilUserOrgUnitHistorizingHelper = ilUserOrgUnitHistorizingHelper::getInstance();
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
	protected static function getCaseId($component, $event, $parameter) {
		if ($event === 'deleteUser') {
			return array( 'user_id' => $parameter['usr_id']);
		}

		if($component == 'Modules/OrgUnit' && $event == 'delete') {
			return array('orgu_id' => $parameter['obj_id']);
		}

		if($component == 'Modules/OrgUnit') {
			return array(
				'orgu_id' => $parameter['obj_id'],
				'usr_id' => $parameter['user_id'],
				'role_id' => $parameter['role_id']);
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
	protected static function getStateData($component, $event, $parameter) {
		if($component == 'Modules/OrgUnit') {
			switch($event) {
				case 'delete':
					$data_payload = array('action' => -1);
					break;
				case 'assignUsersToEmployeeRole':
					$data_payload = array(
						'action' => 1,
						'orgu_title' => $parameter['object']->getTitle(),
						'usr_status' => 'employee');
					break;
				case 'assignUsersToSuperiorRole':
					$data_payload = array(
						'action' => 1,
						'orgu_title' => $parameter['object']->getTitle(),
						'usr_status' => 'superior');
					break;
				case 'deassignUserFromEmployeeRole':
				case 'deassignUserFromSuperiorRole':
					$data_payload = array(
						'action' => -1);
					break;
			}
		}
		if($component == 'Services/User' &&  $event == 'delete') {
			$data_payload = array('action' => -1);
		}
		return $data_payload;
	}

	protected static function massAction($a_component, $a_event) {
		if( $a_event == 'deleteUser' ) {
			return true;
		}		
		if( $a_event == 'delete' && $a_component == 'Modules/OrgUnit' ) {
			return true;
		}
		return false;
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
	protected static function getRecordCreator($event, $parameter) {
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
	protected static function getCreationTimestamp($event, $parameter) {
		return time();
	}
}