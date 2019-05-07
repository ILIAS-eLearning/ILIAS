<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data\Interval;

use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

class ClosedFloatInterval
{
	/**
	 * @var float
	 */
	private $range;

	/**
	 * @param $minimum
	 * @param $maximum
	 * @throws ConstraintViolationException
	 */
	public function __construct($minimum, $maximum)
	{
		if ($maximum === $minimum) {
			throw new ConstraintViolationException(
				sprintf('The maximum("%s") and minimum("%s") can NOT be the same', $maximum, $minimum),
				'exception_maximum_minimum_same',
				$maximum,
				$minimum
			);
		}

		$this->range = new OpenedFloatInterval($minimum, $maximum);
	}

	/**
	 * @param float $numberToCheck
	 * @return bool
	 */
	public function spans(float $numberToCheck) : bool
	{
		if ($numberToCheck <= $this->range->minimum()) {
			return false;
		} elseif ($numberToCheck >= $this->range->maximum()) {
			return false;
		}

		return true;
	}

	/**
	 * @return float
	 */
	public function minimum() : float
	{
		return $this->range->minimum();
	}

	/**
	 * @return float
	 */
	public function maximum() : float
	{
		return $this->range->maximum();
	}
}
