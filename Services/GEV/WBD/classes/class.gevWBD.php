<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for WBD stuff.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/
require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");

class gevWBD {
	protected $udf_utils;
	protected $instance = array();

	//Constants
	//aus UserUtils
	const WBD_NO_SERVICE 		= "0 - kein Service";
	const WBD_EDU_PROVIDER		= "1 - Bildungsdienstleister";
	const WBD_TP_BASIS			= "2 - TP-Basis";
	const WBD_TP_SERVICE		= "3 - TP-Service";
	
	const WBD_OKZ_FROM_POSITION	= "0 - aus Rolle";
	const WBD_OKZ1				= "1 - OKZ1";
	const WBD_OKZ2				= "2 - OKZ2";
	const WBD_OKZ3				= "3 - OKZ3";
	const WBD_NO_OKZ			= "4 - keine Zuordnung";
	
	const WBD_AGENTSTATUS0	= "0 - aus Rolle";
	const WBD_AGENTSTATUS1	= "1 - Angestellter Außendienst";
	const WBD_AGENTSTATUS2	= "2 - Ausschließlichkeitsvermittler";
	const WBD_AGENTSTATUS3	= "3 - Makler";
	const WBD_AGENTSTATUS4	= "4 - Mehrfachagent";
	const WBD_AGENTSTATUS5	= "5 - Mitarbeiter eines Vermittlers";
	const WBD_AGENTSTATUS6	= "6 - Sonstiges";
	const WBD_AGENTSTATUS7	= "7 - keine Zuordnung";

	const WBD_ERROR_WRONG_USERDATA 		= 'WRONG_USERDATA'; 
	const WBD_ERROR_USER_SERVICETYPE 	= 'USER_SERVICETYPE'; 
	const WBD_ERROR_USER_DIFFERENT_TP 	= 'USER_DIFFERENT_TP'; 
	const WBD_ERROR_USER_UNKNOWN 		= 'USER_UNKNOWN';
	const WBD_ERROR_USER_DEACTIVATED 	= 'USER_DEACTIVATED';
	const WBD_ERROR_NO_RELEASE			= 'NO_RELEASE';
	const WBD_ERROR_USER_EXISTS_TP 		= 'USER_EXISTS_TP';
	const WBD_ERROR_USER_EXISTS 		= 'USER_EXISTS';
	const WBD_ERROR_USER_NOT_IN_POOL	= 'USER_NOT_IN_POOL';
	const WBD_ERROR_UNKNOWN 			= '-unknown-';

	//aus Settings
	const USR_TP_TYPE				= "usr_udf_tp_type";
	const USR_BWV_ID				= "usr_udf_bwv_id";

	const USR_WBD_NEXT_ACTION_NOTHING			= "0 - keine Aktion";
	const USR_WBD_NEXT_ACTION_NEW_TP_SERVICE	= "1 - Erstanlage TP Service";
	const USR_WBD_NEXT_ACTION_NEW_TP_BASIS		= "2 - Erstanlage TP Basis";
	const USR_WBD_NEXT_ACTION_AFFILIATE			= "3 - Aufnahme";
	const USR_WBD_NEXT_ACTION_RELEASE			= "4 - Transferfähig machen";

	const USR_WBD_STATUS			= "usr_udf_wbd_status";
	const USR_WBD_CERT_PERIOD_BEGIN = "usr_udf_wbd_cert_period_begin";
	const USR_WBD_DID_REGISTRATION	= "usr_udf_wbd_did_registration";
	const USR_WBD_COM_EMAIL			= "usr_udf_wbd_com_email";
	const USR_WBD_EXIT_DATE			= "usr_udf_wbd_exit_date";
	const USR_WBD_NEXT_ACTION		= "usr_udf_wbd_next_action";
	const USR_WBD_TP_SERVICE_OLD	= "usr_udf_wbd_tp_service_old";
	const USR_WBD_OKZ				= "usr_udf_wbd_okz";

