<?php
namespace ILIAS\Data\Domain\Event;
use ActiveRecord;
use \ilException;

/**
 * Class AbstractActiveRecordStoredEvent
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
abstract class AbstractStoredEvent extends ActiveRecord  {

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @con_sequence   true
	 */
	protected $event_id;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     200
	 * @con_index      true
	 * @con_is_notnull true
	 */
	protected $aggregate_id;
	/**
	 * @var string
	 *
	 * @con_has_field  text
	 * @con_fieldtype  integer
	 * @con_length     200
	 * @con_index      true
	 * @con_is_notnull true
	 */
	protected $event_name;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  timestamp
	 * @con_index      true
	 * @con_is_notnull true
	 */
	protected $occured_on;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_index      true
	 * @con_is_notnull true
	 */
	protected $initiating_user_id;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     3000
	 * @con_is_notnull true
	 */
	protected $event_body = '';


	/**
	 * ilDBQuestionEventStore constructor.
	 *
	 * @param string $aggregate_id
	 * @param string $event_name
	 * @param \DateTime $occured_on
	 * @param int $initiating_user_id
	 * @param string $event_body
	 */
	//TODO FIND a way for the Default constructor. Meanmwile the Install-Table
	// process doesn't work with a constructor.
	/*
	public function __construct(
		int $aggregate_id,
		string $event_name,
		\DateTime $occured_on,
		int $initiating_user_id,
		string $event_body)
	{
		parent::__construct();

		$this->aggregate_id = $aggregate_id;
		$this->event_name = $event_name;
		$this->occured_on = $occured_on;
		$this->initiating_user_id = $initiating_user_id;
		$this->event_body = $event_body;
	}*/

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		self::STORAGE_NAME;
	}


	/**
	 * @return int
	 */
	public function getEventId(): int {
		return $this->event_id;
	}


	public function getAggregateId(): IdentifiesAggregate {
		// TODO: Implement getAggregateId() method.
	}


	/**
	 * @return string
	 */
	public function getEventName(): string {
		return $this->event_name;
	}


	/**
	 * @return int
	 */
	public function getOccuredOn(): int {
		return $this->occured_on;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;
	}


	/**
	 * @return string
	 */
	public function getEventBody(): string {
		return $this->event_body;
	}

	//
	// CRUD
	//
	/**
	 *
	 */
	public function create() {
		parent::create();
	}


	//
	// Not supported CRUD-Options:
	//
	/**
	 * @throws ilException
	 */
	public function store() {
		throw new ilException("Store is not supported - It's only possible to add new records to this store!");
	}


	/**
	 * @throws ilException
	 */
	public function update() {
		throw new ilException("Update is not supported - It's only possible to add new records to this store!");
	}


	/**
	 * @throws ilException
	 */
	public function delete() {
		throw new ilException("Delete is not supported - It's only possible to add new records to this store!");
	}


	/**
	 * @throws ilException
	 */
	public function save() {
		throw new ilException("Save is not supported - It's only possible to add new records to this store!");
	}
}
