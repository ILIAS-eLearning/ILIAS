<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data\Range;


class StrictIntegerRange
{
	/**
	 * @var IntegerRange
	 */
	private $range;

	/**
	 * @param int $minimum
	 * @param int $maximum
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $minimum, int $maximum)
	{
		if ($minimum === $maximum) {
			throw new \InvalidArgumentException(sprintf('The maximum("%s") can NOT be same than the minimum("%s")', $maximum, $minimum));
		}

		$this->range = new IntegerRange($minimum, $maximum);
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
