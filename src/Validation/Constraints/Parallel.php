<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class Parallel extends Custom implements Constraint {
	/**
	 * @var Constraint[]
	 */
	protected $constraints;

	/**
	 * @var Constraint[]
	 */
	protected $failed_constraints = array();

	public function __construct(array $constraints, Data\Factory $data_factory) {
		$this->min = $min;
		$this->data_factory = $data_factory;
		$this->constraints = $constraints;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		$ret = true;
		$this->failed_constraints = array();
		foreach ($this->constraints as $constraint) {
			if(!$constraint->accepts($value)) {
				$this->failed_constraints[] = $constraint;
				$ret = false;
			}
		}

		return $ret;
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

		$message = "";
		foreach ($this->failed_constraints as $key => $constraint) {
			$message .= $constraint->getErrorMessage();
		}

		return $message;
	}
}