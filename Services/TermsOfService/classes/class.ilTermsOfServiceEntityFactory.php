<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceEntityFactory implements ilTermsOfServiceFactory
{
	/**
	 * @var ilTermsOfServiceDataGatewayFactory
	 */
	protected $data_gateway_factory;

	/**
	 * @param ilTermsOfServiceInteractor $interactor
	 * @return ilTermsOfServiceAcceptanceEntity
	 * @throws InvalidArgumentException
	 */
	public function getByName($name)
	{
		if(null === $this->data_gateway_factory)
		{
			require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingDataGatewayFactoryException.php';
			throw new ilTermsOfServiceMissingDataGatewayFactoryException('Incomplete factory configuration. Please inject a data gateway factory.');
		}

		switch(strtolower($name))
		{
			case 'iltermsofserviceacceptanceentity':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';
				$entity = new ilTermsOfServiceAcceptanceEntity();
				$entity->setDataGateway($this->data_gateway_factory->getByName('ilTermsOfServiceAcceptanceDatabaseGateway'));
				return $entity;

			default:
				throw new InvalidArgumentException('Entity not supported');
		}
	}

	/**
	 * @param ilTermsOfServiceDataGatewayFactory $data_gateway_factory
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
}
