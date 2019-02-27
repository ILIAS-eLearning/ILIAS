<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;


use phpDocumentor\Reflection\Types\Integer;

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
			throw new \InvalidArgumentException(sprintf('The minimum value("%s") is not a integer', $minimum));
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
