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
class getLogsOfCourseRequest extends Request {

	const DEFAULT_ORDER_FIELD = 'timestamp';

	/**
	 * @var int
	 */
	protected $crs_obj_id;

	/**
	 * @var getLogsOfUserFilter
	 */
	protected $filter;

	/**
	 * getLogsOfCourseRequest constructor.
	 * @param int $crs_obj_id
	 */
	public function __construct(int $crs_obj_id) {
		$this->crs_obj_id = $crs_obj_id;
		$this->filter = new getLogsOfCourseFilter();
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
	public function getDefaultOrderField(): string {
		return self::DEFAULT_ORDER_FIELD;
	}


}