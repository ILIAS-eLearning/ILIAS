<?php
/* Copyright (c) 2017 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;

class HasMaxLength extends Custom implements Constraint {
	/**
	 * @var int
	 */
	protected $max_length;

	public function __construct($max_length, Data\Factory $data_factory) {
		assert('is_int($max_length)');
		$this->max_length = $max_length;
		parent::__construct( function ($value) {
			return strlen($value) <= $this->max_length;
		},
			function ($value) {
				return "'$value' has a length more than '{$this->max_length}'.";
			},
			$data_factory);
	}
}
