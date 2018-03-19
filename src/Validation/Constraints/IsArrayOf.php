<?php
/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;

/**
 * Class IsArrayOf
 *
 * @package ILIAS\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsArrayOf extends Custom implements Constraint {

	/**
	 * IsArrayOf constructor.
	 *
	 * @param Data\Factory $data_factory
	 * @param Constraint   $on_element
	 */
	public function __construct(Data\Factory $data_factory, Constraint $on_element) {
		parent::__construct(
			function ($value) use ($on_element) {
				if (!is_array($value)) {
					return false;
				}
				foreach ($value as $item) {
					if (!$on_element->accepts($item)) {
						return false;
					}
				}

				return true;
			}, function ($value) use ($on_element) {
			if (!is_array($value)) {
				return "'Value must be type of array, " . gettype($value) . " given'.";
			}

			return "'All elements of array must be of Constraint " . get_class($on_element) . "'.";
		}, $data_factory
		);
	}
}