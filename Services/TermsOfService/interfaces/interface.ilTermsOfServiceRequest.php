<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceRequest
{
	/**
	 * @abstract
	 * @param ilTermsOfServiceEntityFactory $entity_factory
	 */
	public function setEntityFactory(ilTermsOfServiceEntityFactory $entity_factory);

	/**
	 * @abstract
	 * @return ilTermsOfServiceEntityFactory
	 */
	public function getEntityFactory();

	/**
	 * @abstract
	 * @param  $data_gateway_factory
	 */
	public function setDataGatewayFactory(ilTermsOfServiceDataGatewayFactory $data_gateway_factory);

	/**
	 * @abstract
	 * @return ilTermsOfServiceDataGatewayFactory
	 */
	public function getDataGatewayFactory();

	/**
	 * @abstract
	 * @param ilTermsOfServiceInteractorFactory $interactor_factory
	 */
	public function setInteractorFactory(ilTermsOfServiceInteractorFactory $interactor_factory);

	/**
	 * @abstract
	 * @return ilTermsOfServiceInteractorFactory
	 */
	public function getInteractorFactory();

	/**
	 * @abstract
	 * @param ilTermsOfServiceResponseFactory $response_factory
	 */
	public function setResponseFactory(ilTermsOfServiceResponseFactory $response_factory);

	/**
	 * @abstract
	 * @return ilTermsOfServiceResponseFactory
	 */
	public function getResponseFactory();
}
