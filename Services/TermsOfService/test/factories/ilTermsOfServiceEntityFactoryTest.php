<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceEntityFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceDataGatewayFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceEntityFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 *
	 */
	public function setUp()
	{
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new ilTermsOfServiceEntityFactory();
		$this->assertInstanceOf('ilTermsOfServiceEntityFactory', $factory);
	}

	/**
	 * @expectedException ilTermsOfServiceMissingDataGatewayFactoryException
	 */
	public function testExceptionIsRaisedWhenEntityIsRequestedWithoutDataGatewayConfiguration()
	{
		$factory = new ilTermsOfServiceEntityFactory();
		$factory->getByName('PHP Unit');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenEntityIsRequestedWithUnsupportedName()
	{
		$factory = new ilTermsOfServiceEntityFactory();
		$factory->setDataGatewayFactory($this->getMock('ilTermsOfServiceDataGatewayFactory'));
		$factory->getByName('PHP Unit');
	}

	/**
	 *
	 */
	public function testAcceptanceEntityIsReturnedWhenEntityIsRequestedByName()
	{
		$data_gateway_factory = $this->getMock('ilTermsOfServiceDataGatewayFactory');
		$data_gateway_factory->expects($this->once())->method('getByName')->will($this->returnValue($this->getMock('ilTermsOfServiceAcceptanceDataGateway')));

		$factory = new ilTermsOfServiceEntityFactory();
		$factory->setDataGatewayFactory($data_gateway_factory);
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceEntity', $factory->getByName('ilTermsOfServiceAcceptanceEntity'));
	}
}
