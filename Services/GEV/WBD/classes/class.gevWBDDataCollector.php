<?php


class gevWBDDataCollector implements WBDDataCollector {
	
	protected $gDB;
	protected $gAppEventHandler;
	protected $records;
	protected $error_statement;

	const WBD_NO_SERVICE 		= "0 - kein Service";
	const WBD_EDU_PROVIDER		= "1 - Bildungsdienstleister";
	const WBD_TP_BASIS			= "2 - TP-Basis";
	const WBD_TP_SERVICE		= "3 - TP-Service";

	public function __construct() {
		global $ilDB, $ilAppEventHandler;
		$this->gDB =  $ilDB;
		$this->gAppEventHandler = $ilAppEventHandler;
		$this->prepareErrorStatement();

		$this->records = array();
	}

	/** 
	* creates the list of users to gather for WBD Service
	* 
	*/
	public function createAffiliateUserList() {

	}

	public function successAffiliateUser($success_data) {

	}

	/** 
	* creates the list of new WP reports
	* 
	*/
	public function createNewEduRecordList() {

	}
	/** 
	* callback public function if report was successfull
	*
	* @param array $success_data 
	*/
	public function successNewEduRecord($success_data) {

	}
	/** 
	* creates the list of storno reports
	* 
	*/
	public function createStornoRecordList() {

	}

	/** 
	* callback public function if report was successfull
	*
	* @param array $success_data 
	*/
	public function successStornoRecord($success_data) {

	}


	/** 
	* creates the list of changed edu record
	* 
	*/
	public function createUpdateEduRecordList() {

	}
	/** 
	* callback public function if report was successfull
	*
	* @param array $success_data 
	*/
	public function successUpdateEduRecord($success_data) {

	}
	/** 
	* creates the list of user records
	* 
	*/
	public function createWPAbfrageRecordList() {

	}
	/** 
	* callback public function if there are any WP reports for the user
	* creates new courses id necessary
	*
	* @param array $success_data 
	*/
	public function successWPAbfrageRecord($success_data) {

	}

