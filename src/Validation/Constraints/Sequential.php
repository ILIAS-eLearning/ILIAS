<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;

class Sequential extends Custom implements Constraint {
	/**
	 * @var Constraint[]
	 */
	protected $constraints;

	/**
	 * @var Constraint
	 */
	protected $failed_constraint;

	public function __construct(array $constraints, Factory $data_factory) {
		$this->min = $min;
		$this->data_factory = $data_factory;
		$this->constraints = $constraints;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		foreach ($this->constraints as $key => $constraint) {
			if(!$constraint->accepts($value)) {
				$this->failed_constraint = $constraint;
				return false;
			}
		}

		return true;
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

		return $this->failed_constraint->getErrorMessage();
	}
}