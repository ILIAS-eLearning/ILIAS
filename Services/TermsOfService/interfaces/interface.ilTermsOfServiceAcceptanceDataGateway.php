<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceAcceptanceDataGateway
{
	/**
	 * @abstract
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 */
	public function save(ilTermsOfServiceAcceptanceEntity $entity);

	/**
	 * @abstract
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public function loadCurrentOfUser(ilTermsOfServiceAcceptanceEntity $entity);
}
