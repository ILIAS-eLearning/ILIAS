<?php

/**
 * Class getLogsOfUsersFilter
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class getLogsOfUserFilter {

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
	 * @return ilDateTime
	 */
	public function getDateFrom(): ilDateTime {
		return $this->date_from;
	}

	/**
	 * @param ilDateTime $date_from
	 */
	public function setDateFrom(ilDateTime $date_from) {
		$this->date_from = $date_from;
	}

	/**
	 * @return ilDateTime
	 */
	public function getDateTo(): ilDateTime {
		return $this->date_to;
	}

	/**
	 * @param ilDateTime $date_to
	 */
	public function setDateTo(ilDateTime $date_to) {
		$this->date_to = $date_to;
	}

	/**
	 * @return int
	 */
	public function getEventType(): int {
		return $this->event_type;
	}

	/**
	 * @param int $event_type
	 */
	public function setEventType(int $event_type) {
		$this->event_type = $event_type;
	}


}