<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailOnlyExternalAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOnlyExternalAddressList implements \ilMailAddressList
{
	/** @var \ilMailAddressList */
	protected $origin;

	/** @var string */
	protected $installationHost;
	
	/** @var callable */
	protected $getUsrIdByLoginCallable;

	/**
	 * ilMailOnlyExternalAddressList constructor.
	 * @param \ilMailAddressList $origin
	 * @param string $installationHost
	 * @param callable $getUsrIdByLoginCallable A callable which accepts a string as argument and returns an integer >= 0
	 */
	public function __construct(
		\ilMailAddressList $origin,
		string $installationHost,
		callable $getUsrIdByLoginCallable
	) {
		$this->origin = $origin;
		$this->installationHost = $installationHost;
		$this->getUsrIdByLoginCallable = $getUsrIdByLoginCallable;
	}

	/**
	 * @inheritdoc
	 */
	public function value(): array
	{
		$addresses = $this->origin->value();

		$filteredAddresses = array_filter($addresses, function(\ilMailAddress $address) {
			$c = $this->getUsrIdByLoginCallable;
			if (!$c((string)$address)) {
				// Fixed mantis bug #5875
				return false;
			}

			if ($address->getHost() === $this->installationHost) {
				return false;
			}

			if('#' === substr($address->getMailbox(), 0, 1)) {
				return false;
			}

			return true;
		});

		return $filteredAddresses;
	}
}