<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceAcceptanceDataGateway
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceAcceptanceDataGateway
{
	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 */
	public function trackAcceptance(ilTermsOfServiceAcceptanceEntity $entity);

	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public function loadCurrentAcceptanceOfUser(ilTermsOfServiceAcceptanceEntity $entity);

	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 */
	public function deleteAcceptanceHistoryByUser(ilTermsOfServiceAcceptanceEntity $entity);
}
