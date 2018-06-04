<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints\Password;

use ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;


class HasMinLength extends Constraints\Custom implements Constraint {
	/**
	 * @var int
	 */
	protected $min_length;

	public function __construct($min_length, Data\Factory $data_factory) {
		assert('is_int($min_length)');
		$this->min_length = $min_length;
		parent::__construct( function (Data\Password $value) {
				return strlen($value->toString()) >= $this->min_length;
			},
			function ($value) {
				return "Password has a length less than '{$this->min_length}'.";
			},
			$data_factory
		);
	}

}