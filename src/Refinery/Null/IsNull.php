<?php
/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Null;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;
use ILIAS\Refinery\Constraint;

/**
 * Class IsNull
 *
 * @package ILIAS\Refinery\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsNull extends CustomConstraint implements Constraint {

	/**
	 * IsNull constructor.
	 *
	 * @param Data\Factory $data_factory
	 */
	public function __construct(Data\Factory $data_factory, \ilLanguage $lng) {
		parent::__construct(
			function ($value) {
				return is_null($value);
			},
			function ($txt, $value) {
				return $txt("not_a_null", gettype($value));
			},
			$data_factory,
			$lng
		);
	}
}
