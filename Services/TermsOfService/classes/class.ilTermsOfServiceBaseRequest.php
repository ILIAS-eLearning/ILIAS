<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceRequest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @abstract
 */
abstract class ilTermsOfServiceBaseRequest implements ilTermsOfServiceRequest
{
	/**
	 * @var ilTermsOfServiceEntityFactory
	 */
	protected $entity_factory;

	/**
	 * @var ilTermsOfServiceDataGatewayFactory
	 */
	protected $data_gateway_factory;

	/**
	 * @var ilTermsOfServiceInteractorFactory
	 */
	protected $interactor_factory;

	/**
	 * @var ilTermsOfServiceResponseFactory
	 */
	protected $response_factory;

	/**
	 * @param ilTermsOfServiceEntityFactory $entity_factory
	 */
	public function setEntityFactory(ilTermsOfServiceEntityFactory $entity_factory)
	{
		$this->entity_factory = $entity_factory;
	}

	/**
	 * @return ilTermsOfServiceEntityFactory
	 */
	public function getEntityFactory()
	{
		return $this->entity_factory;
	}

	/**
	 * @param  $data_gateway_factory
	 */
	public function setDataGatewayFactory(ilTermsOfServiceDataGatewayFactory $data_gateway_factory)
	{
		$this->data_gateway_factory = $data_gateway_factory;
	}

	/**
	 * @return ilTermsOfServiceDataGatewayFactory
	 */
	public function getDataGatewayFactory()
	{
		return $this->data_gateway_factory;
	}

	/**
	 * @param ilTermsOfServiceInteractorFactory $interactor_factory
	 */
	public function setInteractorFactory(ilTermsOfServiceInteractorFactory $interactor_factory)
	{
		$this->interactor_factory = $interactor_factory;
	}

	/**
	 * @return ilTermsOfServiceInteractorFactory
	 */
	public function getInteractorFactory()
	{
		return $this->interactor_factory;
	}

	/**
	 * @param ilTermsOfServiceResponseFactory $response_factory
	 */
	public function setResponseFactory(ilTermsOfServiceResponseFactory $response_factory)
	{
		$this->response_factory = $response_factory;
	}

	/**
	 * @return ilTermsOfServiceResponseFactory
	 */
	public function getResponseFactory()
	{
		return $this->response_factory;
	}
}
