<?php

class GroupNameAsMailValidator
{
	private $host;

	public function __construct($host)
	{
		$this->host = $host;
	}

	public function validate(\ilMailAddress $address)
	{
		$groupName = substr($address->getMailbox(), 1);

		if (ilUtil::groupNameExists($groupName) && $this->isHostValid($address->getHost())) {
			return true;
		}

		return false;
	}

	private function isHostValid($host)
	{
		return ($host == $this->host || 0 === strlen($host));
	}
}
