<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;
use ILIAS\Refinery\Custom\Constraints\Custom;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;

class Sequential extends Custom implements Constraint {
	/**
	 * @var Constraint[]
	 */
	protected $constraints;

	/**
	 * Theres a test to show this state will never be visible
	 * SequentialTest::testCorrectErrorMessagesAfterMultiAccept
	 *
	 * @var Constraint
	 */
	protected $failed_constraint;

	public function __construct(array $constraints, Data\Factory $data_factory, \ilLanguage $lng) {
		$this->constraints = $constraints;
		parent::__construct(
			function($value) {
				foreach ($this->constraints as $key => $constraint) {
					if(!$constraint->accepts($value)) {
						$this->failed_constraint = $constraint;
						return false;
					}
				}

				return true;
			},
			function($txt, $value) {
				return $this->failed_constraint->getErrorMessage($value);
			},
			$data_factory,
			$lng
		);
	}
}
