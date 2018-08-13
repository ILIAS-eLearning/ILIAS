<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDataGatewayFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactoryTest extends \ilTermsOfServiceBaseTest
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 * @inheritdoc
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
		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$this->assertInstanceOf('ilTermsOfServiceDataGatewayFactory', $factory);
	}

	/**
	 * @expectedException \ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public function testExceptionIsRaisedWhenWhenGatewayIsRequestedWithMissingDependencies()
	{
		$this->assertException(\ilTermsOfServiceMissingDatabaseAdapterException::class);

		$factory = new \ilTermsOfServiceDataGatewayFactory();
		$factory->getByName('PHP Unit');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnknowDataGatewayIsRequested()
	{
		$this->assertException(\InvalidArgumentException::class);

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