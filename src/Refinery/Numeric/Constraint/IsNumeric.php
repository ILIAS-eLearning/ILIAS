<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\Refinery;
use ILIAS\Refinery\Custom\Constraints\Custom;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;

class IsNumeric extends Custom implements Constraint {
	public function __construct(Data\Factory $data_factory, \ilLanguage $lng) {
		parent::__construct( function ($value) {
				return is_numeric($value);
			}, 
			function ($txt, $value) {
				return $txt("not_numeric", $value);
			},
			$data_factory,
			$lng
		);
	}
}
