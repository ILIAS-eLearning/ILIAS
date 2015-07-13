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
	
	public function __construct() {
		
	}
	
	public function getRequest($a_request_id) {
		assert(is_int($a_request_id));
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
			"       orga_info, webinar_link, webinar_password)\n".
			" VALUES ( ".$ilDB->quote($request_id, "integer")."\n".
			"        , ".$ilDB->quote($a_request->userId(), "integer")."\n".
			"        , ".$ilDB->quote($a_request->templateObjId(), "integer")."\n".
			"        , ".$ilDB->quote($requested_ts ? $requested_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"        , ".$ilDB->quote($finished_ts ? $finished_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"        , ".$ilDB->quote($a_request->createdObjId(), "integer")."\n".
			"        , ".$ilDB->quote(implode(";", $a_request->trainerIds()), "text")."\n".
			"        , ".$ilDB->quote($settings->start()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"        , ".$ilDB->quote($settings->end()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"        , ".$ilDB->quote($settings->venueObjId(), "integer")."\n".
			"        , ".$ilDB->quote($settings->venueText(), "text")."\n".
			"        , ".$ilDB->quote($settings->orguRefId(), "integer")."\n".
			"        , ".$ilDB->quote($settings->description(), "text")."\n".
			"        , ".$ilDB->quote($settings->orgaInfo(), "text")."\n".
			"        , ".$ilDB->quote($settings->webinarLink(), "text")."\n".
			"        , ".$ilDB->quote($settings->webinarPassword(), "text")."\n".
			"        )\n"
		);
		return $request_id;
	}
	
	public function updateRequest(gevDecentralTrainingCreationRequest $request) {
		$ilDB = $this->getDB();
		if ($request->requestId() === null) {
			$this->throwException("Can't update request withou id.");
		}
		$settings = $a_request->settings();
		$requested_ts = $a_request->requestedTS();
		$finished_ts = $a_request->finishedTS();
		$ilDB->manipulate(
			"UPDATE ".self::TABLE_NAME."\n".
			"       (request_id, user_id, template_obj_id, requested_ts,\n".
			"       finished_ts, created_obj_id, trainer_ids, start_dt,\n".
			"       end_dt, venue_obj_id, venue_text, orgu_ref_id, description,\n".
			"       orga_info, webinar_link, webinar_password)\n".
			" SET ( user_id = ".$ilDB->quote($a_request->userId(), "integer")."\n".
			"     , template_obj_id = ".$ilDB->quote($a_request->templateObjId(), "integer")."\n".
			"     , requested_ts = ".$ilDB->quote($requested_ts ? $requested_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"     , finished_ts = ".$ilDB->quote($finished_ts ? $finished_ts->get(IL_CAL_DATETIME) : null, "timestamp")."\n".
			"     , created_obj_id = ".$ilDB->quote($a_request->createdObjId(), "integer")."\n".
			"     , trainer_ids = ".$ilDB->quote(implode(";", $a_request->trainerIds()), "text")."\n".
			"     , start_dt = ".$ilDB->quote($settings->start()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"     , end_dt = ".$ilDB->quote($settings->end()->get(IL_CAL_DATETIME), "timestamp")."\n".
			"     , venue_obj_id = ".$ilDB->quote($settings->venueObjId(), "integer")."\n".
			"     , venue_text = ".$ilDB->quote($settings->venueText(), "text")."\n".
			"     , orgu_ref_id= ".$ilDB->quote($settings->orguRefId(), "integer")."\n".
			"     , description = ".$ilDB->quote($settings->description(), "text")."\n".
			"     , orga_info = ".$ilDB->quote($settings->orgaInfo(), "text")."\n".
			"     , webinar_link = ".$ilDB->quote($settings->webinarLink(), "text")."\n".
			"     , webinar_password = ".$ilDB->quote($settings->webinarPassword(), "text")."\n".
			"     )\n".
			" WHERE request_id = ".$ilDB->quote($request->requestId(), "integer")."\n"
		);
	}
	
	
	// HELPERS
	
	protected function throwException($msg) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingException.php");
		throw new gevDecentralTrainingException($msg);
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
}