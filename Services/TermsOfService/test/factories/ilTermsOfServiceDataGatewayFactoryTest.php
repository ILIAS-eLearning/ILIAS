<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceDataGatewayFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceDataGatewayFactoryTest extends PHPUnit_Framework_TestCase
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
		parent::setUp();
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new ilTermsOfServiceDataGatewayFactory();
		$this->assertInstanceOf('ilTermsOfServiceDataGatewayFactory', $factory);
	}

	/**
	 *
	 */
	public function testExceptionIsRaisedWhenWhenGatewayIsRequestedWithMissingDependencies()
	{
		$this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);
		$factory = new ilTermsOfServiceDataGatewayFactory();
		$factory->getByName('PHP Unit');
	}

	/**
	 *
	 */
	public function testExceptionIsRaisedWhenUnknowDataGatewayIsRequested()
	{
		$this->expectException(InvalidArgumentException::class);
		$factory = new ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($this->getMockBuilder('ilDBInterface')->getMock());
		$factory->getByName('PHP Unit');
	}

	/**
	 *
	 */
	public function testAcceptanceDatabaseGatewayIsReturnedWhenRequestedByName()
	{
		$factory = new ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($this->getMockBuilder('ilDBInterface')->getMock());
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceDatabaseGateway', $factory->getByName('ilTermsOfServiceAcceptanceDatabaseGateway'));
	}

	/**
	 *
	 */
	public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet()
	{
		$expected = $this->getMockBuilder('ilDBInterface')->getMock();

		$factory = new ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($expected);
		$this->assertEquals($expected, $factory->getDatabaseAdapter());
	}
}