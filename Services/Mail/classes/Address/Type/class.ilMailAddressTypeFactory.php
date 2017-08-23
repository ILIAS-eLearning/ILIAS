<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypeFactory
{
	/**
	 * @param ilMailAddress $a_address
	 * @return ilMailAddressType
	 */
	public static function getByPrefix(ilMailAddress $a_address)
	{
		switch(true)
		{
			case substr($a_address->getMailbox(), 0, 1) != '#' && substr($a_address->getMailbox(), 0, 2) != '"#':
				require_once 'Services/Mail/classes/Address/Type/class.ilMailLoginOrEmailAddressAddressType.php';
				return new ilMailLoginOrEmailAddressAddressType($a_address);
				break;

			case substr($a_address->getMailbox(), 0, 7) == '#il_ml_':
				require_once 'Services/Mail/classes/Address/Type/class.ilMailMailingListAddressType.php';
				return new ilMailMailingListAddressType($a_address);
				break;

			case (
					ilUtil::groupNameExists(substr($a_address->getMailbox(), 1)) && 
					(
						$a_address->getHost() == ilMail::ILIAS_HOST ||
						0 === strlen($a_address->getHost())
					)
				):
				require_once 'Services/Mail/classes/Address/Type/class.ilMailGroupAddressType.php';
				return new ilMailGroupAddressType($a_address);
				break;
			
			default:
				require_once 'Services/Mail/classes/Address/Type/class.ilMailRoleAddressType.php';
				return new ilMailRoleAddressType($a_address);
				break;
		}
	}
}