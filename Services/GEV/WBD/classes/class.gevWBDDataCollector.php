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
	}
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

	/** 
	* creates a list of users to register in WBD
	*/
	public function createNewUserList() {
		if($this->records) {
			throw new LogicException("gevWBDDataCollector::createNewUserList: Can't build new list. Still records left in the old one.");
		}
		$this->records  = array();
		$res = $this->gDB->query($this->newUserListQuery());

		while ($rec = $this->gDB->fetchAssoc($res)) {
			$object = gevWBDRequestVvErstanlage::getInstance($rec);
			if(is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$this->records[] = $object; 
		}
	}
	/**
	* returns the query to acquire the new users list
	*/
	protected function newUserListQuery($service_types = array(self::WBD_TP_BASIS,self::WBD_TP_SERVICE), $limit = null) {
		$sql = 	"SELECT * FROM hist_user "
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
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_BASIS,self::WBD_TP_SERVICE))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}

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
		$this->gAppEventHandler->raise("Services/User", "afterUpdate", array("user_obj" => $usr_utils->getUser()));
		$rows = array($success_data->rowId(),$this->lastHistUserRow());
		$this->setLastWBDReport('hist_user',$rows);
	}

	/** 
	* creates the list of users to update in WBD
	* 
	*/
	public function createUpdateUserList() {
		if($this->records) {
			throw new LogicException("gevWBDDataCollector::createUpdateUserList: Can't build new list. Still records left.");
		}
		$this->records  = array();
		$res = $this->gDB->query($this->updatedUserListQuery());
		while ($rec = $this->gDB->fetchAssoc($res)) {
			$object = gevWBDRequestVvAenderung::getInstance($rec);
			if(is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$this->data_records[] = $object; 
		}
	}

	protected function updatedUserListQuery() {
		$sql = 	"SELECT * FROM hist_user"
				."	WHERE hist_historic = 0"
				."		AND NOT bwv_id = '-empty-'"
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
		return $sql;
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
	* creates the list of users to release in WBD
	* 
	*/
	public function createReleaseUserList() {

	}
	/** 
	* callback public function if release was successfull
	*
	* @param array $success_data 
	*/
	public function successReleaseUser($success_data) {

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

	/** 
	* get the next request object
	* 
	* @return 	WBDRequest
	*/
	public function getNextRecord() {
		if(count($this->records) > 0) {
			return array_shift($this->records);
		}
		$this->records = null;
		return null;
	}

	protected function setLastWBDReport($table ,array $rows) {
		$sql = 'UPDATE '.$table.' SET last_wbd_report = CURDATE()'
				.'	WHERE '.$this->gDB->in('row_id',$rows,false,'text');
		$this->gDB->maipulate($sql);
	}

	protected function lastHistUserRow($usr_id) {
		$sql = 'SELECT row_id FROM hist_user'
				.' WHERE hist_historic = 0 AND user_id ='.$this->gDB->quote($usr_id,'integer');
		$res = $this->gDB->query($sql);
		return $this->gDB->fetchAssoc($res)['row_id'];
	}

	public function getRecords() {
		return $this->records;
	}

}