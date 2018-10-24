<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBaseMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailRfc822AddressParser implements \ilMailRecipientParser
{
	/**
	 * @var string
	 */
	protected $addresses = '';

	/**
	 * @param string $a_addresses A comma separated list of email addresses
	 */
	public function __construct(string $a_addresses)
	{
		$this->addresses = $a_addresses;
	}

	/**
	 * @return string
	 */
	public function getAddresses(): string
	{
		return $this->addresses;
	}

	/**
	 * A comma separated list of email addresses
	 * @param string $addresses
	 */
	public function setAddresses(string $addresses)
	{
		$this->addresses = $addresses;
	}

	/**
	 * @param string $a_addresses A comma separated list of email addresses
	 * @return \ilMailAddress[]
	 */
	protected abstract function parseAddressString(string $a_addresses): array;

	/**
	 * @inheritdoc
	 */
	public function parse(): array
	{
		$addresses = preg_replace('/;/', ',', trim($this->addresses));

		return $this->parseAddressString($addresses);
	}
}