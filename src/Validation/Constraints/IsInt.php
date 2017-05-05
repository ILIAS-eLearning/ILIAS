<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class IsInt extends Custom implements Constraint {
	const ERROR_MESSAGE = "The checked value is not an integer";

	public function __construct(Data\Factory $data_factory) {
		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		return is_int($value);
	}

	/**
	 * Get the problem message
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		if($this->error !== null) {
			return call_user_func($this->error);
		}

		return self::ERROR_MESSAGE;
	}
}