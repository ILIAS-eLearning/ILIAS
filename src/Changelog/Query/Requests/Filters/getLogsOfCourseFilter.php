<?php

namespace ILIAS\Changelog\Query\Requests\Filters;


use ilDateTime;

/**
 * Class getLogsOfCourseFilter
 * @package ILIAS\Changelog\Query\Requests\Filters
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class getLogsOfCourseFilter {
	/**
	 * @var ilDateTime
	 */
	protected $date_from = null;
	/**
	 * @var ilDateTime
	 */
	protected $date_to = null;
	/**
	 * @var int
	 */
	protected $event_type = null;
	/**
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * @return ilDateTime|null
	 */
	public function getDateFrom() {
		return $this->date_from;
	}

	/**
	 * @param ilDateTime $date_from
	 */
	public function setDateFrom(ilDateTime $date_from) {
		$this->date_from = $date_from;
	}

	/**
	 * @return ilDateTime|null
	 */
	public function getDateTo() {
		return $this->date_to;
	}

	/**
	 * @param ilDateTime $date_to
	 */
	public function setDateTo(ilDateTime $date_to) {
		$this->date_to = $date_to;
	}

	/**
	 * @return int|null
	 */
	public function getEventType() {
		return $this->event_type;
	}

	/**
	 * @param int $event_type
	 */
	public function setEventType(int $event_type) {
		$this->event_type = $event_type;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId(int $user_id) {
		$this->user_id = $user_id;
	}



}