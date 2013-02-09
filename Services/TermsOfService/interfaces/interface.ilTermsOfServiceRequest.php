<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceRequest
{
	/**
	 * @param ilTermsOfServiceEntityFactory $entity_factory
	 */
	public function setEntityFactory(ilTermsOfServiceEntityFactory $entity_factory);

	/**
	 * @return ilTermsOfServiceEntityFactory
	 */
	public function getEntityFactory();

	/**
	 * @param  $data_gateway_factory
	 */
	public function setDataGatewayFactory(ilTermsOfServiceDataGatewayFactory $data_gateway_factory);

	/**
	 * @return ilTermsOfServiceDataGatewayFactory
	 */
	public function getDataGatewayFactory();

	/**
	 * @param ilTermsOfServiceInteractorFactory $interactor_factory
	 */
	public function setInteractorFactory(ilTermsOfServiceInteractorFactory $interactor_factory);

	/**
	 * @return ilTermsOfServiceInteractorFactory
	 */
	public function getInteractorFactory();
}