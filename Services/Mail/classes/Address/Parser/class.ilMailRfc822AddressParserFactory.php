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
	public static function getParser($a_address)
	{
		switch(true)
		{
			// imap_rfc822_parse_adrlist currently not used because we cannot determine which of the addresses in the recipient string is faulty
//			case function_exists('imap_rfc822_parse_adrlist'):
//				require_once 'Services/Mail/classes/Address/Parser/class.ilMailImapRfc822AddressParser.php';
//				return new ilMailImapRfc822AddressParser($a_address);
//				break;

			default:
				require_once 'Services/Mail/classes/Address/Parser/class.ilMailPearRfc822WrapperAddressParser.php';
				require_once 'Services/Mail/classes/Address/Parser/class.ilMailRfc822AddressParser.php';
				return new ilMailRfc822AddressParser(new ilMailPearRfc822WrapperAddressParser($a_address));
				break;
		}
	}
}