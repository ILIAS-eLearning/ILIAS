<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data\Interval;

use ILIAS\Refinery\ConstraintViolationException;

class ClosedIntegerInterval
{
	/**
	 * @var OpenedIntegerInterval
	 */
	private $range;

	/**
	 * @param int $minimum
	 * @param int $maximum
	 * @throws ConstraintViolationException
	 */
	public function __construct(int $minimum, int $maximum)
	{
		if ($minimum === $maximum) {
			throw new ConstraintViolationException(
				sprintf('The maximum("%s") and minimum("%s") can NOT be the same', $maximum, $minimum),
				'exception_maximum_minimum_same',
				$maximum,
				$minimum
			);
		}

		$this->range = new OpenedIntegerInterval($minimum, $maximum);
	}

	/**
	 * @param int $numberToCheck
	 * @return bool
	 */
	public function spans(int $numberToCheck) : bool
	{
		if ($numberToCheck <= $this->range->minimum()) {
			return false;
		} elseif ($numberToCheck >= $this->range->maximum()) {
			return false;
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function minimum() : int
	{
		return $this->range->minimum();
	}

	/**
	 * @return int
	 */
	public function maximum() : int
	{
		return $this->range->maximum();
	}
}
