<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

use ILIAS\TMS\Mailing\LogEntry;
use ILIAS\TMS\Mailing\LoggingDB;

/**
 * Implemention for DB
 */
class ilTMSMailingLogsDB implements LoggingDB {

	const TABLE_NAME = "mail_logs";

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db){
		$this->db = $db;
	}

	/**
	 * Get next id for entry
	 */
	public function getNextId() {
		return (int)$this->db->nextId(static::TABLE_NAME);
	}

	/**
	 *@inheritdoc
	 */
	public function log($event, $template_ident, $usr_mail,
		$usr_name = '', $usr_id = null, $usr_login = '',
		$crs_ref_id = null, $subject = '', $msg = '', $error='') {

		$id = $this->getNextId();
		$date = new \ilDateTime(date('c'), IL_CAL_DATETIME);

		$entry = new LogEntry($id, $date,
			$event, $crs_ref_id, $template_ident,
			$usr_id, $usr_login, $usr_name, $usr_mail,
			$subject, $msg, $error);

		$values = array(
			"id" => array("integer", $entry->getId()),
			"datetime" => array("text", $entry->getDate()->get(IL_CAL_DATETIME)),
			"event" => array("text", $entry->getEvent()),
			"template_ident" => array("text", $entry->getTemplateIdent()),
			"usr_mail" => array("text", $entry->getUserMail()),
			"usr_name" => array("text", $entry->getUserName()),
			"usr_login" => array("text", $entry->getUserLogin()),
			"subject" => array("text", $entry->getSubject()),
			"msg" => array("text", $entry->getMessage()),
			"error" => array("text", $entry->getError()),
		);
		if(! is_null($entry->getCourseRefId())) {
			$values["crs_ref_id"] = array("integer", $entry->getCourseRefId());
		}
		if(! is_null($entry->getUserId())) {
			$values["usr_id"] = array("integer", $entry->getUserId());
		}

		$this->db->insert(static::TABLE_NAME, $values);
		return $entry;
	}

	/**
	 *@inheritdoc
	 */
	public function	selectForCourse($ref_id, $sort=null, $limit=null) {
		assert('is_int($ref_id)');
		$query = "SELECT" .PHP_EOL
				." id, datetime, event, crs_ref_id, template_ident,"
				." usr_id, usr_login, usr_mail, usr_name,"
				." subject, msg, error" .PHP_EOL
				." FROM ".static::TABLE_NAME .PHP_EOL
				." WHERE crs_ref_id = " .$this->db->quote($ref_id, "integer")
				.PHP_EOL;

		if($sort) {
			list($field, $direction) = $sort;
			$query .= ' ORDER BY ' .$field  .' ' .strtoupper($direction) .PHP_EOL;
		}
		if($limit) {
			list($length, $offset) = $limit;
			$query .= ' LIMIT ' .$this->db->quote($length, "integer");
			$query .= ' OFFSET ' .$this->db->quote($offset, "integer");
		}

		$ret = array();
		$result = $this->db->query($query);
		while ($row = $this->db->fetchAssoc($result)) {
			$entry = new LogEntry(
				(int)$row["id"],
				new \ilDateTime($row["datetime"], IL_CAL_DATETIME),
				(string)$row["event"],
				(int)$row["crs_ref_id"],
				(string)$row["template_ident"],
				(int)$row["usr_id"],
				(string)$row["usr_login"],
				(string)$row["usr_name"],
				(string)$row["usr_mail"],
				(string)$row["subject"],
				(string)$row["msg"],
				(string)$row["error"]
			);
			$ret[] = $entry;
		}
		return $ret;
	}

	/**
	 *@inheritdoc
	 */
	public function	selectCountForCourse($ref_id) {
		$query = "SELECT COUNT ('id') FROM " .static::TABLE_NAME.PHP_EOL
			." WHERE crs_ref_id = " .$this->db->quote($ref_id, "integer");
		$result = $this->db->query($query);
		$ret = (int)array_values($this->db->fetchAssoc($result))[0];
		return $ret;
	}


	/**
	 * create table
	 *
	 * @return void
	 */
	public function createTable() {
		if (!$this->db->tableExists(static::TABLE_NAME)) {
			$fields = array(
				'id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> true
				),
				'datetime' => array(
					'type' => 'timestamp',
					'notnull' => true
				),
				'event' => array(
					'type' 		=> 'text',
					'length' 	=> 128,
					'notnull' 	=> false
				),
				'crs_ref_id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> false
				),
				'template_ident' => array(
					'type' 		=> 'text',
					'length' 	=> 64,
					'notnull' 	=> true
				),
				'usr_id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> false
				),
				'usr_login' => array(
					'type' 		=> 'text',
					'length' 	=> 255,
					'notnull' 	=> false
				),
				'usr_mail' => array(
					'type' 		=> 'text',
					'length' 	=> 255,
					'notnull' 	=> true
				),
				'usr_name' => array(
					'type' 		=> 'text',
					'length' 	=> 255,
					'notnull' 	=> false
				),
				'subject' => array(
					'type' 		=> 'clob',
					'notnull' 	=> false
				),
				'msg' => array(
					'type' 		=> 'clob',
					'notnull' 	=> false
				),
				'error' => array(
					'type' 		=> 'clob',
					'notnull' 	=> false
				)
			);
			$this->db->createTable(static::TABLE_NAME, $fields);
		}
	}

	/**
	 * Configure primary key on table
	 *
	 * @return void
	 */
	public function createPrimaryKey(){
		$this->db->addPrimaryKey(static::TABLE_NAME, array("id"));
	}

	/**
	 * Create sequences for ids
	 *
	 * @return void
	 */
	public function createSequence(){
		$this->db->createSequence(static::TABLE_NAME);
	}

}
