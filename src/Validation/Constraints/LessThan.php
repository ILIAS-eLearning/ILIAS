<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class LessThan extends Custom implements Constraint {
	/**
	 * @var int
	 */
	protected $max;

	public function __construct($max, Data\Factory $data_factory) {
		assert(is_int($max));
		$this->max = $max;
		parent::__construct( function ($value) {
				return $value < $this->max;
			}, 
			function ($value) {
				return "'$value' is greater than '{$this->max}'.";
			},
			$data_factory);
	}
}