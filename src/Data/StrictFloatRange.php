<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;


class StrictFloatRange
{
	/**
	 * @var FloatRange
	 */
	private $floatRange;

	/**
	 * @param $minimum
	 * @param $maximum
	 * @throws \InvalidArgumentException
	 */
	public function __construct($minimum, $maximum)
	{
		if ($maximum === $minimum) {
			throw new \InvalidArgumentException(sprintf('The maximum("%s") can NOT be same than the minimum("%s")', $maximum, $minimum));
		}

		$this->floatRange = new FloatRange($minimum, $maximum);
	}

	/**
	 * @return float
	 */
	public function minimumAsFloat() : float
	{
		return $this->floatRange->minimumAsFloat();
	}

	/**
	 * @return float
	 */
	public function maximumAsFloat() : float
	{
		return $this->floatRange->maximumAsFloat();
	}
}
