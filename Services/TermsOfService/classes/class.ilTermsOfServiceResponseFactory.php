<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceResponseFactory implements ilTermsOfServiceFactory
{
	/**
	 * @param string $name
	 * @return ilTermsOfServiceResponse
	 * @throws InvalidArgumentException
	 */
	public function getByName($name)
	{
		switch(strtolower($name))
		{
			case 'iltermsofservicecurrentacceptanceresponse':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceResponse.php';
				return new ilTermsOfServiceCurrentAcceptanceResponse();

			default:
				throw new InvalidArgumentException('Response not supported');
		}
	}
}
