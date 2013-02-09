<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceTableDataProviderFactoryTest extends PHPUnit_Framework_TestCase
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
		require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
		ilUnitUtil::performInitialisation();
	}

	/**
	 * @return ilTermsOfServiceTableDataProviderFactory
	 */
	public function testInstanceCanBeCreated()
	{
		$factory = new ilTermsOfServiceTableDataProviderFactory();
		$this->assertInstanceOf('ilTermsOfServiceTableDataProviderFactory', $factory);
		return $factory;
	}

	/**
	 * @depends           testInstanceCanBeCreated
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnsupportedProviderIsRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->getByContext('PHP unit');
	}

	/**
	 * @param ilTermsOfServiceAcceptanceRequest $entity
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnLanguageAdapterWhenLanguageAdapterIsSet(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$lng = $this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock();
		$factory->setLanguageAdapter($lng);
		$this->assertEquals($lng, $factory->getLanguageAdapter());
	}

	/**
	 * @param ilTermsOfServiceAcceptanceRequest $entity
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$db = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$factory->setDatabaseAdapter($db);
		$this->assertEquals($db, $factory->getDatabaseAdapter());
	}

	/**
	 * @depends           testInstanceCanBeCreated
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @expectedException ilTermsOfServiceMissingLanguageAdapterException
	 */
	public function testExceptionIsRaisedWhenAgreementByLanguageProviderIsRequestedWithoutCompleteFactoryConfiguration(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->setLanguageAdapter(null);
		$factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE);
	}

	/**
	 * @depends           testInstanceCanBeCreated
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @expectedException ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public function testExceptionIsRaisedWhenAcceptanceHistoryProviderIsRequestedWithoutCompleteFactoryConfiguration(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->setDatabaseAdapter(null);
		$factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);
	}

	/**
	 * @param ilTermsOfServiceAcceptanceRequest $entity
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnAgreementByLanguageProviderWhenRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$lng = $this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock();
		$factory->setLanguageAdapter($lng);
		$this->assertInstanceOf('ilTermsOfServiceAgreementByLanguageProvider', $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE));
	}

	/**
	 * @param ilTermsOfServiceAcceptanceRequest $entity
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnAcceptanceHistoryProviderWhenRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$db = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$factory->setDatabaseAdapter($db);
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceHistoryProvider', $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY));
	}
}
