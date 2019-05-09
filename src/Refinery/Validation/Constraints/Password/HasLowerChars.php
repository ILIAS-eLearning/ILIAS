<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Validation\Constraints\Password;

use ILIAS\Refinery\Validation\Constraints;
use ILIAS\Refinery\Validation\Constraint;
use ILIAS\Data;


class HasLowerChars extends Constraints\Custom implements Constraint {
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
