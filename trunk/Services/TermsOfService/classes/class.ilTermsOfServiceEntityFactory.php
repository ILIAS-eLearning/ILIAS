<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceEntityFactory
{
	/**
	 * @param string $name
	 * @return ilTermsOfServiceAcceptanceEntity
	 * @throws InvalidArgumentException
	 */
	public function getByName($name)
	{
		switch(strtolower($name))
		{
			case 'iltermsofserviceacceptanceentity':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';
				return new ilTermsOfServiceAcceptanceEntity();

			default:
				throw new InvalidArgumentException('Entity not supported');
		}
	}
}
