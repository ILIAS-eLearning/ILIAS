<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;


class PositiveInteger
{
	/**
	 * @var int
	 */
	private $value;

	/**
	 * @param int $value
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $value)
	{
		$matches = null;

		if ($value < 0) {
			throw new \InvalidArgumentException(sprintf('The value "%s" is not a positive integer', $value));
		}

		$this->value = $value;
	}

	/**
	 * @return int
	 */
	public function getValue() : int
	{
		return (int) $this->value;
	}
}
