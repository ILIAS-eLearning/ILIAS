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
	 * @param ilTermsOfServiceCurrentAcceptanceRequest $request
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public function invoke(ilTermsOfServiceRequest $request)
	{
		$entity = $request->getEntityFactory()->getByName('ilTermsOfServiceAcceptanceEntity');
		$entity->setUserId($request->getUserId());
		$entity->loadCurrentOfUser();

		return $entity;
	}
}
