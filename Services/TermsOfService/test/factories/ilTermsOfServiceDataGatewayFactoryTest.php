<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDataGatewayFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactoryTest extends \ilTermsOfServiceBaseTest
{
	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$this->assertInstanceOf('ilTermsOfServiceDataGatewayFactory', $factory);
	}

	/**
	 * 
	 */
	public function testExceptionIsRaisedWhenGatewayIsRequestedWithMissingDependencies()
	{
		$this->expectException(\ilTermsOfServiceMissingDatabaseAdapterException::class);

		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->getByName('PHP Unit');
	}

	/**
	 * 
	 */
	public function testExceptionIsRaisedWhenUnknownDataGatewayIsRequested()
	{
		$this->expectException(\InvalidArgumentException::class);

		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($this->getMockBuilder(\ilDBInterface::class)->getMock());
		$factory->getByName('PHP Unit');
	}

	/**
	 *
	 */
	public function testAcceptanceDatabaseGatewayIsReturnedWhenRequestedByName()
	{
		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($this->getMockBuilder(\ilDBInterface::class)->getMock());

		$this->assertInstanceOf(
			'ilTermsOfServiceAcceptanceDatabaseGateway',
			$factory->getByName('ilTermsOfServiceAcceptanceDatabaseGateway')
		);
	}

	/**
	 *
	 */
	public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet()
	{
		$expected = $this->getMockBuilder(\ilDBInterface::class)->getMock();

		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->setDatabaseAdapter($expected);

		$this->assertEquals($expected, $factory->getDatabaseAdapter());
	}
}