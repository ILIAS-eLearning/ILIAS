<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Parser/class.ilBaseMailRfc822AddressParser.php';

/**
 * Class ilPearMailRfc822WrapperAddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailPearRfc822WrapperAddressParser extends ilBaseMailRfc822AddressParser
{
	/**
	 * {@inheritdoc}
	 */
	public function parseAddressString($a_addresses)
	{
		if(strlen($a_addresses) == 0)
		{
			return array();
		}

		require_once 'Services/Mail/classes/class.ilMail.php';
		require_once 'Services/Mail/classes/Address/Parser/RFC822.php';
		$parser = new Mail_RFC822();
		$parsed_addresses = $parser->parseAddressList($a_addresses, ilMail::ILIAS_HOST, false, true);

		require_once 'Services/Mail/classes/Address/class.ilMailAddress.php';
		return array_map(function($address) {
			return new ilMailAddress($address->mailbox, $address->host);
		}, $parsed_addresses);
	}
}