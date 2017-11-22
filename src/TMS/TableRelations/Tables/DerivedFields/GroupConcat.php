<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Group concat all the entries in a field.
 */
class GroupConcat extends T\DerivedField  {
	protected $separator;
	protected $f_order_by;
	protected $order_direction;

	public function __construct(
		Filters\PredicateFactory $f,
		$name,
		Filters\Predicates\Field $field,
		$separator = ', ',
		Filters\Predicates\Field $order_by = null,
		$order_direction
	) {
		assert('is_string($order_direction)');
		$this->derived_from[] = $field;
		$this->separator = $separator;
		$this->arg = $field;
		$this->f_order_by = $order_by;
		$this->order_direction = $order_direction;
		parent::__construct($f, $name);
	}

	/**
	 * The field being concat.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}

	/**
	 * Concat using a separator.
	 *
	 * @return 	string
	 */
	public function separator() {
		return $this->separator;
	}

	/**
	 * Concat ordered by field.
	 *
	 * @return	AbstractField
	 */
	public function orderBy()
	{
		return $this->f_order_by;
	}

	/**
	 * Concat ordered with order direction.
	 *
	 * @return	string
	 */
	public function orderDirection()
	{
		return $this->order_direction;
	}
}