	//WBD Perioden Definer
	const WBD_YEARS_FOR_A_PERIOD 	= 5;
	const WBD_YEARS_FOR_A_YEAR 		= 1;

	//Statics
	static protected $instances = array(); 

	static $wbd_agent_status_mapping = array(
		//1 - Angestellter Außendienst
		self::WBD_AGENTSTATUS1 => array(
			/* GOA V1:
			"OD/LD/BD/VD/VTWL"
			,"DBV/VL-EVG"
			,"DBV-UVG"
			*/
			"OD /BD"
			,"OD/BD"
			,"OD"
			,"BD"
			,"FD"
			,"Org PV 59"
			,"PV 59"
			,"Ausbildungsbeauftragter"
			,"VA 59"
			,"VA HGB 84"
			,"NFK"
			,"OD-Betreuer"
			,"DBV UVG"
			,"DBV EVG"
		),

		//2 - Ausschließlichkeitsvermittler
		self::WBD_AGENTSTATUS2 => array(
			/* GOA V1:
			"AVL"
			,"HA"
			,"BA"
			,"NA"
			*/
			"UA"
			,"HA 84"
			,"BA 84"
			,"NA"
			,"AVL" 
		),
		//3 - Makler
		self::WBD_AGENTSTATUS3 => array(
			"VP"
		),

		//5 - Mitarbeiter eines Vermittlers
		self::WBD_AGENTSTATUS5 => array(
			"Agt-ID"
		),
		//6 - Sonstiges
		self::WBD_AGENTSTATUS6 => array(
			'Administrator'
			,'Admin-Voll'
			,'Admin-eingeschraenkt'
			,'Admin-Ansicht'
			,'ID FK'
			,'ID MA'
			,'OD/FD/BD ID'
			,'OD/FD ID'
			,'BD ID'
			,'FDA'
			,'Ausbilder'
			,'Azubi'
			,'Veranstalter'
			,'int. Trainer'
			,'ext. Trainer'
			,'TP Service'
			,'TP Basis'
			,'VFS'

		)

	);

	static $wbd_tp_service_roles = array(
		"UA"
		,"HA 84"
		,"BA 84"
		,"Org PV 59"
		,"PV 59"
		,"AVL"
		,"DBV UVG"
		,"DBV EVG"
		,"TP Service"
	);
	
	static $wbd_relevant_roles = array(
		"UA"
		,"HA 84"
		,"BA 84"
		,"Org PV 59"
		,"PV 59"
		,"AVL"
		,"DBV UVG"
		,"DBV EVG"
		,"TP Service"
		,"TP Basis"
		,"VP"
	);


	protected function __construct($a_user_id) {
		global $ilDB;

		$this->gDB = $ilDB;
		$this->user_id = $a_user_id;
		$this->user_utils = gevUserUtils::getInstance($a_user_id);
		$this->udf_utils = gevUDFUtils::getInstance();
	}

	static public function getInstance($a_user_id) {
		if($a_user_id === null) {
			throw new Exception("gevWBD::getInstance: ".
								"No User-ID given.");
		}

		if(!self::userIdExists($a_user_id)) {
			throw new Exception("gevWBD::getInstance: ".
									"User with ID '".$a_user_id."' does not exist.");
		}

		if (array_key_exists($a_user_id, self::$instances)) {
			return self::$instances[$a_user_id];
		}

		self::$instances[$a_user_id] = new gevWBD($a_user_id);
		return self::$instances[$a_user_id];
	}

	static public function userIdExists($a_user_id) {
		global $ilDB;

		$sql = "SELECT usr_id FROM usr_data WHERE usr_id = ".$ilDB->quote($a_user_id, "integer");
		$res = $ilDB->query($sql);

		if($ilDB->numRows($res) == 0) {
			return false;
		}

		return true;
	}

	static public function getInstanceByObj(ilObjUser $a_user_obj) {
		$inst = self::getInstance($a_user_obj->getId());
		$inst->user_obj = $a_user_obj;
		return $inst;
	}
	
