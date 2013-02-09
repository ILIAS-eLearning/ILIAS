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
	 * @param ilTermsOfServiceAcceptanceRequest $request
	 * @throws InvalidArgumentException
	 */
	public function invoke(ilTermsOfServiceRequest $request)
	{
		$entity = $request->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');

		$document = $request->getDocument();

		$entity->setSource($document->getSource());
		$entity->setSourceType($document->getSourceType());
		$entity->setSignedText($document->getContent());
		$entity->setIso2LanguageCode($document->getIso2LanguageCode());
		$entity->setUserId($request->getUserId());
		$entity->setTimestamp($request->getTimestamp());
		$entity->setHash(md5($entity->getSignedText()));
		$entity->save();
	}
}
