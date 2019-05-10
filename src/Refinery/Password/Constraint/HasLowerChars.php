<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password\Constraint;

use ILIAS\Refinery\Custom\Constraints\Custom;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;


class HasLowerChars extends Custom implements Constraint {
	public function __construct(Data\Factory $data_factory, \ilLanguage $lng) {
		parent::__construct( function (Data\Password $value) {
				return (bool) preg_match('/[a-z]/', $value->toString());
			},
			function ($value) {
				return "Password must contain lower-case characters.";
			},
			$data_factory,
			$lng
		);
	}

}
