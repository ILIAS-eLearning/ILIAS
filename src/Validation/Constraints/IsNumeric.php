<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class IsNumeric extends Custom implements Constraint {
	public function __construct(Data\Factory $data_factory) {
		parent::__construct( function ($value) {
				return is_numeric($value);
			}, 
			function ($value) {
				if (is_array($value)) {
					return "array is not a number.";
				}
				if (is_object($value)) {
					return "object of type'".gettype($value)."' is not a number.";
				}
				return "'".$value."' is not a number.";
			}, $data_factory);
	}
}
