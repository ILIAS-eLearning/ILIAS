<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;


class FloatRange
{
	private $minimum;
	private $maximum;

	/**
	 * @param float $minimum
	 * @param float $maximum
	 */
	public function __construct(float $minimum, float $maximum)
	{
		if ($maximum === $minimum) {
			throw new \InvalidArgumentException(sprintf('The maximum("%s") can NOT be same than the minimum("%s")', $maximum, $minimum));
		}

		if ($maximum < $minimum) {
			throw new \InvalidArgumentException(sprintf('The maximum("%s") can NOT be lower than the minimum("%s")', $maximum, $minimum));
		}

		$this->minimum = $minimum;
		$this->maximum = $maximum;
	}

	/**
	 * @return float
	 */
	public function minimumAsFloat()
	{
		return $this->minimum;
	}

	/**
	 * @return float
	 */
	public function maximumAsFloat()
	{
		return $this->maximum;
	}
}
