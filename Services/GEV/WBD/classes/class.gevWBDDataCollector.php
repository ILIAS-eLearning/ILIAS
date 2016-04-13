<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of WBD DataCollector
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDDataCollector implements WBDDataCollector {
	
	protected $gDB;
	protected $gAppEventHandler;
	protected $requests;
	protected $error_statement;
	protected $storno_rows;
	protected $abfrage_usr_ids;

	const EMPTY_BWV_ID_TEXT = "-empty-";
	const EMPTY_DATE_TEXT = "0000-00-00";

	const WBD_NO_SERVICE 		= "0 - kein Service";
	const WBD_EDU_PROVIDER		= "1 - Bildungsdienstleister";
	const WBD_TP_BASIS			= "2 - TP-Basis";
	const WBD_TP_SERVICE		= "3 - TP-Service";

	public function __construct($lms_folder) {
		chdir($lms_folder);
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestVermitVerwaltungAufnahme.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestVermitVerwaltungTransferfaehig.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestVvAenderung.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestVvErstanlage.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestWPAbfrage.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestWPMeldung.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestWPStorno.php");
		require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
		require_once("Services/GEV/WBD/classes/class.gevWBD.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");


		require_once "./Services/Context/classes/class.ilContext.php";
		ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
		require_once("./Services/Init/classes/class.ilInitialisation.php");
		ilInitialisation::initILIAS();

		global $ilDB, $ilAppEventHandler, $ilLog;
		$this->gDB =  $ilDB;
		$this->gAppEventHandler = $ilAppEventHandler;
		$this->gLog = $ilLog;

		$this->prepareErrorStatement();

		$this->requests = array();

		$this->stornoCounter = 0;
		$this->storno_rows = null;
		$this->abfrage_usr_ids = null;
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
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createNewUserList: Can't build new list. Still records left in the old one.");
		}

		$this->requests = $this->_createNewUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestVVErstanlage[]
	 */
	protected function _createNewUserList($db) {
		$returns = array();
		$res = $db->query($this->newUserListQuery());
		
		while ($rec = $db->fetchAssoc($res)) {
			$wbd = gevWBD::getInstance($rec['user_id']);

			$checks_to_release = array();
			switch($rec["next_wbd_action"]) {
				case gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE:
					$rec["wbd_type"] = self::WBD_TP_SERVICE;
					$checks_to_release = $wbd->shouldBeRegisteredAsNewTPServiceChecks();
					break;
				case gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS:
					$rec["wbd_type"] = self::WBD_TP_BASIS;
					$checks_to_release = $wbd->shouldBeRegisteredAsNewTPBasis();
					break;
			}

			$failed_checks = $this->performPreliminaryChecks($checks_to_release, $wbd);

			if (count($failed_checks) == 0) {
				$rec["address_type"] = "geschäftlich";
				$rec["info_via_mail"] = false;
				$rec["send_data"] = true;
				$rec["data_secure"] = true;
				$rec["country"] = "D";
				$rec["degree"] = "";
				$rec["address_info"] = "";

				$object = gevWBDRequestVvErstanlage::getInstance($rec);
				if(is_array($object)) {
					foreach ($object as $error) {
						$this->error($error);
					}
					continue;
				}
				$returns[] = $object;
			} else {
				foreach ($failed_checks as $key => $value) {
					$error = new gevWBDError($value->message(), "pre", "new_user", $rec["user_id"], $rec["row_id"]);
					$this->error($error);
				}
			}
		}

		return $returns;
	}

	/** 
	* creates the list of users to update in WBD
	* 
	*/
	public function createUpdateUserList() {
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createUpdateUserList: Can't build new list. Still records left.");
		}
		
		$this->requests = $this->_createUpdateUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestVvAenderung[]
	 */
	protected function _createUpdateUserList($db) {
		$returns = array();
		$res = $db->query($this->updatedUserListQuery());

		while ($rec = $db->fetchAssoc($res)) {
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
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createReleaseUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createReleaseUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestVermitVerwaltungTransferfaehig[]
	 */
	protected function _createReleaseUserList($db) {
		$returns = array();
		$res = $db->query($this->releaseUserListQuery());

		while ($rec = $db->fetchAssoc($res)) {
			$wbd = gevWBD::getInstanceByObjOrId($rec['user_id']);
			
			$checks_to_release = $wbd->shouldBeReleasedChecks();
			$failed_checks = $this->performPreliminaryChecks($checks_to_release, $wbd);

			if(count($failed_checks) == 0) {
				$object = gevWBDRequestVermitVerwaltungTransferfaehig::getInstance($rec);
				if(is_array($object)) {
					foreach ($object as $error) {
						$this->error($error);
					}
					continue;
				}
				$returns[] = $object; 
			} else {
				foreach ($failed_checks as $key => $value) {
					$error = new gevWBDError($value->message(), "pre", "release_user", $rec["user_id"], $rec["row_id"]);
					$this->error($error);
				}
			}
		}

		return $returns;
	}

	/** 
	* creates the list of users to gather for WBD Service
	* 
	*/
	public function createAffiliateUserList() {
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createAffiliateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createAffiliateUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestVermitVerwaltungAufnahme[]
	 */
	protected function _createAffiliateUserList($db) {
		$returns = array();
		$res = $db->query($this->affiliateUserListQuery());

		while ($rec = $db->fetchAssoc($res)) {
			$wbd = gevWBD::getInstanceByObjOrId($rec['user_id']);
			$checks_to_release = $wbd->shouldBeAffiliateAsTPServiceChecks();
			$failed_checks = $this->performPreliminaryChecks($checks_to_release, $wbd);

			if (count($failed_checks) == 0) {
				$object = gevWBDRequestVermitVerwaltungAufnahme::getInstance($rec);
				if(is_array($object)) {
					foreach ($object as $error) {
						$this->error($error);
					}
					continue;
				}
				$returns[] = $object;
			} else {
				foreach ($failed_checks as $key => $value) {
					$error = new gevWBDError($value->message(), "pre", "affiliate_user", $rec["user_id"], $rec["row_id"]);
					$this->error($error);
				}
			}
		}

		return $returns;
	}

	/** 
	* creates the list of new WP reports
	* 
	*/
	public function createNewEduRecordList() {
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createAffiliateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createNewEduRecordList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestWPMeldung[]
	 */
	protected function _createNewEduRecordList($db) {
		$returns = array();
		$res = $db->query($this->newEduRecordListQuery());

		while ($rec = $db->fetchAssoc($res)) {
			$object = gevWBDRequestWPMeldung::getInstance($rec);
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
	* creates the list of storno reports
	* 
	*/
	public function createStornoRecordList() {
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createAffiliateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createStornoRecordList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestWPStorno[]
	 */
	protected function _createStornoRecordList($db) {
		$returns = array();
		$res = $db->query($this->storneEduRecordListQuery(array(self::WBD_TP_SERVICE,self::WBD_TP_BASIS,self::WBD_EDU_PROVIDER), null));

		while ($rec = $db->fetchAssoc($res)) {
			$object = gevWBDRequestWPStorno::getInstance($rec);
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
	* creates the list of changed edu record
	* 
	*/
	public function createUpdateEduRecordList() {
		//TODO
	}

	/** 
	* creates the list of user records
	* 
	*/
	public function createWPAbfrageRecordList() {
		if(!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createWPAbfrageRecordList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createWPAbfrageRecordList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestWPAbfrage[]
	 */
	protected function _createWPAbfrageRecordList($db) {
		$returns = array();
		$res = $db->query($this->WPAbfrageRecordList());

		while ($rec = $db->fetchAssoc($res)) {
			$rec["certification_period"] = "Selektiert nicht stornierte Weiterbildungsmaßnahmen aus der aktuelle Zertifizierungsperiode.";

			$object = gevWBDRequestWPAbfrage::getInstance($rec);
			if(is_array($object)) {
				foreach ($object as $error) {
					$this->gLog->write($error);
				}
				continue;
			}
			$returns[] = $object;
		}

		return $returns;
	}

	/**********************************
	*
	* SQL STATEMENTS
	*
	**********************************/
	/**
	* returns the query to acquire the new users list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	*
	* @return string 	$sql
	*/
	protected function newUserListQuery($next_action = array(gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE
															,gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS), $limit = null) 
	{
		//check for valid service types
		if(count(array_intersect($next_action, array(gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE
													  ,gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS))) != count($next_action)) 
		{
			throw new LogicException("One or more invalid next_actions");
		}

		$sql = "SELECT hu.row_id, hu.user_id, hu.gender, hu.email, hu.wbd_email, hu.mobile_phone_nr, hu.birthday, hu.lastname, hu.firstname\n"
				.", hu.city, hu.next_wbd_action, hu.zipcode, hu.phone_nr, hu.wbd_agent_status, hu.okz, hu.wbd_type, hu.street\n"
				." FROM hist_user hu\n"
				." LEFT JOIN wbd_errors wbde ON wbde.usr_id = hu.user_id\n"
				."    AND wbde.resolved = 0\n"
				."    AND wbde.crs_id = 0\n"
				." WHERE hu.hist_historic = ".$this->gDB->quote(0, "integer")."\n"
				."    AND hu.deleted = ".$this->gDB->quote(0, "integer")."\n"
				."    AND hu.last_wbd_report IS NULL"
				."    AND ".$this->gDB->in("hu.next_wbd_action",$next_action,false, "text")."\n"
				."    AND wbde.reason IS NULL";

		if($limit) {
			$sql .= " LIMIT ".$this->gDB->quote($limit,'integer');
		}

		return $sql;
	}

	/**
	* returns the query to acquire the update users list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	*
	* @return string 	$sql
	*/
	protected function updatedUserListQuery($service_types = array(self::WBD_TP_SERVICE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_SERVICE))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}

		$sql = 	"SELECT hu.row_id, hu.user_id, hu.gender, hu.email, hu.wbd_email, hu.mobile_phone_nr, hu.birthday\n"
				.", hu.lastname, hu.firstname, hu.city, hu.zipcode, hu.phone_nr, hu.wbd_agent_status, hu.okz, hu.wbd_type, hu.street, hu.bwv_id\n"
				." FROM hist_user hu\n"
				." LEFT JOIN wbd_errors wbde ON wbde.usr_id = hu.user_id\n"
				."    AND wbde.resolved = 0\n"
				."    AND wbde.crs_id = 0\n"
				." WHERE hu.hist_historic = 0\n"
				."    AND NOT hu.bwv_id = ".$this->gDB->quote('-empty-','text')."\n"
				."    AND hu.last_wbd_report IS NULL\n"
				."    AND hu.wbd_type = ".$this->gDB->quote(self::WBD_TP_SERVICE,"text")."\n"
				."    AND hu.user_id in (SELECT usr_id FROM usr_data)\n"
				."    AND hu.user_id NOT IN (6, 13)\n"
				."    AND wbde.reason IS NULL\n"
				." ORDER BY hu.row_id\n";
		if($limit) {
			$sql .= " LIMIT ".$this->gDB->quote($limit,'integer');
		}

		return $sql;
	}

	/**
	* returns the query to acquire the release users list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	*
	* @return string 	$sql
	*/
	protected function releaseUserListQuery($next_action = array(gevWBD::USR_WBD_NEXT_ACTION_RELEASE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($next_action, array(gevWBD::USR_WBD_NEXT_ACTION_RELEASE))) != count($next_action)) {
			throw new LogicException("One or more invalid next_action");
		}

		$sql = "SELECT hu.row_id, hu.user_id, hu.email, hu.mobile_phone_nr, hu.bwv_id\n"
				." FROM hist_user hu\n"
				." LEFT JOIN wbd_errors wbde ON wbde.usr_id = hu.user_id\n"
				."    AND wbde.resolved = 0\n"
				."    AND wbde.crs_id = 0\n"
				." WHERE hu.hist_historic = ".$this->gDB->quote(0, "integer")."\n"
				."    AND hu.deleted = ".$this->gDB->quote(0, "integer")."\n"
				."    AND ".$this->gDB->in("hu.next_wbd_action",$next_action,false, "text")."\n"
				."    AND wbde.reason IS NULL\n";

		if($limit) {
			$sql .= " LIMIT ".$this->gDB->quote($limit,'integer');
		}

		return $sql;
	}

	/**
	* returns the query to acquire the affiliate users list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	*
	* @return string 	$sql
	*/
	protected function affiliateUserListQuery($next_action = array(gevWBD::USR_WBD_NEXT_ACTION_AFFILIATE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($next_action, array(gevWBD::USR_WBD_NEXT_ACTION_AFFILIATE))) != count($next_action)) {
			throw new LogicException("One or more invalid next_action");
		}

		$sql = "SELECT hu.row_id, hu.user_id, hu.email, hu.mobile_phone_nr, hu.birthday, hu.bwv_id, hu.lastname, hu.firstname\n"
				." FROM hist_user hu\n"
				." LEFT JOIN wbd_errors wbde ON wbde.usr_id = hu.user_id\n"
				."    AND wbde.resolved = 0\n"
				."    AND wbde.crs_id = 0\n"
				." WHERE hu.hist_historic = ".$this->gDB->quote(0, "integer")."\n"
				."    AND hu.deleted = ".$this->gDB->quote(0, "integer")."\n"
				."    AND hu.last_wbd_report IS NULL\n"
				."    AND ".$this->gDB->in("hu.next_wbd_action",$next_action,false, "text")."\n"
				."    AND wbde.reason IS NULL\n";

		return $sql;
	}

	/**
	* returns the query to acquire the new edu records list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	*
	* @return string 	$sql
	*/
	protected function newEduRecordListQuery($service_types = array(self::WBD_TP_SERVICE,self::WBD_TP_BASIS,self::WBD_EDU_PROVIDER), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_SERVICE,self::WBD_TP_BASIS,self::WBD_EDU_PROVIDER))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}
		
		$sql = "SELECT hist_usercoursestatus.row_id, hist_user.user_id\n"
					.", hist_usercoursestatus.begin_date, hist_usercoursestatus.end_date\n"
					.", hist_usercoursestatus.credit_points, hist_course.type, hist_course.wbd_topic\n"
					.", hist_course.crs_id"
					.", hist_course.title, hist_user.bwv_id\n"
					.", hist_user.begin_of_certification\n"
					.", count(wbd_errors.usr_id) AS errors\n"
				." FROM hist_usercoursestatus\n"
				." JOIN hist_course\n"
					." ON hist_usercoursestatus.crs_id = hist_course.crs_id\n"
				." JOIN hist_user\n"
					." ON hist_usercoursestatus.usr_id = hist_user.user_id\n"
				." LEFT JOIN wbd_errors\n"
					." ON wbd_errors.usr_id = hist_user.user_id\n"
						." AND wbd_errors.crs_id = hist_usercoursestatus.crs_id\n"
						." AND wbd_errors.resolved = 0\n"
				." WHERE hist_usercoursestatus.hist_historic = 0\n"
					." AND hist_course.hist_historic = 0\n"
					." AND hist_user.hist_historic = 0\n"
					." AND hist_user.bwv_id != '-empty-'\n"
					." AND hist_usercoursestatus.function IN ('Mitglied', 'Teilnehmer')\n"
					." AND hist_usercoursestatus.okz IN ('OKZ1', 'OKZ2','OKZ3')\n"
					." AND hist_usercoursestatus.participation_status = 'teilgenommen'\n"
					." AND hist_usercoursestatus.last_wbd_report IS NULL\n"
					." AND hist_usercoursestatus.wbd_booking_id IS NULL\n"
					." AND hist_usercoursestatus.credit_points > 0\n"
					." AND (hist_usercoursestatus.end_date > '2013-12-31'\n"
						." OR (hist_course.type = 'Selbstlernkurs' \n"
							."AND hist_usercoursestatus.begin_date > '2013-12-31'\n"
							.")\n"
						.")\n"
					." AND ".$this->gDB->in("hist_user.wbd_type", $service_types, false, "text")."\n"
					." AND hist_user.user_id in (SELECT usr_id FROM usr_data)\n"
					." AND hist_user.user_id NOT IN (6, 13)\n"
				." GROUP BY hist_usercoursestatus.row_id, hist_usercoursestatus.usr_id\n"
					.", hist_usercoursestatus.begin_date, hist_usercoursestatus.end_date\n"
					.", hist_usercoursestatus.credit_points, hist_course.type, hist_course.wbd_topic\n"
					.", hist_course.title, hist_user.bwv_id\n"
				." HAVING errors = 0\n"
				." ORDER BY hist_usercoursestatus.row_id\n";
		
		return $sql;
	}

	/**
	* returns the query to acquire the storno record list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	*
	* @return string 	$sql
	*/
	protected function storneEduRecordListQuery($service_types = array(self::WBD_TP_SERVICE,self::WBD_TP_BASIS,self::WBD_EDU_PROVIDER), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_SERVICE,self::WBD_TP_BASIS,self::WBD_EDU_PROVIDER))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}

		if($this->storno_rows === null) {
			throw new LogicException("No rows for Storno");
		}

		$sql = " SELECT hist_usercoursestatus.row_id, hist_usercoursestatus.wbd_booking_id, hist_user.user_id, hist_user.bwv_id\n"
				." FROM	hist_usercoursestatus\n"
				." INNER JOIN hist_course\n"
					." ON hist_usercoursestatus.crs_id = hist_course.crs_id\n"
					." AND hist_course.hist_historic = 0\n"
				." INNER JOIN hist_user\n"
					." ON hist_usercoursestatus.usr_id = hist_user.user_id\n"
					." AND hist_user.hist_historic = 0\n"
				." WHERE hist_user.bwv_id != '-empty-'\n"
				." AND ".$this->gDB->in("wbd_type", $service_types, false, "text")."\n"
				." AND usr_id in (SELECT usr_id FROM usr_data)\n"
				." AND user_id NOT IN (6, 13)\n"
				." AND ".$this->gDB->in("hist_usercoursestatus.row_id", $this->storno_rows, false, "integer")."\n"
				." ORDER BY hist_usercoursestatus.row_id\n";
				//." AND hist_usercoursestatus.row_id IN ()\n";

		if($limit) {
			$sql .= " LIMIT ".$this->gDB->quote($limit,'integer');
		}
		return $sql;
	}

	/**
	* returns the query to acquire the wp abfrage list
	* @param array 		$service_types 		which TP Types should be used
	* @param intgeger 	$limit
	* @param array 		$usr_ids 				user_id to search for
	*
	* @return string 	$sql
	*/
	protected function WPAbfrageRecordList($service_types = array(self::WBD_TP_SERVICE), $limit = null) {
		//check for valid service types
		if(count(array_intersect($service_types, array(self::WBD_TP_SERVICE))) != count($service_types)) {
			throw new LogicException("One or more invalid service_types");
		}

		$sql = "SELECT bwv_id, user_id, row_id\n"
				." FROM hist_user\n"
				." WHERE bwv_id != '-empty-'\n"
				." AND hist_historic=0"
				." AND ".$this->gDB->in("wbd_type", $service_types, false, "text")."\n";

		if($this->abfrage_usr_ids !== null) {
			$sql .= " AND ".$this->gDB->in("user_id", $this->abfrage_usr_ids, false, "text")."\n";
		}

		$sql .= " ORDER BY user_id";

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
		$wbd = gevWBD::getInstance($usr_id);

		$wbd->setWBDBWVId($success_data->agentId());
		$wbd->setWBDFirstCertificationPeriodBegin($success_data->beginOfCertificationPeriod());
		$wbd->setWBDTPType($success_data->wbdType());

		$this->setNextWBDActionToNothing($usr_id);
		$this->raiseEventUserChanged($usr_utils->getUser());
		$this->setLastWBDReportForAutoHistRows($usr_id);
		

		$this->setLastWBDReport('hist_user',array($success_data->rowId()));
	}

	/** 
	* callback public function if update was successfull
	*
	* @param gevWBDSuccessVvAenderung $success_data
	*/
	public function successUpdateUser(gevWBDSuccessVvAenderung $success_data) {
		$row_id = $success_data->rowId();
		$this->setLastWBDReport('hist_user',array($success_data->rowId()));
	}

	/** 
	* callback public function if release was successfull
	*
	* @param gevWBDSuccessVermitVerwaltungTransferfaehig $success_data 
	*/
	public function successReleaseUser(gevWBDSuccessVermitVerwaltungTransferfaehig $success_data) {
		$row_id = $success_data->rowId();
		$usr_id = $success_data->usrId();

		$usr_utils = gevUserUtils::getInstance($usr_id);
		$wbd = gevWBD::getInstance($usr_id);
		$wbd->setWbdExitUserData($this->getCurrentDate());

		$this->setNextWBDActionToNothing($usr_id);
		$this->raiseEventUserChanged($usr_utils->getUser());
		$this->setLastWBDReportForAutoHistRows($usr_id);

		$this->setLastWBDReport('hist_user', array($row_id));
	}

	/** 
	* callback public function if affiliate was successfull
	*
	* @param array $success_data 
	*/
	public function successAffiliateUser(gevWBDSuccessVermitVerwaltungAufnahme $success_data) {
		$usr_id = $success_data->usrId();
		$row_id = $success_data->rowId();

		$usr_utils = gevUserUtils::getInstance($usr_id);
		$wbd = gevWBD::getInstance($usr_id);
		$wbd->setWBDTPType(gevWBD::WBD_TP_SERVICE);
		
		$this->setNextWBDActionToNothing($usr_id);
		$this->raiseEventUserChanged($usr_utils->getUser());
		$this->setLastWBDReportForAutoHistRows($usr_id);

		$this->setLastWBDReport('hist_user', array($row_id));
	}

	/**
	* callback public function if report was successfull
	*
	* @param gevWBDSuccessWPMeldung $success_data 
	*/
	public function successNewEduRecord(gevWBDSuccessWPMeldung $success_data) {
		$this->setLastWBDReport('hist_usercoursestatus', array($success_data->rowId()));
		$this->setBookingId($success_data->rowId(), $success_data->wbdBookingId());
		
		if($success_data->doUpdateBeginOfCertification()) {
			$usr_id = $success_data->usrId();
			$usr_utils = gevUserUtils::getInstance($usr_id);
			$wbd = gevWBD::getInstance($usr_id);
			$wbd->setWBDFirstCertificationPeriodBegin($success_data->beginOfCertificationPeriod());
			$this->raiseEventUserChanged($usr_utils->getUser());
			$this->setLastWBDReportForAutoHistRows($usr_id);
		}
	}

	/** 
	* callback public function if report was successfull
	*
	* @param gevWBDSuccessWPStorno $success_data 
	*/
	public function successStornoRecord(gevWBDSuccessWPStorno $success_data) {
		//NOTHING HAPPENS!
	}

	/** 
	* callback public function if report was successfull
	*
	* @param array $success_data 
	*/
	public function successUpdateEduRecord($success_data) {
		//TODO
	}

	/** 
	* callback public function if there are any WP reports for the user
	* creates new courses id necessary
	*
	* @param gevWBDSuccessWPAbfrage $success_data 
	*/
	public function successWPAbfrageRecord(gevWBDSuccessWPAbfrage $success_data) {

		$import_course_data = $success_data->importCourseData();
		foreach ($import_course_data as $key => $value) {
			if(!$this->bookingIdExists($value->wbdBookingId())){
				$crs_id = $this->importSeminar($value);

				if($crs_id === null) {
					continue;
				}

				$this->assignUserToSeminar($value, $crs_id,$success_data->userId());
			}
		}
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
	public function getNextRequest() {
		return array_shift($this->requests);
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
				." SET last_wbd_report = ".$this->gDB->quote($this->getCurrentDate(),"text")."\n"
				." WHERE ".$this->gDB->in("row_id",$rows,false,'text')."\n";
		$this->gDB->manipulate($sql);
	}

	/**
	 * raises the event user has changed
	 *
	 * @param ilObjUser $user
	 */
	public function raiseEventUserChanged(ilObjUser $user) {
		$this->gAppEventHandler->raise("Services/User", "afterUpdate", array("user_obj" => $user));
	}

	/**
	* set last_wbd_report for automaticly created hist rows
	*
	* @param integer 	$a_user_id
	*/
	public function setLastWBDReportForAutoHistRows($a_user_id) {
		$sql = "SELECT row_id FROM hist_user\n"
				." WHERE user_id = ".$this->gDB->quote($a_user_id,'integer')."\n"
				." AND hist_historic = 0\n";
		$result = $this->gDB->query($sql);
		$record = $this->gDB->fetchAssoc($result);
		$this->setLastWBDReport('hist_user', array($record['row_id']));
	}

	/**
	* set WBD Booking id on usercoursestatus row
	*
	* @param int 		$row_id
	* @param string 	$booking_id
	*/
	public function setBookingId($row_id, $booking_id){
		$sql = "UPDATE hist_usercoursestatus\n"
				." SET wbd_booking_id = ".$this->gDB->quote($booking_id,"text")."\n"
				." WHERE row_id = ".$this->gDB->quote($row_id,"integer")."\n";
		$result = $this->gDB->query($sql);
	}

	/**
	 * check the booking id exists
	 *
	 * @param string $booking_id
	 *
	 * @return boolean
	 */
	protected function bookingIdExists($booking_id) {
		
		$sql = "SELECT wbd_booking_id\n" 
				." FROM hist_usercoursestatus\n"
				." WHERE wbd_booking_id = ".$this->gDB->quote($booking_id,"text")."\n";
			
		$temp_result = $this->gDB->query($sql);
			
		if($this->gDB->numRows($temp_result) == 0) {
			return false;
		}
		return true;
	}

	/**
	 * new entry for foreign wbd-course
	 * or matching for existing seminar
	 *
	 * @param gevImportCourseData $values
	 *
	 * @return integer 	$crs_id 	ID of the course
	**/
	protected function importSeminar(gevImportCourseData $values){

		$title 		= $values->title();
		$type 		= $values->courseType(); 
		$wbd_topic 	= $values->studyContent(); 
		$begin_date	= $values->beginDate()->get(IL_CAL_DATE); // date('Y-m-d', strtotime($rec['Beginn']));
		$end_date 	= $values->endDate()->get(IL_CAL_DATE); //date('Y-m-d', strtotime($rec['Ende']));
		$creator_id = -666;


		$sql = "SELECT crs_id\n"
				." FROM hist_course\n"
				." WHERE title = ".$this->gDB->quote($title,"text")."\n"
				." AND begin_date = ".$this->gDB->quote($begin_date,"text")."\n"
				." AND end_date = ".$this->gDB->quote($end_date,"text")."\n";

		$result = $this->gDB->query($sql);
		if($this->gDB->numRows($result) > 0){
			$record = $this->gDB->fetchAssoc($result);
			return $record['crs_id'];
		}
		
		//new seminar
		$sql = "SELECT MIN(crs_id) - 1 AS new_crs_id\n"
				." FROM hist_course\n"
				." WHERE crs_id < 0\n";

		$result = $this->gDB->query($sql);
		$record = $this->gDB->fetchAssoc($result);
		
		$crs_id = $record['new_crs_id'];
		//start with 4 digits
		if($crs_id == -1){
			$crs_id = -1000;
		}

		$next_id = $this->gDB->nextId('hist_course');

		$sql = "INSERT INTO hist_course\n"
			." (\n"
				." row_id,\n"
				." hist_version,\n"
				." created_ts,\n"
				." creator_user_id,\n"
		 		." is_template,\n"
		 		." crs_id,\n"
		 		." title,\n"
		 		." type, \n"
		 		." wbd_topic,\n"
		 		." begin_date,\n"
		 		." end_date,\n"
		 		." custom_id,\n"
		 		." template_title,\n"
		 		." max_credit_points\n"
			." )\n"
			." VALUES\n"
			." (\n"
				."".$this->gDB->quote($next_id,"integer").",\n"
				." 0,\n"
				." UNIX_TIMESTAMP(),\n"
				."".$this->gDB->quote($creator_id,"integer").",\n"
				." 'Nein',\n"
				."".$this->gDB->quote($crs_id,"integer").",\n"
				."".$this->gDB->quote($title,"text").",\n"
				."".$this->gDB->quote($type,"text").",\n"
		 		."".$this->gDB->quote($wbd_topic,"text").",\n"
		 		."".$this->gDB->quote($begin_date,"date").",\n"
		 		."".$this->gDB->quote($end_date,"date").",\n"
		 		." '-empty-',\n"
		 		." '-empty-',\n"
		 		." '-empty-'\n"
			.")";

			if(! $this->gDB->query($sql)){
				echo "Course could not be created....($sql)\n";
				return null;
			}

		return $crs_id;
	}

	/**
	 * new entry for foreign wbd-courses in hist_usercoursestatus
	 * @param gevImportCourseData 	$values
	 * @param integer 				$crs_id 	id of course
	 * @param integer 				$user_id 	id of user
	**/
	protected function assignUserToSeminar(gevImportCourseData $values, $crs_id, $user_id) {
		$usr_id = $user_id;
		$wbd = gevWBD::getInstanceByObjOrId($usr_id);

		$okz 			= $wbd->getWBDOKZ();
		$booking_id		= $values->wbdBookingId();
		$credit_points 	= $values->creditPoints();
		$begin_date		= $values->beginDate()->get(IL_CAL_DATE); // date('Y-m-d', strtotime($rec['Beginn']));
		$end_date 		= $values->endDate()->get(IL_CAL_DATE); //date('Y-m-d', strtotime($rec['Ende']));
		$creator_id 	= -666;
		$next_id 		= $this->gDB->nextId('hist_usercoursestatus');

		$sql = "INSERT INTO hist_usercoursestatus\n"
			." (\n"
				."row_id,\n"
				."wbd_booking_id,\n"
				."created_ts,\n"
				."creator_user_id,\n"
				."usr_id,\n"
		 		."crs_id,\n"
		 		."credit_points,\n"
		 		."hist_historic,\n"
		 		."hist_version,\n"
		 		."okz,\n"
		 		."function,\n"
		 		."booking_status,\n"
		 		."participation_status,\n"
		 		."begin_date,\n"
		 		."end_date,\n"
		 		."bill_id,\n"
		 		."certificate\n"
			.") \n"
			."VALUES \n"
			."(\n"
				."".$this->gDB->quote($next_id,"integer").",\n"
				."".$this->gDB->quote($booking_id,"text").",\n"
				."UNIX_TIMESTAMP(),\n"
				."".$this->gDB->quote($creator_id,"integer").",\n"
				."".$this->gDB->quote($usr_id,"integer").",\n"
				."".$this->gDB->quote($crs_id,"integer").",\n"
				."".$this->gDB->quote($credit_points,"integer").",\n"
				."0,\n"
				."0,\n"
				."".$this->gDB->quote($okz,"text").",\n"
				."'Mitglied',\n"
				."'gebucht',\n"
				."'teilgenommen',\n"
				."".$this->gDB->quote($begin_date,"date").",\n"
				."".$this->gDB->quote($end_date,"date").",\n"
				."-1,\n"
				."-1\n"
			.")\n";

		if(! $this->gDB->query($sql)){
			echo "User could not assigned...($sql)\n";
		}
	}

	public function requestsCount() {
		return count($this->requests);
	}

	protected function getCurrentDate() {
		return date("Y-m-d");
	}

	/**
	* set next wbd action to nothing
	*
	* @param string $user_id 
	*/
	public function setNextWBDActionToNothing($user_id) {
		$wbd = gevWBD::getInstance($user_id);
		$wbd->setNextWBDAction(gevWBD::USR_WBD_NEXT_ACTION_NOTHING);
	}

	/**
	* sets the rows for storno
	*
	* @param array 		$storno_rows
	*/
	public function setStornoRows($storno_rows) {
		$this->storno_rows = $storno_rows;
	}

	/**
	* sets the users id's to recieve wp
	*
	* @param array 		$abfrage_usr_ids
	*/
	public function setAbfrageUsrIds($abfrage_usr_ids) {
		$this->abfrage_usr_ids = $abfrage_usr_ids;
	}

	protected function performPreliminaryChecks(array $checks_to_release, gevWBD $wbd) {
		return array_filter($checks_to_release,
					function($v) use ($wbd) {
						if(!$v->performCheck($wbd)) {
							return $v;
						}
					}
					);
	}
}	