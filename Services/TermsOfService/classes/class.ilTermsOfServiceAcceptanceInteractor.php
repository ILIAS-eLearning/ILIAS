<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceInteractor.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceInteractor implements ilTermsOfServiceInteractor
{
	/**
	 * @param ilTermsOfServiceRequest $request
	 * @throws InvalidArgumentException
	 */
	public function invoke(ilTermsOfServiceRequest $request)
	{
		if(!is_file($request->getPathToFile()) || !is_readable($request->getPathToFile()))
		{
			throw new InvalidArgumentException("Terms of service {$request->getPathToFile()} does not exists or is not readable.");
		}

		$entity = $request->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$entity->setUserId($request->getUserId());
		$entity->setPathToFile($request->getPathToFile());
		$entity->setSignedText(file_get_contents($request->getPathToFile()));
		$entity->setTimestamp($request->getTimestamp());
		$entity->setHash(md5($entity->getSignedText()));
		$matches = null;
		preg_match('/agreement_([A-Za-z]{2})\.html$/', trim($request->getPathToFile()), $matches);
		if(is_array($matches) && isset($matches[1]))
		{
			$entity->setLanguage($matches[1]);
		}
		$entity->save();
	}
}