	static public function getInstanceByObjOrId($a_user) {
		if (is_int($a_user) || is_numeric($a_user)) {
			return self::getInstance((int)$a_user);
		}
		else {
			return self::getInstanceByObj($a_user);
		}
	}

	public function forceWBDUserProfileFields() {
		return $this->hasWBDRelevantRole()
			&& $this->hasDoneWBDRegistration()
			&& (  ($this->getNextWBDAction() == self::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE
					|| $this->getNextWBDAction() == self::USR_WBD_NEXT_ACTION_NEW_TP_BASIS
					|| $this->getNextWBDAction() == self::USR_WBD_NEXT_ACTION_AFFILIATE
				  ) ||
				  ($this->getWBDTPType() == self::WBD_TP_BASIS
				  	|| $this->getWBDTPType() == self::WBD_TP_SERVICE)
				);
	}

	public function getWBDTPType() {
		return $this->udf_utils->getField($this->user_id, self::USR_TP_TYPE);
	}
	
	public function setWBDTPType($a_type) {
		if (!in_array($a_type, array( self::WBD_NO_SERVICE, self::WBD_EDU_PROVIDER
									, self::WBD_TP_BASIS, self::WBD_TP_SERVICE))
			) {
			throw new Exception("gevWBD::setWBDTPType: ".$a_type." is no valid type.");
		}

		$this->udf_utils->setField($this->user_id, self::USR_TP_TYPE, $a_type);
	}

	public function getNextWBDAction() {
		return $this->udf_utils->getField($this->user_id, self::USR_WBD_NEXT_ACTION);
	}

	public function setNextWBDAction($action) {
		$this->udf_utils->setField($this->user_id, self::USR_WBD_NEXT_ACTION, $action);
	}
	
	public function getWBDBWVId() {
		return $this->udf_utils->getField($this->user_id, self::USR_BWV_ID);
	}
	
	public function setWBDBWVId($a_id) {
		$this->udf_utils->setField($this->user_id, self::USR_BWV_ID, $a_id);
	}
	
	public function setTPServiceOld($tp_service_old) {
		$this->udf_utils->setField($this->user_id, self::USR_WBD_TP_SERVICE_OLD, $tp_service_old);
	}

	public function getTPServiceOld() {
		return $this->udf_utils->getField($this->user_id, self::USR_WBD_TP_SERVICE_OLD);
	}

	protected function getRawWBDOKZ() {
		return $this->udf_utils->getField($this->user_id, self::USR_WBD_OKZ);
	}
	
	public function setRawWBDOKZ($a_okz) {
		if (!in_array($a_okz, array( self::WBD_OKZ_FROM_POSITION, self::WBD_NO_OKZ
								   , self::WBD_OKZ1, self::WBD_OKZ2, self::WBD_OKZ3))
		   ) {
			throw new Exception("gevWBD::setRawWBDOKZ: ".$a_okz." is no valid okz.");
		}
		
		return $this->udf_utils->getField($this->user_id, self::USR_WBD_OKZ, $a_okz);
	}
	
	public function getWBDOKZ() {
		$okz = $this->getRawWBDOKZ();
		
		if ($okz == WBD_NO_OKZ) {
			return null;
		}
		
		if (in_array($okz, array(self::WBD_OKZ1, self::WBD_OKZ2, self::WBD_OKZ3))) {
			$spl = explode("-", $okz);
			return trim($spl[1]);
		}
		
		
		// Everyone who has a wbd relevant role also has okz1
		if ($this->hasWBDRelevantRole()) {
			return "OKZ1";
		}
		
		return;
	}

	public function getRawWBDAgentStatus() {
		return $this->udf_utils->getField($this->user_id, self::USR_WBD_STATUS);
	}