	/**********************************
	*
	* CREATE LISTS
	*
	**********************************/
	/** 
	* creates a list of users to register in WBD
	*/
	public function createNewUserList() {
		if(!empty($this->records)) {
			throw new LogicException("gevWBDDataCollector::createNewUserList: Can't build new list. Still records left in the old one.");
		}

		$this->records = $this->_createNewUserList($this->gDB);
		echo(count($this->records));
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestVVErstanlage[]
	 */
	protected function _createNewUserList($db) {
		$returns = array();
		$res = $db->query($this->newUserListQuery());
		while ($rec = $db->fetchAssoc($res)) {
			$rec["address_type"] = "geschäftlich";
			$rec["info_via_mail"] = false;
			$rec["send_data"] = false;
			$rec["data_secure"] = true;
			$rec["country"] = "D";
			$rec["degree"] = "";
			$rec["training_pass"] = true;
			$rec["address_info"] = "";

			$object = gevWBDRequestVvErstanlage::getInstance($rec);
			if(is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$returns[] = $object;
		}
		return $returns;
	}

	/** 
	* creates the list of users to update in WBD
	* 
	*/
	public function createUpdateUserList() {
		if(!empty($this->records)) {
			throw new LogicException("gevWBDDataCollector::createUpdateUserList: Can't build new list. Still records left.");
		}
		
		$this->records = $this->_createUpdateUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestVvAenderung[]
	 */
	protected function _createUpdateUserList($db) {
		$returns = array();
		$res = $this->gDB->query($this->updatedUserListQuery());

		while ($rec = $this->gDB->fetchAssoc($res)) {
			$rec["address_type"] = "geschäftlich";
			$rec["info_via_mail"] = false;
			$rec["country"] = "D";
			$rec["degree"] = "";
			$rec["address_info"] = "";

			$object = gevWBDRequestVvAenderung::getInstance($rec);
			if(is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$returns[] = $object; 
		}
		return $returns;
	}

	/** 
	* creates the list of users to release in WBD
	* 
	*/
	public function createReleaseUserList() {
		if(!empty($this->records)) {
			throw new LogicException("gevWBDDataCollector::createReleaseUserList: Can't build new list. Still records left.");
		}

		$res = $this->gDB->query($this->releaseUserListQuery());

		while ($rec = $this->gDB->fetchAssoc($res)) {
			$object = gevWBDRequestVermitVerwaltungTransferfaehig::getInstance($rec);
			if(is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$this->records[] = $object; 
		}
	}

	/**********************************
	*
	* SQL STATEMENTS
	*
	**********************************/
	/**
	* returns the query to acquire the new users list
	*/
	protected function newUserListQuery($service_types = array(self::WBD_TP_BASIS,self::WBD_TP_SERVICE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_BASIS,self::WBD_TP_SERVICE))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}

		$sql = 	"SELECT row_id, user_id, gender, email, mobile_phone_nr, birthday, lastname, firstname, city, zipcode, phone_nr, wbd_agent_status, okz, wbd_type, street"
				." FROM hist_user "
				."	WHERE hist_historic = 0"
				."		AND deleted = 0"
				."		AND bwv_id = ".$this->gDB->quote('-empty-','text')
				."		AND	last_wbd_report IS NULL"
				."		AND user_id NOT IN ("
		//pending users
				."			SELECT DISTINCT user_id	FROM hist_user"
				."				WHERE hist_historic = 1"
				."					AND NOT last_wbd_report IS NULL"
				."		)";
		$sql .= "		AND ".$this->gDB->in('wbd_type', $service_types, false, 'text')
			 	."		AND user_id IN (SELECT usr_id FROM usr_data)"
				."		AND user_id NOT IN (6, 13)"
				."		AND hist_user.is_active = 1"; 
		//only error-free users
		$sql .= " 		AND user_id NOT IN ("
				." 			SELECT DISTINCT usr_id FROM wbd_errors"
				."				WHERE resolved=0"
				."				AND reason IN ('WRONG_USERDATA','USER_EXISTS_TP', 'USER_EXISTS', 'USER_SERVICETYPE')"
				."		)";
		if($limit) {
			$sql .= "	LIMIT ".$this->gDB->quote($limit,'integer');
		}
		return $sql;
	}

	protected function updatedUserListQuery($service_types = array(self::WBD_TP_SERVICE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_SERVICE))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}

		$sql = 	"SELECT row_id, user_id, gender, email, mobile_phone_nr, birthday, lastname, firstname, city, zipcode, phone_nr, wbd_agent_status, okz, wbd_type, street, bwv_id"
				." FROM hist_user"
				."	WHERE hist_historic = 0"
				."		AND NOT bwv_id = ".$this->gDB->quote('-empty-','text')
				."		AND last_wbd_report IS NULL";
		// manage accounts for TP_Service only:
		$sql .= "		AND wbd_type = ".$this->gDB->quote(self::WBD_TP_SERVICE,"text");
		//dev-safety:
		$sql .= " 		AND user_id in (SELECT usr_id FROM usr_data)"
				." 		AND user_id NOT IN (6, 13)"; //root, anonymous

		//ERROR-LOG:
		$sql .= " 		AND user_id NOT IN ("
			." 				SELECT DISTINCT usr_id FROM wbd_errors"
			."					WHERE resolved=0"
			." 					AND reason IN ('WRONG_USERDATA','USER_EXISTS_TP'," 
			."						'USER_SERVICETYPE', 'USER_DIFFERENT_TP'," 
			."						'USER_DEACTIVATED', 'USER_UNKNOWN', 'CREATE_DUPLICATE')"
			//." AND action='new_user'"
			."			)";
		
		if($limit) {
			$sql .= "	LIMIT ".$this->gDB->quote($limit,'integer');
		}
		
		return $sql;
	}

	protected function releaseUserListQuery($service_types = array(self::WBD_TP_SERVICE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_SERVICE))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}
		
		$sql = "SELECT row_id, user_id, email, mobile_phone_nr, bwv_id FROM hist_user"
					." WHERE hist_historic = ".$this->ilDB->quote(0, "integer")
					." AND NOT	bwv_id = ".$this->ilDB->quote($this->empty_bwv_id_text, "text").""
					." AND NOT exit_date = ".$this->ilDB->quote($this->empty_date_text, "text").""
					." AND exit_date < CURDATE()"
					." AND exit_date_wbd = ".$this->ilDB->quote($this->empty_date_text, "text")."";
		// manage accounts for TP_Service only:
		$sql .= " AND ".$this->ilDB->in("wbd_type", array(self::WBD_TP_SERVICE), false, "text")."";

		//dev-safety:
		$sql .= ' AND user_id in (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous

