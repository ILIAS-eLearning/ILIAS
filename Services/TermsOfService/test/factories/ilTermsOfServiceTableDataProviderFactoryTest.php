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
	 * @expectedException ilTermsOfServiceMissingLanguageAdapterException
	 */
	public function testExceptionIsRaisedWhenLanguageAdapterWasNotSetBeforeInstanceDelivery()
	{
		$factory = new ilTermsOfServiceTableDataProviderFactory();
		$factory->getByContext('PHP unit');
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
	 * @expectedException ilTermsOfServiceMissingLanguageAdapterException
	 */
	public function testExceptionIsRaisedWhenConfigurationIsIncompleteAndProviderIsRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->getByContext('PHP unit');
	}

	/**
	 * @depends           testInstanceCanBeCreated
	 * @param ilTermsOfServiceTableDataProviderFactory $factory
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionIsRaisedWhenUnsupportedProviderIsRequested(ilTermsOfServiceTableDataProviderFactory $factory)
	{
		$factory->setLanguageAdapter($this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock());
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
}
