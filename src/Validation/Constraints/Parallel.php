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
	 * There's a test to show this state will never be visible
	 * ParallelTest::testCorrectErrorMessagesAfterMultiAccept
	 *
	 * @var Constraint[]
	 */
	protected $failed_constraints;

	public function __construct(array $constraints, Data\Factory $data_factory) {
		$this->constraints = $constraints;
		parent::__construct(
			function($value) {
				$ret = true;
				$this->failed_constraints = array();
				foreach ($this->constraints as $constraint) {
					if(!$constraint->accepts($value)) {
						$this->failed_constraints[] = $constraint;
						$ret = false;
					}
				}

				return $ret;
			},
			function($value) {
				$message = "";
				foreach ($this->failed_constraints as $key => $constraint) {
					$message .= $constraint->getErrorMessage($value);
				}

				return $message;
			},
			$data_factory
		);
	}
}