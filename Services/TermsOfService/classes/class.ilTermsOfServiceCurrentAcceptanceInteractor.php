<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceInteractor.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceCurrentAcceptanceInteractor implements ilTermsOfServiceInteractor
{
	/**
	 * @param ilTermsOfServiceRequest $request
	 * @return ilTermsOfServiceResponse
	 */
	public function invoke(ilTermsOfServiceRequest $request)
	{
		$entity = $request->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$entity->setUserId($request->getUserId());

		$response = $request->getResponseFactory()->getByName('ilTermsOfServiceCurrentAcceptanceResponse');

		try
		{
			$entity->loadCurrentOfUser();
			$response->setLanguage($entity->getLanguage());
			$response->setPathToFile($entity->getPathToFile());
			$response->setSignedText($entity->getSignedText());
			$response->setHasCurrentAcceptance(true);
		}
		catch(ilException $e)
		{
		}

		return $response;
	}
}