	public function getWBDAgentStatus() {
		$agent_status_user =  $this->getRawWBDAgentStatus();

		if(  $agent_status_user == self::WBD_AGENTSTATUS0
		  // When user gets created and nobody clicked "save" on his profile, the
		  // udf-field will not contain a value, thus getRawWBDAgentStatus returned null.
		  // The default for the agent status is to determine it based on the role of
		  // a user.
		  || $agent_status_user === null)
		{
			//0 - aus Stellung	//0 - aus Rolle
			require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
			$roles = $this->user_utils->getGlobalRoles();
			foreach($roles as $key => $value) {
				$roles[$key] = ilObject::_lookupTitle($value);
			}
		
			foreach (self::$wbd_agent_status_mapping as $agent_status => $relevant_roles) {
				foreach ($roles as $role) {
					if(in_array($role, $relevant_roles)){
						$ret = explode("-", $agent_status);
						return trim($ret[1]);
					}
				}
			}
			
			return null;
		}
		$ret = explode("-", $agent_status_user);
		return trim($ret[1]);
	}
	
	public function setRawWBDAgentStatus($a_state) {
	
		if (!in_array($a_state, array( self::WBD_AGENTSTATUS0,
									   self::WBD_AGENTSTATUS1,
									   self::WBD_AGENTSTATUS2,
									   self::WBD_AGENTSTATUS3,
									   self::WBD_AGENTSTATUS4,
									   self::WBD_AGENTSTATUS5,
									   self::WBD_AGENTSTATUS6,
									   self::WBD_AGENTSTATUS7,
									   )
				)
			) {
			throw new Exception("gevWBD::setWBDAgentStatus: ".$a_state." is no valid agent status.");
		}
		
		return $this->udf_utils->setField($this->user_id, self::USR_WBD_STATUS, $a_state);
	}
	
	static public function isValidBWVId($a_id) {
		return 1 == preg_match("/\d{8}-.{6}-../", $a_id);
	}
	
	public function transferPointsToWBD() {
		return (   in_array($this->getWBDOKZ(), 
							array("OKZ1", "OKZ2", "OKZ3"))
				&& in_array($this->getWBDTPType(), 
							array(self::WBD_EDU_PROVIDER, self::WBD_TP_BASIS, self::WBD_TP_SERVICE))
				&& $this->getWBDBWVId()
				);
	}
	
	public function transferPointsFromWBD() {
		return (   in_array($this->getWBDOKZ(), 
							array("OKZ1", "OKZ2", "OKZ3"))
				&& $this->getWBDTPType() == self::WBD_TP_SERVICE
				&& $this->getWBDBWVId()
				);
	}
	
	public function wbdRegistrationIsPending() {
		return (   in_array($this->getWBDOKZ(), 
							array("OKZ1", "OKZ2", "OKZ3"))
				&& in_array($this->getNextWBDAction(),
							array(self::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE, self::USR_WBD_NEXT_ACTION_NEW_TP_BASIS)
							)
				);	
	}
	
	public function getWBDFirstCertificationPeriodBegin() {
		$date = $this->udf_utils->getField($this->user_id, self::USR_WBD_CERT_PERIOD_BEGIN);
		return new ilDate($date, IL_CAL_DATE);
	}
	
	public function setWBDFirstCertificationPeriodBegin(ilDate $a_start) {
		$this->udf_utils->setField($this->user_id, self::USR_WBD_CERT_PERIOD_BEGIN, $a_start->get(IL_CAL_DATE));
	}

	public function hasWBDRelevantRole() {
		$query = "SELECT COUNT(*) cnt "
				."  FROM rbac_ua ua "
				."  JOIN object_data od ON od.obj_id = ua.rol_id "
				." WHERE ua.usr_id = ".$this->gDB->quote($this->user_id, "integer")
				."   AND od.type = 'role' "
				."   AND ".$this->gDB->in("od.title", self::$wbd_relevant_roles, false, "text")
				;

		$res = $this->gDB->query($query);
		if ($rec = $this->gDB->fetchAssoc($res)) {
			return $rec["cnt"] > 0;
		}
		return false;
	}

	public function hasDoneWBDRegistration() {
		return ($this->udf_utils->getField($this->user_id, self::USR_WBD_DID_REGISTRATION) == "1 - Ja");
	}
	
