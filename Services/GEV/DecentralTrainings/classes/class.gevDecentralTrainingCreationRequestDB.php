<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */#

/**
* Database for decentral training creation request.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevDecentralTrainingCreationRequestDB {
	const TABLE_NAME = "dct_creation_requests";
	const ARRAY_DELIM = ";";
	
	public function __construct() {
		
	}
	
	public function createRequest(gevDecentralTrainingCreationRequest $a_request) {
		$ilDB = $this->getDB();
		$request_id = $ilDB->nextId(self::TABLE_NAME);
		$settings = $a_request->settings();
		$requested_ts = $a_request->requestedTS();
		$finished_ts = $a_request->finishedTS();
		$ilDB->manipulate(
			"INSERT INTO ".self::TABLE_NAME."\n".
			"       (request_id, user_id, template_obj_id, requested_ts,\n".
			"       finished_ts, created_obj_id, trainer_ids, start_dt,\n".
			"       end_dt, venue_obj_id, venue_text, orgu_ref_id, description,\n".
			"       orga_info, webinar_link, webinar_password, session_id,title,vc_type,\n".
			"       training_category,target_group,gdv_topic,tmp_path_string,added_files)\n".
			" VALUES ( ".$ilDB->quote($request_id, "integer")."\n".
			"        , ".$ilDB->quote($a_request->userId(), "integer")."\n".
			"        , ".$ilDB->quote($a_request->templateObjId(), "integer")."\n".
			"        , ".$ilDB->quote($requested_ts ? $requested_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"        , ".$ilDB->quote($finished_ts ? $finished_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"        , ".$ilDB->quote($a_request->createdObjId(), "integer")."\n".
			"        , ".$ilDB->quote(implode(self::ARRAY_DELIM, $a_request->trainerIds()), "text")."\n".
			"        , ".$ilDB->quote($settings->start()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"        , ".$ilDB->quote($settings->end()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"        , ".$ilDB->quote($settings->venueObjId(), "integer")."\n".
			"        , ".$ilDB->quote($settings->venueText(), "text")."\n".
			"        , ".$ilDB->quote($settings->orguRefId(), "integer")."\n".
			"        , ".$ilDB->quote($settings->description(), "text")."\n".
			"        , ".$ilDB->quote($settings->orgaInfo(), "text")."\n".
			"        , ".$ilDB->quote($settings->webinarLink(), "text")."\n".
			"        , ".$ilDB->quote($settings->webinarPassword(), "text")."\n".
			"        , ".$ilDB->quote($a_request->sessionId(), "text")."\n".
			"        , ".$ilDB->quote($settings->title(), "text")."\n".
			"        , ".$ilDB->quote($settings->vcType(), "text")."\n".
			"        , ".$ilDB->quote(serialize($settings->trainingCategory()), "text")."\n".
			"        , ".$ilDB->quote(serialize($settings->targetGroup()), "text")."\n".
			"        , ".$ilDB->quote($settings->gdvTopic(), "text")."\n".
			"        , ".$ilDB->quote($settings->tmpPathString(), "text")."\n".
			"        , ".$ilDB->quote(serialize($settings->addedFiles()), "text")."\n".
			"        )\n"
		);
		return $request_id;
	}
	
	public function updateRequest(gevDecentralTrainingCreationRequest $a_request) {
		$ilDB = $this->getDB();
		if ($a_request->requestId() === null) {
			$this->throwException("Can't update request without id.");
		}
		$settings = $a_request->settings();
		$requested_ts = $a_request->requestedTS();
		$finished_ts = $a_request->finishedTS();
		$ilDB->manipulate(
			"UPDATE ".self::TABLE_NAME."\n".
			" SET   user_id = ".$ilDB->quote($a_request->userId(), "integer")."\n".
			"     , template_obj_id = ".$ilDB->quote($a_request->templateObjId(), "integer")."\n".
			"     , requested_ts = ".$ilDB->quote($requested_ts ? $requested_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"     , finished_ts = ".$ilDB->quote($finished_ts ? $finished_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"     , created_obj_id = ".$ilDB->quote($a_request->createdObjId(), "integer")."\n".
			"     , trainer_ids = ".$ilDB->quote(implode(self::ARRAY_DELIM, $a_request->trainerIds()), "text")."\n".
			"     , start_dt = ".$ilDB->quote($settings->start()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"     , end_dt = ".$ilDB->quote($settings->end()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"     , venue_obj_id = ".$ilDB->quote($settings->venueObjId(), "integer")."\n".
			"     , venue_text = ".$ilDB->quote($settings->venueText(), "text")."\n".
			"     , orgu_ref_id= ".$ilDB->quote($settings->orguRefId(), "integer")."\n".
			"     , description = ".$ilDB->quote($settings->description(), "text")."\n".
			"     , orga_info = ".$ilDB->quote($settings->orgaInfo(), "text")."\n".
			"     , webinar_link = ".$ilDB->quote($settings->webinarLink(), "text")."\n".
			"     , webinar_password = ".$ilDB->quote($settings->webinarPassword(), "text")."\n".
			"     , session_id = ".$ilDB->quote($a_request->sessionId(), "text")."\n".
			"     , title = ".$ilDB->quote($settings->title(), "text")."\n".
			"     , vc_type = ".$ilDB->quote($settings->vcType(), "text")."\n".
			"     , training_category = ".$ilDB->quote(serialize($settings->trainingCategory()), "text")."\n".
			"     , target_group = ".$ilDB->quote(serialize($settings->targetGroup()), "text")."\n".
			"     , gdv_topic = ".$ilDB->quote($settings->gdvTopic(), "text")."\n".
			"     , tmp_path_string = ".$ilDB->quote($settings->tmpPathString(), "text")."\n".
			"     , added_files = ".$ilDB->quote(serialize($settings->addedFiles()), "text")."\n".

			" WHERE request_id = ".$ilDB->quote($a_request->requestId(), "integer")."\n"
		);
	}
	
	public function deleteRequest(gevDecentralTrainingCreationRequest $a_request) {
		$ilDB = $this->getDB();
		if ($a_request->requestId() === null) {
			$this->throwException("Can't delete request without id.");
		}
		
		$ilDB->manipulate(
			"DELETE FROM ".self::TABLE_NAME."\n".
			" WHERE request_id = ".$ilDB->quote($a_request->requestId(), "integer")
		);
	}
	
	public function request($a_request_id) {
		assert(is_int($a_request_id));
		$ilDB = $this->getDB();
		$query = "SELECT * FROM ".self::TABLE_NAME." WHERE request_id = ".$ilDB->quote($a_request_id, "integer");
		$res = $ilDB->query($query);
		if ($rec = $ilDB->fetchAssoc($res)) {
			$settings = $this->newSettings( new ilDateTime($rec["start_dt"], IL_CAL_DATETIME)
										  , new ilDateTime($rec["end_dt"], IL_CAL_DATETIME)
										  , $rec["venue_obj_id"] ? (int)$rec["venue_obj_id"] : null
										  , $rec["venue_text"] ? $rec["venue_text"] : null
										  , $rec["orgu_ref_id"] ? (int)$rec["orgu_ref_id"] : null
										  , $rec["description"] ? $rec["description"] : ""
										  , $rec["orga_info"] ? $rec["orga_info"] : ""
										  , $rec["webinar_link"]
										  , $rec["webinar_password"]
										  , $rec["title"] ? $rec["title"] : null
										  , $rec["vc_type"] ? $rec["vc_type"] : null
										  , unserialize($rec["training_category"])
										  , unserialize($rec["target_group"])
										  , $rec["gdv_topic"] ? $rec["gdv_topic"] : null
										  , $rec["tmp_path_string"] ? $rec["tmp_path_string"] : null
										  , ($rec["added_file"] === null) ? null : unserialize($rec["added_files"])
										  );
			$trainer_ids = array_map(function($v) {return (int)$v;}, explode(self::ARRAY_DELIM, $rec["trainer_ids"]));
			$request = $this->newCreationRequest( (int)$rec["user_id"]
												, (int)$rec["template_obj_id"]
												, $trainer_ids
												, $settings
												, (int)$a_request_id
												, $rec["session_id"]
												, $rec["requested_ts"] ? new ilDateTime($rec["requested_ts"], IL_CAL_DATETIME) : null
												, $rec["finished_ts"] ? new ilDateTime($rec["finished_ts"], IL_CAL_DATETIME) : null
												, $rec["created_obj_id"] ? (int)$rec["created_obj_id"] : null
												);
			return $request;
		}
		else {
			$this->throwException("Unknown request: $a_request_id");
		}
	}

	public function openRequestsOfUser($a_user_id) {
		assert(is_int($a_user_id));
		assert(ilObject::_lookupType($a_user_id) == "usr");
		$ilDB = $this->getDB();
		$query = "SELECT * FROM ".self::TABLE_NAME.
				 " WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
				 "   AND NOT requested_ts IS NULL".
				 "   AND finished_ts IS NULL"
				 ;
		$res = $ilDB->query($query);
		$returns = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$settings = $this->newSettings( new ilDateTime($rec["start_dt"], IL_CAL_DATETIME)
										  , new ilDateTime($rec["end_dt"], IL_CAL_DATETIME)
										  , $rec["venue_obj_id"] ? (int)$rec["venue_obj_id"] : null
										  , $rec["venue_text"] ? $rec["venue_text"] : null
										  , $rec["orgu_ref_id"] ? (int)$rec["orgu_ref_id"] : null
										  , $rec["description"] ? $rec["description"] : ""
										  , $rec["orga_info"] ? $rec["orga_info"] : ""
										  , $rec["webinar_link"]
										  , $rec["webinar_password"]
										  , $rec["title"] ? $rec["title"] : null
										  , $rec["vc_type"] ? $rec["vc_type"] : null
										  , unserialize($rec["training_category"])
										  , unserialize($rec["target_group"])
										  , $rec["gdv_topic"] ? $rec["gdv_topic"] : null
										  , $rec["tmp_path_string"] ? $rec["tmp_path_string"] : null
										  , ($rec["added_file"] === null) ? null : unserialize($rec["added_files"])
										  );
			$trainer_ids = array_map(function($v) {return (int)$v;}, explode(self::ARRAY_DELIM, $rec["trainer_ids"]));
			$request = $this->newCreationRequest( (int)$rec["user_id"]
												, (int)$rec["template_obj_id"]
												, $trainer_ids
												, $settings
												, (int)$rec["request_id"]
												, $rec["session_id"]
												, $rec["requested_ts"] ? new ilDateTime($rec["requested_ts"], IL_CAL_DATETIME) : null
												, $rec["finished_ts"] ? new ilDateTime($rec["finished_ts"], IL_CAL_DATETIME) : null
												, $rec["created_obj_id"] ? (int)$rec["created_obj_id"] : null
												);
			$returns[] = $request;
		}
		return $returns;
	}
	
	public function nextOpenRequest() {
		$ilDB = $this->getDB();
		$query = "SELECT * FROM ".self::TABLE_NAME.
				 " WHERE NOT requested_ts IS NULL".
				 "   AND finished_ts IS NULL".
				 " ORDER BY request_id ASC LIMIT 1"
				 ;
		$res = $ilDB->query($query);
		if ($rec = $ilDB->fetchAssoc($res)) {
			$settings = $this->newSettings( new ilDateTime($rec["start_dt"], IL_CAL_DATETIME)
										  , new ilDateTime($rec["end_dt"], IL_CAL_DATETIME)
										  , $rec["venue_obj_id"] ? (int)$rec["venue_obj_id"] : null
										  , $rec["venue_text"] ? $rec["venue_text"] : null
										  , $rec["orgu_ref_id"] ? (int)$rec["orgu_ref_id"] : null
										  , $rec["description"] ? $rec["description"] : ""
										  , $rec["orga_info"] ? $rec["orga_info"] : ""
										  , $rec["webinar_link"]
										  , $rec["webinar_password"]
										  , $rec["title"] ? $rec["title"] : null
										  , $rec["vc_type"] ? $rec["vc_type"] : null
										  , unserialize($rec["training_category"])
										  , unserialize($rec["target_group"])
										  , $rec["gdv_topic"] ? $rec["gdv_topic"] : null
										  , $rec["tmp_path_string"] ? $rec["tmp_path_string"] : null
										  , ($rec["added_file"] === null) ? null : unserialize($rec["added_files"])
										  );
			$trainer_ids = array_map(function($v) {return (int)$v;}, explode(self::ARRAY_DELIM, $rec["trainer_ids"]));
			$request = $this->newCreationRequest( (int)$rec["user_id"]
												, (int)$rec["template_obj_id"]
												, $trainer_ids
												, $settings
												, (int)$rec["request_id"]
												, $rec["session_id"]
												, $rec["requested_ts"] ? new ilDateTime($rec["requested_ts"], IL_CAL_DATETIME) : null
												, $rec["finished_ts"] ? new ilDateTime($rec["finished_ts"], IL_CAL_DATETIME) : null
												, $rec["created_obj_id"] ? (int)$rec["created_obj_id"] : null
												);
			return $request;
		}
		else {
			return null;
		}
	}
	
	public function waitingTimeInMinuteEstimate() {
		$ilDB = $this->getDB();
		
		$query = "SELECT CEIL(AVG(TIME_TO_SEC(TIMEDIFF(finished_ts, requested_ts)) / 60)) min_avg\n"
				."  FROM ".self::TABLE_NAME."\n"
				." WHERE NOT finished_ts IS NULL";
		$res = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($res);
		$min_avg = $rec["min_avg"];
		
		$query = "SELECT COUNT(*) cnt\n"
				."  FROM ".self::TABLE_NAME."\n"
				." WHERE finished_ts IS NULL"
				."   AND NOT requested_ts IS NULL";
		$res = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($res);
		$open_requests = $rec["cnt"];
		
		return $min_avg * $open_requests;
	}
	
	public function lastCreatedTrainingOfUser($a_user_id) {
		assert(is_int($a_user_id));
		assert(ilObject::_lookupType($a_user_id) == "usr");
		
		$ilDB = $this->getDB();
		
		$query = "SELECT created_obj_id\n"
				."  FROM ".self::TABLE_NAME."\n"
				." WHERE NOT finished_ts IS NULL\n"
				."   AND NOT created_obj_id IS NULL\n"
				."   AND created_obj_id > 0"
				."   AND request_id = (SELECT MAX(request_id)\n"
				."                     FROM ".self::TABLE_NAME."\n"
				."                     WHERE NOT finished_ts IS NULL\n"
				."                         AND NOT created_obj_id IS NULL\n"
				."                         AND created_obj_id > 0\n"
				."                         AND user_id = ".$ilDB->quote($a_user_id,"integer").")\n";

		$res = $ilDB->query($query);
		if ($rec = $ilDB->fetchAssoc($res)) {
			return $rec["created_obj_id"];
		}
		return null;
	}
	
	// HELPERS
	
	protected function throwException($msg) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingException.php");
		throw new gevDecentralTrainingException($msg);
	}
	
	protected function newSettings( ilDateTime $a_start_datetime
							   , ilDateTime $a_end_datetime
							   , $a_venue_obj_id
							   , $a_venue_text
							   , $a_orgu_ref_id
							   , $a_description
							   , $a_orga_info
							   , $a_webinar_link
							   , $a_webinar_password
							   , $a_title
							   , $a_vc_type
							   , $a_training_category
							   , $a_target_group
							   , $a_gdv_topic
							   , $a_tmp_path_string
							   , $a_added_files
								  ) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingSettings.php");
		return new gevDecentralTrainingSettings( $a_start_datetime, $a_end_datetime, $a_venue_obj_id, $a_venue_text
											   , $a_orgu_ref_id, $a_description, $a_orga_info, $a_webinar_link
											   , $a_webinar_password,$a_title,$a_vc_type,$a_training_category,$a_target_group,$a_gdv_topic
											   , $a_tmp_path_string, $a_added_files);
	}
	
	protected function newCreationRequest( $a_user_id
										 , $a_template_obj_id
										 , array $a_trainer_ids
										 , gevDecentralTrainingSettings $a_settings
										 , $a_request_id
										 , $a_session_id
										 , ilDateTime $a_requested_ts = null
										 , ilDateTime $a_finished_ts = null
										 , $a_created_obj_id = null) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequest.php");
		return new gevDecentralTrainingCreationRequest( $this, $a_user_id, $a_template_obj_id, $a_trainer_ids, $a_settings
													  , $a_request_id, $a_session_id, $a_requested_ts, $a_finished_ts, $a_created_obj_id);
	}
	
	// GETTERS FOR GLOBALS
	
	protected function getDB() {
		global $ilDB;
		return $ilDB;
	}
	
	// Installation
	
	static public function install_step1(ilDB $ilDB) {
		if( $ilDB->tableExists(self::TABLE_NAME) ) {
			throw new ilException("Database ".self::TABLE_NAME." already exists.");
		}
		
		$ilDB->createTable(self::TABLE_NAME, array(
			'request_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'template_obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'requested_ts' => array(
				'type' => 'timestamp',
				'notnull' => true
			),
			'finished_ts' => array(
				'type' => 'timestamp',
				'notnull' => false
			),
			'created_obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => false
			),
			'trainer_ids' => array(
				'type' => 'text',
				'length' => 200,
				'notnull' => true
			),
			// Settings
			'start_dt' => array(
				'type' => 'timestamp',
				'notnull' => true
			),
			'end_dt' => array(
				'type' => 'timestamp',
				'notnull' => true
			),
			'venue_obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => false,
				'default' => null
			),
			'venue_text' => array(
				'type' => 'text',
				'length' => 4000,
				'notnull' => false,
				'default' => null
			),
			'orgu_ref_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => false,
				'default' => null
			),
			'description' => array(
				'type' => 'text',
				'length' => 128,
				'notnull' => false
			),
			'orga_info' => array(
				'type' => 'text',
				'length' => 4000,
				'notnull' => false
			),
			'webinar_link' => array(
				'type' => 'text',
				'length' => 200,
				'notnull' => false,
				'default' => null
			),
			'webinar_password' => array(
				'type' => 'text',
				'length' => 200,
				'notnull' => false,
				'default' => null
			)
		));
			
		$ilDB->addPrimaryKey(self::TABLE_NAME, array('request_id'));
		$ilDB->createSequence(self::TABLE_NAME);
	}
	
	static public function install_step2(ilDB $ilDB) {
		$ilDB->addTableColumn(self::TABLE_NAME, 'session_id', array(
			"type" => "text",
			"length" => 250,
			"notnull" => true
		));
	}
	
	static public function install_step3(ilDB $ilDB) {
		$ilDB->modifyTableColumn(self::TABLE_NAME, "requested_ts", array(
			'type' => 'timestamp',
			'notnull' => false
		));
	}

	static public function install_step4(ilDB $ilDB) {
		$ilDB->addTableColumn(self::TABLE_NAME, 'title', array(
			"type" => "text",
			"length" => 100,
			"notnull" => false
		));

		$ilDB->addTableColumn(self::TABLE_NAME, 'vc_type', array(
			"type" => "text",
			"length" => 100,
			"notnull" => false
		));

		$ilDB->addTableColumn(self::TABLE_NAME, 'training_category', array(
			"type" => "text",
			"length" => 4000,
			"notnull" => false
		));

		$ilDB->addTableColumn(self::TABLE_NAME, 'target_group', array(
			"type" => "text",
			"length" => 4000,
			"notnull" => false
		));

		$ilDB->addTableColumn(self::TABLE_NAME, 'gdv_topic', array(
			"type" => "text",
			"length" => 100,
			"notnull" => false
		));
	}
	
	static public function install_step5(ilDB $ilDB) {
		$ilDB->modifyTableColumn(self::TABLE_NAME, 'session_id', array(
			"type" => "text",
			"length" => 250,
			"notnull" => false
		));
	}

	static public function install_step6(ilDB $ilDB) {
		if(!$ilDB->tableColumnExists(self::TABLE_NAME, 'tmp_path_string')) {
			$ilDB->addTableColumn(self::TABLE_NAME, 'tmp_path_string', array(
				"type" => "text",
				"length" => 250,
				"notnull" => false
			));
		}
		
		if(!$ilDB->tableColumnExists(self::TABLE_NAME, 'added_files')) {
			$ilDB->addTableColumn(self::TABLE_NAME, 'added_files', array(
				"type" => "text",
				"length" => 4000,
				"notnull" => false
			));
		}
	}
}
