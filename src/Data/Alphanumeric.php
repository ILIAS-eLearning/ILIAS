<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;


class Alphanumeric
{
	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @param $value
	 * @throws \InvalidArgumentException
	 */
	public function __construct($value)
	{
		$matches = null;
		if (!preg_match('/^[a-zA-Z0-9]+$/', (string) $value, $matches)) {
			throw new \InvalidArgumentException(sprintf('The value "%s" is not an alphanumeric value.', $value));
		}

		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function asString() : string
	{
		return (string) $this->value;
	}
}
