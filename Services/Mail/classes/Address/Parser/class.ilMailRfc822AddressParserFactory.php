<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailRfc822AddressParserFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRfc822AddressParserFactory
{
	/**
	 * @param string $a_address
	 * @return ilMailRecipientParser
	 */
	public function getParser($a_address)
	{
		require_once 'Services/Mail/classes/Address/Parser/class.ilMailPearRfc822WrapperAddressParser.php';
		require_once 'Services/Mail/classes/Address/Parser/class.ilMailRfc822AddressParser.php';
		return new ilMailRfc822AddressParser(new ilMailPearRfc822WrapperAddressParser($a_address));
	}
}