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
	/** @var  ilUserOrgUnitHistorizingHelper $ilUserOrgUnitHistorizingHelper */
	protected static $ilUserOrgUnitHistorizingHelper;

	/** @var  ilUserOrgUnitHistorizing $ilUserOrgUnitHistorizing */
	protected static $ilUserOrgUnitHistorizing;

	static $relevant_events = array(
		'Services/AccessControl' 
			=> array(
			'removeUser',
			'deleteOrguRole',
			'assignUserOrguRole',
			'deassignUserOrguRole',
			'deassignUsersOrguRole'
			),
		'Modules/OrgUnit'
			=> array(
			'delete',
			'update'
			)
	);

	/**
	 * Handles raised events for ilUserOrgUnitHistorizing.
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

		if($component == 'Services/AccessControl') {
			switch ($event) {
				case 'removeUser':
					$case_id = array('usr_id' => $parameter['usr_id']);
					break;
				case 'deleteOrguRole':
					$case_id = array(
						'orgu_id' => $parameter['rol_obj_id'],
						'rol_id' => $parameter['rol_id']);
					break;
				case 'assignUserOrguRole':
					$case_id = array(
						'usr_id' => $parameter['usr_id'], 
						'orgu_id' => $parameter['rol_obj_id'],
						'rol_id' => $parameter['rol_id']);
					break;
				case 'deassignUserOrguRole':
					$case_id = array(
						'usr_id' => $parameter['usr_id'],
						'orgu_id' => $parameter['rol_obj_id'],
						'rol_id' => $parameter['rol_id']);
					break;
				case 'deassignUsersOrguRole':
					$case_id = array(
						'orgu_id' => $parameter['rol_obj_id'],
						'rol_id' => $parameter['rol_id']);
					break;
			}
		}	
		if($component == 'Modules/OrgUnit') {
			switch ($event) {
				case 'delete':
					$case_id = array('orgu_id' => $parameter['obj_id']);
					break;
				case 'update':
					$case_id = array('orgu_id' => $parameter['obj_id']);
					break;		
			}
		}
		return $case_id;		
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
		if($component == 'Services/AccessControl') {
			switch ($event) {
				case 'removeUser':
					$data_payload = array('action' => -1);
					break;
				case 'deleteOrguRole':
					$data_payload = array('action' => -1);				
					break;
				case 'assignUserOrguRole':
					$data_payload = array('action' => 1);
					$orgus_above = 
						self::$ilUserOrgUnitHistorizingHelper
						->getOrgUnitsAboveOf($parameter['rol_obj_id']);
					$data_payload['org_unit_above1'] = $orgus_above[0];
					$data_payload['org_unit_above2'] = $orgus_above[1];	
					$data_payload['orgu_title'] = $parameter['rol_obj']->getTitle();
					$role_title = $parameter['rol']->getTitle();
					if(strpos($role_title, 'il_orgu_employee_') !== false) {
						$role_title = 'Mitarbeiter';
					} elseif(strpos($role_title, 'il_orgu_superior_') !== false) {
						$role_title = 'Vorgesetzter';
					}
					$data_payload['rol_title'] = $role_title;
					break;
				case 'deassignUserOrguRole':
					$data_payload = array('action' => -1);
					break;
				case 'deassignUsersOrguRole':
					$data_payload = array('action' => -1);
					break;
			}
		}
		if($component == 'Modules/OrgUnit') {
			switch ($event) {
				case 'delete':
					$data_payload = array('action' => -1);	
					break;
				case 'update':
					$data_payload = array('action' => 0, 'orgu_title' => $parameter['orgu_title']);
					break;
			}
		}
		return $data_payload;
	}

	protected static function massAction($a_component, $a_event) {
		if($a_component == 'Services/AccessControl') {
			switch ($a_event) {
				case 'removeUser':
					return true;
				case 'deleteOrguRole':
					return true;		
				case 'assignUserOrguRole':
					return false;
				case 'deassignUserOrguRole':
					return false;
				case 'deassignUsersOrguRole':
					return true;
			}
		}
		if($a_component == 'Modules/OrgUnit') {
			switch ($a_event) {
				case 'delete':
					return true;
				case 'update':
					return true;
			}
		}
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
		return $parameter['creation_timestamp'] ? $parameter['creation_timestamp'] : time();
	}
}