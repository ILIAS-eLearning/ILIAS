<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Filter\Sort;

use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField as TableFilterSortFieldInterface;
use ILIAS\UI\Component\Table\Data\Filter\Storage\TableFilterStorage;
use stdClass;

/**
 * Class TableFilterSortField
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Filter\Sort
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class TableFilterSortField implements TableFilterSortFieldInterface {

	/**
	 * @var string
	 */
	protected $sort_field = "";
	/**
	 * @var int
	 */
	protected $sort_field_direction = 0;


	/**
	 * @inheritDoc
	 */
	public function __construct(string $sort_field, int $sort_field_direction) {
		$this->sort_field = $sort_field;

		$this->sort_field_direction = $sort_field_direction;
	}


	/**
	 * @inheritDoc
	 */
	public function getSortField(): string {
		return $this->sort_field;
	}


	/**
	 * @inheritDoc
	 */
	public function withSortField(string $sort_field): TableFilterSortFieldInterface {
		$clone = clone $this;

		$clone->sort_field = $sort_field;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getSortFieldDirection(): int {
		return $this->sort_field_direction;
	}


	/**
	 * @inheritDoc
	 */
	public function withSortFieldDirection(int $sort_field_direction): TableFilterSortFieldInterface {
		$clone = clone $this;

		$clone->sort_field_direction = $sort_field_direction;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): stdClass {
		return (object)[
			TableFilterStorage::VAR_SORT_FIELD => $this->sort_field,
			TableFilterStorage::VAR_SORT_FIELD_DIRECTION => $this->sort_field_direction
		];
	}
}
