<?php
/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

/**
 * Class IsString
 *
 * @package ILIAS\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsString extends Custom implements Constraint {

	/**
	 * IsString constructor.
	 *
	 * @param \ILIAS\Data\Factory $data_factory
	 */
	public function __construct(Data\Factory $data_factory) {
		parent::__construct(function ($value) {
			return is_string($value);
		}, function ($value) {
			return "'" . gettype($value) . "' is not a string.";
		}, $data_factory);
	}
}