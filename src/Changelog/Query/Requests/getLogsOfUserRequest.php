<?php

namespace ILIAS\Changelog\Query\Requests;

use ILIAS\Changelog\Query\Requests\Filters\getLogsOfUserFilter;

/**
 * Class getLogsOfUsersRequest
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class getLogsOfUserRequest {

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var getLogsOfUserFilter
	 */
	protected $filter;
	/**
	 * @var int
	 */
	protected $limit = 0;
	/**
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @var string
	 */
	protected $orderBy = null;

	/**
	 * @var string
	 */
	protected $orderDirection = 'ASC';

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
	public function getLimit(): int {
		return $this->limit;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit(int $limit) {
		$this->limit = $limit;
	}

	/**
	 * @return int
	 */
	public function getOffset(): int {
		return $this->offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset(int $offset) {
		$this->offset = $offset;
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

	/**
	 * @return string
	 */
	public function getOrderBy(): string {
		return $this->orderBy;
	}

	/**
	 * @param string $orderBy
	 */
	public function setOrderBy(string $orderBy) {
		$this->orderBy = $orderBy;
	}

	/**
	 * @return string
	 */
	public function getOrderDirection(): string {
		return $this->orderDirection;
	}

	/**
	 * @param string $orderDirection
	 */
	public function setOrderDirection(string $orderDirection) {
		$this->orderDirection = $orderDirection;
	}

}