		//ERROR-LOG:
		$sql .= " AND user_id NOT IN ("
			." SELECT DISTINCT usr_id FROM wbd_errors WHERE"
			." resolved=0"
			." AND ".$this->ilDB->in("reason", 
										array('WRONG_USERDATA', 'USER_SERVICETYPE', 'USER_DIFFERENT_TP', 'USER_UNKNOWN', 'NO_RELEASE'), false, "text"
									).""
			//." AND action='new_user'"
			.")";

		if($limit) {
			$sql .= "	LIMIT ".$this->gDB->quote($limit,'integer');
		}

		return $sql;
	}

	/**********************************
	*
	* SUCCESS CALLBACKS
	*
	**********************************/
	/** 
	* callback public function if registration was successfull
	* 
	* @param array $success_data
	*/
	public function successNewUser(gevWBDSuccessVvErstanlage $success_data) {
		$usr_id = $success_data->internalAgentId();
		$usr_utils = gevUserUtils::getInstance($usr_id);
		$usr_utils->setWBDBWVId($success_data->agentId());
		$usr_utils->setWBDFirstCertificationPeriodBegin($success_data->beginOfCertificationPeriod());

		$this->raiseEventUserChanged($usr_utils->getUser());

		$this->setLastWBDReport('hist_user',$rows);
	}

	/** 
	* callback public function if update was successfull
	*
	* @param array $success_data
	*/
	public function successUpdateUser(gevWBDSuccessVvAenderung $success_data) {
		$row_id = $success_data->rowId();
		$this->setLastWBDReport('hist_user',array($row_id));
	}

	/** 
	* callback public function if release was successfull
	*
	* @param array $success_data 
	*/
	public function successReleaseUser($success_data) {
		$row_id = $success_data->rowId();
		$usr_id = $success_data->internalAgentId();

		$usr_utils = gevUserUtils::getInstance($usr_id);
		$usr_utils->setWbdExitUserData();
		$this->raiseEventUserChanged($usr_utils->getUser());

		$this->setLastWBDReport('hist_user', $row_id);
	}


	/**********************************
	*
	* ERROR CALLBACK
	*
	**********************************/
	/** 
	* callback public function for every error
	* 
	* @param array $error_data
	*/
	public function error(gevWBDError $error) {
		$data = array(
			$error->service()
			,$error->internal()
			,$error->userId() 
			,$error->crsId() 
			,$error->rowId()
			,$error->reason()
			,$error->message()
			);
		return $this->gDB->execute($this->error_statement, $data);
	}

	protected function prepareErrorStatement() {
		$sql = 'INSERT INTO wbd_errors ('
			.'		action, internal, usr_id, crs_id,'
			.'		internal_booking_id, reason, reason_full'
			.'	) VALUES (?,?,?,?,?,?,?)';
		$data_types = array('text','integer','integer','integer','integer','text','text');
		$this->error_statement = $this->gDB->prepareManip($sql, $data_types);
	}

	/**********************************
	*
	* GET NEXT RECORD
	*
	**********************************/
	/** 
	* get the next request object
	* 
	* @return 	WBDRequest
	*/
	public function getNextRecord() {
		return array_shift($this->records);
	}

	/**********************************
	*
	* USEFUL FUNCTIONS
	*
	**********************************/
	/**
	* set specified row of reported today
	*
	* @param string 	$table 		Table where the date should to set
	* @param integer 	$row 		Specifies the row
	*/
	protected function setLastWBDReport($table ,array $rows) {
		$sql = "UPDATE ".$table."\n"
				." SET last_wbd_report = CURDATE()\n"
				." WHERE ".$this->gDB->in("row_id",$rows,false,'text')."\n";
		$this->gDB->maipulate($sql);
	}

	/**
	 * raises the event user has changed
	 *
	 * @param ilObjUser $user
	 */
	public function raiseEventUserChanged(ilObjUser $user) {
		$this->gAppEventHandler->raise("Services/User", "afterUpdate", array("user_obj" => $user));
		$this->setLastWBDReportForAutoHistRows($user->getId());
	}

	/**
	* set last_wbd_report for automaticly created hist rows
	*/
	public function setLastWBDReportForAutoHistRows($a_user_id) {
		$sql = "SELECT row_id FROM hist_user\n"
				." WHERE user_id = ".$this->gDB->quote($usr_id,'integer')."\n"
				." AND hist_historic = 0\n";

		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		$this->setLastWBDReport('hist_user', $record['row_id']);
	}

}