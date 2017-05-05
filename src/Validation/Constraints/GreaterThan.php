<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class GreaterThan extends Custom implements Constraint {
	/**
	 * @var int
	 */
	protected $min;

	public function __construct($min, Data\Factory $data_factory) {
		assert('is_int($min)');
		$this->min = $min;
		parent::__construct( function ($value) {
				return $value > $this->min;
			}, 
			function ($value) {
				return "'$value' is not greater than '{$this->min}'.";
			},
			$data_factory);
	}
}