	public function setWBDRegistrationDone() {
		$this->udf_utils->setField($this->user_id, self::USR_WBD_DID_REGISTRATION, "1 - Ja");
	}
	public function setWBDRegistrationNotDone() {
		$this->udf_utils->setField($this->user_id, self::USR_WBD_DID_REGISTRATION, "0 - Nein");
	}
	
	public function canBeRegisteredAsTPService() {
		$query = "SELECT COUNT(*) cnt "
				."  FROM rbac_ua ua "
				."  JOIN object_data od ON od.obj_id = ua.rol_id "
				." WHERE ua.usr_id = ".$this->gDB->quote($this->user_id, "integer")
				."   AND od.type = 'role' "
				."   AND ".$this->gDB->in("od.title", self::$wbd_tp_service_roles, false, "text")
				;

		$res = $this->gDB->query($query);
		if ($rec = $this->gDB->fetchAssoc($res)) {
			return $rec["cnt"] > 0;
		}
		return false;
	}

	public function getWBDCommunicationEmail() {
		return $this->udf_utils->getField($this->user_id, self::USR_WBD_COM_EMAIL);
	}
	
	public function setWBDCommunicationEmail($a_email) {
		$this->udf_utils->setField($this->user_id, self::USR_WBD_COM_EMAIL, $a_email);
	}
	
	public function getExitDateWBD() {
		$date = $this->udf_utils->getField($this->user_id, self::USR_WBD_EXIT_DATE);
		if (!trim($date)) {
			return null;
		}

		return new ilDate($date, IL_CAL_DATE);
	}

	/**
	* Checks requirements user must have to get in pool for new wbd account for TPS
	*
	* WBD Resistration done
	* has specified Role
	* is an existing user
	* is aktive
	* is not root oder anomynos
	* entry date is passed
	* has no BWV Id
	* has specifed TP-Types
	*
	* @return boolean
	*/
	public function shouldBeRegisteredAsNewTPServiceChecks() {
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasDoneWBDRegistration.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasWBDRelevantRole.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryUserExists.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryIsActiveUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHandleUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryEntryDatePassed.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryBWVIdIsEmpty.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNotWBDType.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNoOpenWBDError.php");

		$wbd_errors = array(self::WBD_ERROR_WRONG_USERDATA
							, self::WBD_ERROR_USER_SERVICETYPE
							, self::WBD_ERROR_USER_EXISTS_TP
							, self::WBD_ERROR_USER_EXISTS
							, self::WBD_ERROR_UNKNOWN);

		return array(new WBDPreliminaryHasDoneWBDRegistration()
					, new WBDPreliminaryHasWBDRelevantRole()
					, new WBDPreliminaryUserExists()
					, new WBDPreliminaryIsActiveUser()
					, new WBDPreliminaryHandleUser(array(6,13))
					, new WBDPreliminaryEntryDatePassed()
					, new WBDPreliminaryBWVIdIsEmpty()
					, new WBDPreliminaryHasNotWBDType(self::WBD_NO_SERVICE)
					, new WBDPreliminaryHasNoOpenWBDError($wbd_errors)
					);
	}

	/**
	* Checks requirements user must have to get in pool for new wbd account for TPB
	*
	* WBD Resistration done
	* has specified Role
	* is an existing user
	* is aktive
	* is not root oder anomynos
	* has no BWV Id
	* has specifed TP-Types
	*
	* @return boolean
	*/
	public function shouldBeRegisteredAsNewTPBasis() {
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasDoneWBDRegistration.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasWBDRelevantRole.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryUserExists.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryIsActiveUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHandleUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryBWVIdIsEmpty.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNotWBDType.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNoOpenWBDError.php");

		$wbd_errors = array(self::WBD_ERROR_WRONG_USERDATA
							, self::WBD_ERROR_USER_SERVICETYPE
							, self::WBD_ERROR_USER_EXISTS_TP
							, self::WBD_ERROR_USER_EXISTS
							, self::WBD_ERROR_UNKNOWN);

		return array(new WBDPreliminaryHasDoneWBDRegistration()
					, new WBDPreliminaryHasWBDRelevantRole()
					, new WBDPreliminaryUserExists()
					, new WBDPreliminaryIsActiveUser()
					, new WBDPreliminaryHandleUser(array(6,13))
					, new WBDPreliminaryBWVIdIsEmpty()
					, new WBDPreliminaryHasNotWBDType(self::WBD_NO_SERVICE)
					, new WBDPreliminaryHasNoOpenWBDError($wbd_errors)
					);
	}
	
