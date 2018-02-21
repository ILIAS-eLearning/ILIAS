<?php

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;

/**
 * Class IsArrayOfAny
 *
 * @package ILIAS\Validation\Constraints
 */
class IsArrayOfAny extends Custom implements Constraint {

	/**
	 * IsArrayOfAny constructor.
	 *
	 * @param \ILIAS\Data\Factory $data_factory
	 */
	public function __construct(Data\Factory $data_factory) {
		parent::__construct(function ($value) {
			return is_array($value);
		}, function ($value) {
			return "'" . gettype($value) . "' is not an array.";
		}, $data_factory);
	}
}