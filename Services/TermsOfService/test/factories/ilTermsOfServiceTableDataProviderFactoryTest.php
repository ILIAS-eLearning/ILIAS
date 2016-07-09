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
		if(!defined('CLIENT_ID'))
		{
			define('CLIENT_ID', 'phpunit');
		}

		parent::setUp();
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
	 */
	public function testExceptionIsRaisedWhenUnsupportedProviderIsRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$this->expectException(InvalidArgumentException::class);
		$factory->getByContext('PHP unit');
	}

	/**
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnLanguageAdapterWhenLanguageAdapterIsSet(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$lng = $this->getMockBuilder('ilLanguage')->setMethods(array('toJSON', 'getInstalledLanguages'))->disableOriginalConstructor()->getMock();
		$factory->setLanguageAdapter($lng);
		$this->assertEquals($lng, $factory->getLanguageAdapter());
	}

	/**
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$factory->setDatabaseAdapter($db);
		$this->assertEquals($db, $factory->getDatabaseAdapter());
	}

	/**
	 * @depends           testInstanceCanBeCreated
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 */
	public function testExceptionIsRaisedWhenAgreementByLanguageProviderIsRequestedWithoutCompleteFactoryConfiguration(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$this->expectException(ilTermsOfServiceMissingLanguageAdapterException::class);
		$factory->setLanguageAdapter(null);
		$factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE);
	}

	/**
	 * @depends           testInstanceCanBeCreated
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 */
	public function testExceptionIsRaisedWhenAcceptanceHistoryProviderIsRequestedWithoutCompleteFactoryConfiguration(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);
		$factory->setDatabaseAdapter(null);
		$factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);
	}

	/**
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnAgreementByLanguageProviderWhenRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->setLanguageAdapter($this->getMockBuilder('ilLanguage')->setMethods(array('toJSON', 'getInstalledLanguages'))->disableOriginalConstructor()->getMock());
		$this->assertInstanceOf('ilTermsOfServiceAgreementByLanguageProvider', $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE));
	}

	/**
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @depends           testInstanceCanBeCreated
	 */
	public function testFactoryShouldReturnAcceptanceHistoryProviderWhenRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->setDatabaseAdapter($this->getMockBuilder('ilDBInterface')->getMock());
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceHistoryProvider', $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY));
	}
}
