<?php
/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

/**
 * Class IsNull
 *
 * @package ILIAS\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsNull extends Custom implements Constraint {

	/**
	 * IsNull constructor.
	 *
	 * @param Data\Factory $data_factory
	 */
	public function __construct(Data\Factory $data_factory) {
		parent::__construct(
			function ($value) {
				return is_null($value);
			}, function ($value) {
			if (is_array($value)) {
				return "array is not null.";
			}
			if (is_object($value)) {
				return "object of type'" . gettype($value) . "' is not null.";
			}
			if (is_string($value)) {
				return "string is not null.";
			}

			return "'" . $value . "' is not null.";
		}, $data_factory
		);
	}
}