	/**
	* Checks requirements user must have to get in pool for affiliate as TP-Service
	*
	* WBD Resistration done
	* has specified Role
	* is an existing user
	* is aktive
	* is not root oder anomynos
	* entry date is passed
	* has BWV Id
	* has not specifed TP-Type
	* has no open specified errors
	*
	* @return boolean
	*/
	public function shouldBeAffiliateAsTPServiceChecks() {
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasDoneWBDRegistration.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasWBDRelevantRole.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryUserExists.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryIsActiveUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHandleUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryEntryDatePassed.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryBWVIdIsNotEmpty.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNotWBDType.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNoOpenWBDError.php");

		$wbd_errors = array(self::WBD_ERROR_WRONG_USERDATA
							, self::WBD_ERROR_USER_SERVICETYPE
							, self::WBD_ERROR_USER_DIFFERENT_TP
							, self::WBD_ERROR_USER_UNKNOWN
							, self::WBD_ERROR_USER_DEACTIVATED
							, self::WBD_ERROR_USER_NOT_IN_POOL
							, self::WBD_ERROR_UNKNOWN
							);

		return array(new WBDPreliminaryHasDoneWBDRegistration()
					, new WBDPreliminaryHasWBDRelevantRole()
					, new WBDPreliminaryUserExists()
					, new WBDPreliminaryIsActiveUser()
					, new WBDPreliminaryHandleUser(array(6,13))
					, new WBDPreliminaryEntryDatePassed()
					, new WBDPreliminaryBWVIdIsNotEmpty()
					, new WBDPreliminaryHasNotWBDType(self::WBD_TP_SERVICE)
					, new WBDPreliminaryHasNoOpenWBDError($wbd_errors)
					);
	}

	/**
	* returns the needes checks for an user to be released
	*
	* is an existing user
	* is not root oder anomynos
	* exit date is passed
	* has no wbd exit date
	* has specifed TP-Type
	* has BWV Id
	* has no open specified errors
	*
	* @return boolean
	*/
	public function shouldBeReleasedChecks() {
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryUserExists.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHandleUser.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryExitDatePassed.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNoExitDateWBD.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasWBDType.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryEntryDatePassed.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryBWVIdIsNotEmpty.php");
		require_once("Services/GEV/WBD/classes/Checks/WBDPreliminaryHasNoOpenWBDError.php");

		$wbd_errors = array(self::WBD_ERROR_WRONG_USERDATA
							, self::WBD_ERROR_USER_SERVICETYPE
							, self::WBD_ERROR_USER_DIFFERENT_TP
							, self::WBD_ERROR_USER_UNKNOWN
							, self::WBD_ERROR_USER_DEACTIVATED
							, self::WBD_ERROR_NO_RELEASE
							, self::WBD_ERROR_UNKNOWN);

		return array(new WBDPreliminaryUserExists()
					, new WBDPreliminaryHandleUser(array(6,13))
					, new WBDPreliminaryExitDatePassed()
					, new WBDPreliminaryHasNoExitDateWBD()
					, new WBDPreliminaryHasWBDType(self::WBD_TP_SERVICE)
					, new WBDPreliminaryBWVIdIsNotEmpty()
					, new WBDPreliminaryHasNoOpenWBDError($wbd_errors)
					);
	}

