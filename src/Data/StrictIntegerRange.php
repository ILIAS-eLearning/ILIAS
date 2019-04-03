<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;



class StrictIntegerRange
{
	/**
	 * @var Integer
	 */
	private $integerRange;

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

		$this->integerRange = new IntegerRange($minimum, $maximum);
	}

	/**
	 * @return int
	 */
	public function minimumAsInteger() : int
	{
		return $this->integerRange->minimumAsInteger();
	}

	/**
	 * @return int
	 */
	public function maximumAsInteger() : int
	{
		return $this->integerRange->maximumAsInteger();
	}
}
