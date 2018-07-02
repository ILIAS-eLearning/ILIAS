<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

use ILIAS\TMS\ScheduledEvents;

/**
 * Implementation for ILIAS\TMS\ScheduledEvents\DB
 */

class Schedule implements ScheduledEvents\DB {

	const TABLE_NAME = "scheduled_events";
	const TABLE_NAME_PARAMS = "scheduled_params";

	const DATETIME_FORMAT = 'Y-m-d H:i:s';


	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db) {
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function create($issuer_ref, \DateTime $due, $component, $event, $params = array()) {
		$id = $this->getNextId();
		$event = new ScheduledEvents\Event($id, $issuer_ref, $due, $component, $event, $params);

		$values = array(
			"id" => array("integer", $event->getId()),
			"issuer_ref_id" => array("integer", $event->getIssuerRef()),
			"due" => array("text", $event->getDue()->format(self::DATETIME_FORMAT)),
			"component" => array("text", $event->getComponent()),
			"event" => array("text", $event->getEvent())
		);

		$this->getDB()->insert(static::TABLE_NAME, $values);
		foreach ($event->getParameters() as $key => $value) {
			$values = array(
				"id" => array("integer", $event->getId()),
				"pkey" => array("text", $key),
				"pval" => array("text", $value)
			);
			$this->getDB()->insert(static::TABLE_NAME_PARAMS, $values);
		}
		return $event;
	}

	/**
	 * @inheritdoc
	 */
	public function getAll() {
		$query = "SELECT id, issuer_ref_id, due, component, event"
				." FROM ".static::TABLE_NAME .PHP_EOL
				." ORDER BY due ASC" .PHP_EOL;
		return $this->getEvents($query);
	}

	/**
	 * @inheritdoc
	 */
	public function getAllDue() {
		$now = new DateTime();
		$query = "SELECT id, issuer_ref_id, due, component, event".PHP_EOL
			." FROM ".static::TABLE_NAME .PHP_EOL
			." WHERE due <= "
			. $this->getDB()->quote($now->format(self::DATETIME_FORMAT), "text")
			.PHP_EOL
			." ORDER BY due ASC" .PHP_EOL;;

		return $this->getEvents($query);
	}

	/**
	 * @inheritdoc
	 */
	public function getAllFromIssuer($ref_id, $component=null, $event=null) {
		assert('is_int($ref_id)');
		assert('is_string($component) || is_null($component)');
		assert('is_string($event) || is_null($event)');

		$query = "SELECT id, issuer_ref_id, due, component, event".PHP_EOL
				." FROM ".static::TABLE_NAME .PHP_EOL
				." WHERE issuer_ref_id = " .$this->getDB()->quote($ref_id, "integer")
				.PHP_EOL;
		if($component) {
			$query .= ' AND component = ' .$this->getDB()->quote($component, "text") .PHP_EOL;
		}
		if($event) {
			$query .= ' AND event = ' .$this->getDB()->quote($event, "text") .PHP_EOL;
		}
		$query .=" ORDER BY due ASC" .PHP_EOL;

		return $this->getEvents($query);
	}

	/**
	 * @inheritdoc
	 */
	public function setAccountedFor($events) {
		assert('is_array($events)');
		$this->delete($events);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($events) {
		assert('is_array($events)');
		if(count($events) > 0 ) {
			$del_ids = array();
			foreach ($events as $event) {
				$del_ids[] = $this->getDB()->quote((int)$event->getId(), "integer");
			}

			$where =" WHERE id IN (".implode(', ', $del_ids).')';
			$query = "DELETE FROM ".static::TABLE_NAME_PARAMS .PHP_EOL .$where;
			$this->getDB()->query($query);
			$query = "DELETE FROM ".static::TABLE_NAME .PHP_EOL .$where;
			$this->getDB()->query($query);
		}
	}


	/**
	 * Get Paramter for distinct event
	 *
	 * @param int 	$id
	 * @return array <strin,string>
	 */
	protected function getParamsFor($id) {
		$params = array();
		$query = "SELECT pkey, pval FROM ".static::TABLE_NAME_PARAMS .PHP_EOL
				." WHERE id = " .$this->getDB()->quote($id, "integer");
		$params_result = $this->getDB()->query($query);
		while ($param_row = $this->getDB()->fetchAssoc($params_result)) {
			$params[$param_row['pkey']] = $param_row['pval'];
		}
		return $params;
	}

	/**
	 * execute query and build Events from result.
	 *
	 * @param string 	$query
	 * @return Event[]
	 */
	protected function getEvents($query) {
		$ret = array();
		$result = $this->getDB()->query($query);
		while ($row = $this->getDB()->fetchAssoc($result)) {
			$ret[] = new ScheduledEvents\Event(
				(int)$row['id'],
				(int)$row['issuer_ref_id'],
				new DateTime($row['due']),
				$row['component'],
				$row['event'],
				$this->getParamsFor((int)$row['id'])
			);
		}
		return $ret;
	}


	/**
	 * Get the db handler
	 * @throws \Exception
	 * @return \ilDB
	 */
	protected function getDB() {
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}

	/**
	 * Get next id for entry
	 */
	public function getNextId() {
		return (int)$this->db->nextId(static::TABLE_NAME);
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
				'issuer_ref_id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> true
				),
				'due' => array(
					'type' => 'timestamp',
					'notnull' => true
				),
				'component' => array(
					'type' 		=> 'text',
					'length' 	=> 128,
					'notnull' 	=> true
				),
				'event' => array(
					'type' 		=> 'text',
					'length' 	=> 128,
					'notnull' 	=> true
				)
			);
			$this->db->createTable(static::TABLE_NAME, $fields);
		}
	}

	/**
	 * create table
	 *
	 * @return void
	 */
	public function createParamsTable() {
		if (!$this->db->tableExists(static::TABLE_NAME_PARAMS)) {
			$fields = array(
				'id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> true
				),
				'pkey' => array(
					'type' 		=> 'text',
					'length' 	=> 128,
					'notnull' 	=> true
				),
				'pval' => array(
					'type' 		=> 'text',
					'length' 	=> 256,
					'notnull' 	=> true
				)
			);
			$this->db->createTable(static::TABLE_NAME_PARAMS, $fields);
		}
	}


	/**
	 * Configure primary key on table
	 *
	 * @return void
	 */
	public function createPrimaryKeyForParams(){
		$this->db->addPrimaryKey(static::TABLE_NAME_PARAMS, array("id", "pkey"));
	}


}
