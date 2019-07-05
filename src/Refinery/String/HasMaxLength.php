<?php
/* Copyright (c) 2017 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\String;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;

class HasMaxLength extends CustomConstraint implements Constraint {
	/**
	 * @var int
	 */
	protected $max_length;

	public function __construct(int $max_length, Data\Factory $data_factory, \ilLanguage $lng) {
		$this->max_length = $max_length;
		parent::__construct( function ($value) {
				return strlen($value) <= $this->max_length;
			},
			function ($txt, $value) {
				return $txt("not_max_length", $this->max_length);
			},
			$data_factory,
			$lng
		);
	}
}
