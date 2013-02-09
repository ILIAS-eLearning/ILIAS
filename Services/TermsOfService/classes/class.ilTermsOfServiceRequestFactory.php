<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceRequestFactory implements ilTermsOfServiceFactory
{
	/**
	 * @param string $name
	 * @return ilTermsOfServiceRequest|ilTermsOfServiceAcceptanceRequest|ilTermsOfServiceCurrentAcceptanceRequest
	 * @throws InvalidArgumentException
	 */
	public function getByName($name)
	{
		switch(strtolower($name))
		{
			case 'iltermsofserviceacceptancerequest':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceRequest.php';
				return new ilTermsOfServiceAcceptanceRequest();

			case 'iltermsofservicecurrentacceptancerequest':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceRequest.php';
				return new ilTermsOfServiceCurrentAcceptanceRequest();

			default:
				throw new InvalidArgumentException('Request not supported');
		}
	}
}
