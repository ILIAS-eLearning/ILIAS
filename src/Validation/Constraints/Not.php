<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;

class Not extends Custom implements Constraint {
	/**
	 * @var Constraint
	 */
	protected $constraint;

	public function __construct(Constraint $constraint, Factory $data_factory) {
		$this->constraint = $constraint;
		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		return !$this->constraint->accepts($value);
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

		return $this->constraint->getErrorMessage();
	}
}