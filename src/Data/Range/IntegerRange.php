<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data\Range;

use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

class IntegerRange
{
	/**
	 * @var int
	 */
	private $minimum;

	/**
	 * @var int
	 */
	private $maximum;

	/**
	 * @param int $minimum
	 * @param int $maximum
	 * @throws ConstraintViolationException
	 */
	public function __construct(int $minimum, int $maximum)
	{
		if ($maximum < $minimum) {
			throw new ConstraintViolationException(
				sprintf('The maximum("%s") can NOT be lower than the minimum("%s")', $maximum, $minimum),
				'exception_maximum_minimum_mismatch',
				$maximum,
				$minimum
			);
		}

		$this->minimum = $minimum;
		$this->maximum = $maximum;
	}

	/**
	 * @param int $numberToCheck
	 * @return bool
	 */
	public function spans(int $numberToCheck) : bool
	{
		if ($numberToCheck < $this->minimum) {
			return false;
		} elseif ($numberToCheck > $this->maximum) {
			return false;
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function minimum() : int
	{
		return $this->minimum;
	}

	/**
	 * @return int
	 */
	public function maximum() : int
	{
		return $this->maximum;
	}
}
