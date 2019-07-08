<?php

namespace ILIAS\UI\Component\Table\Data\Filter\Sort;

use JsonSerializable;
use stdClass;

/**
 * Interface TableFilterSortField
 *
 * @package ILIAS\UI\Component\Table\Data\Filter\Sort
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface TableFilterSortField extends JsonSerializable {

	/**
	 * @var int
	 */
	const SORT_DIRECTION_UP = 1;
	/**
	 * @var int
	 */
	const SORT_DIRECTION_DOWN = 2;


	/**
	 * TableFilterSortField constructor
	 *
	 * @param string $sort_field
	 * @param int    $sort_field_direction
	 */
	public function __construct(string $sort_field, int $sort_field_direction);


	/**
	 * @return string
	 */
	public function getSortField(): string;


	/**
	 * @param string $sort_field
	 *
	 * @return self
	 */
	public function withSortField(string $sort_field): self;


	/**
	 * @return int
	 */
	public function getSortFieldDirection(): int;


	/**
	 * @param string $sort_field_direction
	 *
	 * @return self
	 */
	public function withSortFieldDirection(int $sort_field_direction): self;


	/**
	 * @return stdClass
	 */
	public function jsonSerialize(): stdClass;
}