	/**
	 *
	 *
	 */
	public function exitDatePassed() {
		return $this->user_utils->isExitDatePassed();
	}

	/**
	* checks bwvs id empty or not
	*
	* @return boolean
	*/
	public function isWBDBWVIdEmpty() {
		return $this->getWBDBWVId() === null;
	}

	/**
	* checks user is active
	*
	* @return boolean
	*/
	public function isActive() {
		return $this->user_utils->getUser()->getActive();
	}

	/**
	* checks user has one of given wbd tp types
	*
	* @param array
	* @return boolen
	*/
	public function hasOneWBDTypeOf(array $wbd_types) {
		return in_array($this->getWBDTPType(), $wbd_types);
	}

	/**
	* checks user has specified wbd tp type
	* 
	* @param string
	* @return boolean
	*/
	public function hasWBDType($wbd_type) {
		return $this->getWBDTPType() == $wbd_type;
	}

	/**
	* checks if entry date is passed or not
	*
	* @return boolean
	*/
	public function entryDatePassed() {
		
		$now = date("Y-m-d");
		$entry_date = $this->user_utils->getEntryDate();;

		if(!$entry_date) {
			return false;
		}
		return $entry_date->get(IL_CAL_DATE) <= $now;
	}

	/**
	* checks if user is an real ilias user
	*
	* @return boolean
	*/
	public function userExists() {
		return ilObjUser::_lookupLogin($this->user_id) !== false;
	}

	/**
	* is user in specified array
	*
	* @param array 	$special_user_ids
	*
	* @return boolean
	*/
	public function userIdIn(array $special_user_ids) {
		return in_array($this->user_id, $special_user_ids);
	}

	/**
	* checks if there are some open WBD errors according to specified error group
	*
	* @param array
	* @return boolean
	*/
	public function hasOpenWBDErrors(array $wbd_errors) {
		
		$sql = "SELECT DISTINCT count(usr_id) as cnt\n"
				." FROM wbd_errors\n"
				." WHERE resolved=0\n"
				."   AND ".$this->gDB->in("reason", $wbd_errors, false, "text")."\n"
				."   AND usr_id = ".$this->gDB->quote($this->user_id,"integer")."\n";

		$res = $this->gDB->query($sql);
		while($row = $this->gDB->fetchAssoc($res)) {
			return $row["cnt"] > 0;
		}

		return false;
	}

	/**
	* check if there is an WBD Exit date
	*
	* @return boolean
	*/
	public function hasExitDateWBD() {
		return $this->getExitDateWBD() !== null;
	}

	/**
	* checks the next ebd action
	*
	* @param string
	*
	* @return boolean
	*/
	public function nextWBDActionIs($next_wbd_action) {
		return $this->getNextWBDAction() == $next_wbd_action;
	}

	public function getStartOfCurrentCertificationPeriod() {
		return $this->getStartOfCurrentCertificationX(self::WBD_YEARS_FOR_A_PERIOD);
	}
	
	public function getStartOfCurrentCertificationYear() {
		return $this->getStartOfCurrentCertificationX(self::WBD_YEARS_FOR_A_YEAR);
	}
	
	protected function getStartOfCurrentCertificationX($a_year_step) {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		
		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$start = $this->getWBDFirstCertificationPeriodBegin();
		while(   ilDateTime::_before($start, $now)
			  && !ilDateTime::_equals($start, $now)) {
			$start->increment(ilDateTime::YEAR, $a_year_step);
		}
		if (!ilDateTime::_equals($start, $now)) {
			$start->increment(ilDateTime::YEAR, -1 * $a_year_step);
		}
		
		return $start;
	}

	public function setWbdExitUserData($exit_date) {
		require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
		$udf_utils = gevUDFUtils::getInstance();
		$udf_utils->setField($this->user_id,self::USR_WBD_EXIT_DATE, $exit_date);
		$udf_utils->setField($this->user_id,self::USR_TP_TYPE, "1 - Bildungsdienstleister");
	}
}