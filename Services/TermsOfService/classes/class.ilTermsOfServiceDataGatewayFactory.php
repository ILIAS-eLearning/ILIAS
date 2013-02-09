<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceDataGatewayFactory implements ilTermsOfServiceFactory
{
	/**
	 * @param string $name
	 * @return ilTermsOfServiceAcceptanceDataGateway|ilTermsOfServiceAcceptanceDatabaseGateway
	 * @throws InvalidArgumentException
	 */
	public function getByName($name)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		switch(strtolower($name))
		{
			case 'iltermsofserviceacceptancedatabasegateway':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceDatabaseGateway.php';
				return new ilTermsOfServiceAcceptanceDatabaseGateway($ilDB);

			default:
				throw new InvalidArgumentException('Data gateway not supported');
		}
	}
}
