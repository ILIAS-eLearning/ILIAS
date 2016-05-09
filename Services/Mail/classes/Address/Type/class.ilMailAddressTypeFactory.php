<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypeFactory
{
	/**
	 * @param stdClass $address
	 * @return ilMailAddressType
	 */
	public static function getByPrefix(stdClass $address)
	{
		switch(true)
		{
			case substr($address->mailbox, 0, 1) != '#' && substr($address->mailbox, 0, 2) != '"#':
				require_once 'Services/Mail/classes/Address/Type/class.ilMailLoginOrEmailAddressAddressType.php';
				return new ilMailLoginOrEmailAddressAddressType($address);
				break;

			case substr($address->mailbox, 0, 7) == '#il_ml_':
				require_once 'Services/Mail/classes/Address/Type/class.ilMailMailingListAddressType.php';
				return new ilMailMailingListAddressType($address);
				break;

			case ilUtil::groupNameExists(substr($address->mailbox, 1)):
				require_once 'Services/Mail/classes/Address/Type/class.ilMailGroupAddressType.php';
				return new ilMailGroupAddressType($address);
				break;
			
			default:
				require_once 'Services/Mail/classes/Address/Type/class.ilMailRoleAddressType.php';
				return new ilMailRoleAddressType($address);
				break;
		}
	}
}