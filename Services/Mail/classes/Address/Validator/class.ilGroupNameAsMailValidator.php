<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilGroupNameAsMailValidator
{
	/** @var string */
	private $host;

	/**
	 * @param string $host
	 */
	public function __construct(string $host)
	{
		$this->host = $host;
	}

	/**
	 * Validates if the given address contains a valid group name to send an email
	 * @param \ilMailAddress $address
	 * @return bool
	 */
	public function validate(\ilMailAddress $address): bool 
	{
		$groupName = substr($address->getMailbox(), 1);

		if (\ilUtil::groupNameExists($groupName) && $this->isHostValid($address->getHost())) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the given host is valid in the email context
	 * @param string $host
	 * @return bool
	 */
	private function isHostValid(string $host): bool 
	{
		return ($host == $this->host || 0 === strlen($host));
	}
}
