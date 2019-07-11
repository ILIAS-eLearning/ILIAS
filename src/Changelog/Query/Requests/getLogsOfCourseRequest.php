<?php

namespace ILIAS\Changelog\Query\Requests;


use ILIAS\Changelog\Query\Requests\Filters\getLogsOfCourseFilter;
use ILIAS\Changelog\Query\Requests\Filters\getLogsOfUserFilter;

/**
 * Class getLogsOfCourseRequest
 * @package ILIAS\Changelog\Query\Requests
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class getLogsOfCourseRequest {
	/**
	 * @var int
	 */
	protected $crs_obj_id;

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
	protected $orderBy = 'timestamp';

	/**
	 * @var string
	 */
	protected $orderDirection = 'ASC';

	/**
	 * getLogsOfUsersRequest constructor.
	 * @param int $crs_obj_id
	 */
	public function __construct(int $crs_obj_id) {
		$this->crs_obj_id = $crs_obj_id;
		$this->filter = new getLogsOfUserFilter();
	}

	/**
	 * @param getLogsOfCourseFilter $getLogsOfUsersFilter
	 */
	public function setFilter(getLogsOfCourseFilter $getLogsOfUsersFilter) {
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
	public function getCrsObjId(): int {
		return $this->crs_obj_id;
	}

	/**
	 * @return getLogsOfCourseFilter
	 */
	public function getFilter(): getLogsOfCourseFilter {
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