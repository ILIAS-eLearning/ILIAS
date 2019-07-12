<?php

namespace ILIAS\Changelog\Query\Requests;


/**
 * Class Request
 * @package ILIAS\Changelog\Query\Requests
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class Request {

	const ORDER_ASCENDING = 'ASC';
	const ORDER_DESCENDING = 'DESC';

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
	protected $orderBy = '';

	/**
	 * @var string
	 */
	protected $orderDirection = self::ORDER_ASCENDING;

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
	 */
	public function setOrderDirectionAscending() {
		$this->orderDirection = self::ORDER_ASCENDING;
	}

	/**
	 *
	 */
	public function setOrderDirectionDescending() {
		$this->orderDirection = self::ORDER_DESCENDING;
	}

}