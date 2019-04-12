<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * Class ClientId
 * @package ILIAS\Data
 * @author Michael Jansen <mjansen@databay.de>
 */
class ClientId
{
	/**
	 * @var string
	 */
	private $clientId = '';

	/**
	 * ClientId constructor.
	 * @param string $clientId
	 */
	public function __construct($clientId)
	{
		if (!is_string($clientId)) {
			throw new \InvalidArgumentException('Invalid value for $clientId');
		}

		if (preg_match('/[^A-Za-z0-9#_\.\-]/', $clientId)) {
			throw new \InvalidArgumentException('Invalid value for $clientId');
		}

		$this->clientId = $clientId;
	}

	/**
	 * @return string
	 */
	public function toString()
	{
		return $this->clientId;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}
}