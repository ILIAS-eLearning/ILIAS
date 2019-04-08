<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data\Range;

use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

class FloatRange
{
	/**
	 * @var float
	 */
	private $minimum;

	/**
	 * @var float
	 */
	private $maximum;

	/**
	 * @param float $minimum
	 * @param float $maximum
	 * @throws ConstraintViolationException
	 */
	public function __construct(float $minimum, float $maximum)
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
	 * @param float $numberToCheck
	 * @return bool
	 */
	public function spans(float $numberToCheck) : bool
	{
		if ($numberToCheck < $this->minimum) {
			return false;
		} elseif ($numberToCheck > $this->maximum) {
			return false;
		}

		return true;
	}


	/**
	 * @return float
	 */
	public function minimum() : float
	{
		return $this->minimum;
	}

	/**
	 * @return float
	 */
	public function maximum() : float
	{
		return $this->maximum;
	}
}
