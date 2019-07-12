<?php

namespace ILIAS\Changelog\Query\Requests;

use ILIAS\Changelog\Query\Requests\Filters\getLogsOfUserFilter;

/**
 * Class getLogsOfUserRequest
 * @package ILIAS\Changelog\Query\Requests
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class getLogsOfUserRequest extends Request {

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var getLogsOfUserFilter
	 */
	protected $filter;

	/**
	 * getLogsOfUsersRequest constructor.
	 * @param int $user_id
	 */
	public function __construct(int $user_id) {
		$this->user_id = $user_id;
		$this->filter = new getLogsOfUserFilter();
	}

	/**
	 * @param getLogsOfUserFilter $getLogsOfUsersFilter
	 */
	public function setFilter(getLogsOfUserFilter $getLogsOfUsersFilter) {
		$this->filter = $getLogsOfUsersFilter;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}

	/**
	 * @return getLogsOfUserFilter
	 */
	public function getFilter(): getLogsOfUserFilter {
		return $this->filter;
	}

}