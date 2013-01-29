<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceInteractorFactory implements ilTermsOfServiceFactory
{
	/**
	 * @param string $name
	 * @return ilTermsOfServiceInteractor
	 * @throws InvalidArgumentException
	 */
	public function getByName($name)
	{
		switch(strtolower($name))
		{
			case 'iltermsofserviceacceptanceinteractor':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceInteractor.php';
				return new ilTermsOfServiceAcceptanceInteractor();

			case 'iltermsofservicecurrentacceptanceinteractor':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceInteractor.php';
				return new ilTermsOfServiceCurrentAcceptanceInteractor();

			default:
				throw new InvalidArgumentException('Interactor not supported');
		}
	}
}
