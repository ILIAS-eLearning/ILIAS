<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;

/**
 * Class LogicalOr
 * @package ILIAS\Validation\Constraints
 * @author  Michael Jansen <mjansen@databay.de>
 */
class LogicalOr extends Custom implements Constraint
{
	/**
	 * @var Constraint[]
	 */
	protected $other = [];

	/**
	 * @var Constraint[]
	 */
	protected $failedOthers = [];

	/**
	 * LogicalOr constructor.
	 * @param Constraint[] $other
	 * @param Data\Factory $data_factory
	 */
	public function __construct(array $other, Data\Factory $data_factory)
	{
		$this->other = $other;

		parent::__construct(
			function ($value) {
				$return = false;

				foreach ($this->other as $constraint) {
					if ($constraint->accepts($value)) {
						$return = true;
					} else {
						$this->failedOthers[] = $constraint;
					}
				}

				return $return;
			},
			function ($value) {
				$problems = [];

				foreach ($this->failedOthers as $constraint) {
					$problems[] = (string)$constraint->problemWith($value);
				}

				return implode('', array_filter($problems));
			},
			$data_factory
		);
	}
}