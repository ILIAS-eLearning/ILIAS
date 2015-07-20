<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserRoleHistorizingAppEventHandler
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 *  based on work of Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilUserRoleHistorizingAppEventListener {
	/** @var  ilUserRoleHistorizingHelper $ilUserRoleHistorizingHelper */
	protected static $ilUserRoleHistorizingHelper;

	/** @var  ilUserRoleHistorizing $ilUserRoleHistorizing */
	protected static $ilUserRoleHistorizing;

	static $relevant_events = array(
		'Services/AccessControl' 
			=> array(
			'removeUser',
			'deleteGlobalRole',
			'assignUserGlobalRole',
			'deassignUserGlobalRole',
			'deassignUsersGlobalRole'
			)
	);

	/**
	 * Handles raised events for ilUserRoleHistorizing.
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
			self::$ilUserRoleHistorizing->updateHistorizedData(
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
		if (!self::$ilUserRoleHistorizing)
		{
			require_once 'class.ilUserRoleHistorizing.php';
			self::$ilUserRoleHistorizing = new ilUserRoleHistorizing();
		}
		/*
		if(!self::$ilUserRoleHistorizingHelper)
		{
			require_once 'class.ilUserRoleHistorizingHelper.php';
			self::$ilUserRoleHistorizingHelper = ilUserRoleHistorizingHelper::getInstance();
		}*/
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
				case 'deleteGlobalRole':
					$case_id = array('rol_id' => $parameter['rol_id']);
					break;
				case 'assignUserGlobalRole':
					$case_id = array(
						'usr_id' => $parameter['usr_id'], 
						'rol_id' => $parameter['rol_id']);
					break;
				case 'deassignUserGlobalRole':
					$case_id = array(
						'usr_id' => $parameter['usr_id'], 
						'rol_id' => $parameter['rol_id']);
					break;
				case 'deassignUsersGlobalRole':
					$case_id = array('rol_id' => $parameter['rol_id']);
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
				case 'deleteGlobalRole':
					$data_payload = array('action' => -1);				
					break;
				case 'assignUserGlobalRole':
					$data_payload = array('action' => 1);
					$data_payload['rol_title'] = $parameter['rol']->getTitle(); 
					break;
				case 'deassignUserGlobalRole':
					$data_payload = array('action' => -1);
					break;
				case 'deassignUsersGlobalRole':
					$data_payload = array('action' => -1);
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
					break;
				case 'deleteGlobalRole':
					return true;
					break;
				case 'assignUserGlobalRole':
					return false;
					break;
				case 'deassignUserGlobalRole':
					return false;
					break;
				case 'deassignUsersGlobalRole':
					return true;					
					break;
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