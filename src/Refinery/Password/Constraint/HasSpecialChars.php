<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password\Constraint;

use ILIAS\Refinery\Custom\Constraint\Custom;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;


class HasSpecialChars extends Custom implements Constraint {

	protected static $ALLOWED_CHARS = '/[,_.\-#\+\*?!%§\(\)\$]/';

	public function __construct(Data\Factory $data_factory, \ilLanguage $lng) {
		parent::__construct( function (Data\Password $value) {
				return (bool) preg_match(static::$ALLOWED_CHARS, $value->toString());
			},
			function ($value) {
				return "Password must contain special chars.";
			},
			$data_factory,
			$lng
		);
	}

}
