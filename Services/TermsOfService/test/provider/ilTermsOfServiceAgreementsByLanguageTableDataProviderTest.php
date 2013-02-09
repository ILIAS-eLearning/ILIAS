<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAgreementsByLanguageTableDataProviderTest extends PHPUnit_Framework_TestCase
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
	 * @return ilTermsOfServiceAgreementByLanguageProvider
	 */
	public function testAgreementByLanguageProviderCanBeCreatedByFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';
		$factory = new ilTermsOfServiceTableDataProviderFactory();
		$factory->setLanguageAdapter($this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock());
		$provider = $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE);

		$this->assertInstanceOf('ilTermsOfServiceAgreementByLanguageProvider', $provider);
		$this->assertInstanceOf('ilTermsOfServiceTableDataProvider', $provider);

		return $provider;
	}

	/**
	 * @param ilTermsOfServiceAgreementByLanguageProvider $provider
	 * @depends testAgreementByLanguageProviderCanBeCreatedByFactory
	 * @expectedException ilException
	 */
	public function testExceptionIsRaisedWhenListShouldBeRetrievedWithMissingLanguageAdapter(ilTermsOfServiceAgreementByLanguageProvider $provider)
	{
		$provider->setLanguageAdapter(null);
		$provider->getList(array(), array());
	}

	/**
	 * @param ilTermsOfServiceAgreementByLanguageProvider $provider
	 * @depends testAgreementByLanguageProviderCanBeCreatedByFactory
	 */
	public function testProviderReturnsAResultForEveryInstalledLanguage(ilTermsOfServiceAgreementByLanguageProvider $provider)
	{
		$lng = $this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock();

		$installed_languages = array('en', 'de', 'fr', 'es');

		$lng->expects($this->once())->method('getInstalledLanguages')->will($this->onConsecutiveCalls($installed_languages));
		$provider->setLanguageAdapter($lng);

		$data = $provider->getList(array(), array());
		$this->assertCount(count($installed_languages), $data['items']);
		$this->assertEquals(count($installed_languages), $data['cnt']);
		$this->assertArrayHasKey('language', $data['items'][0]);
		$this->assertArrayHasKey('agreement', $data['items'][0]);
		$this->assertArrayHasKey('agreement_document', $data['items'][0]);
		$this->assertArrayHasKey('agreement_document_modification_ts', $data['items'][0]);
	}

	/**
	 * @param ilTermsOfServiceAgreementByLanguageProvider $provider
	 * @depends           testAgreementByLanguageProviderCanBeCreatedByFactory
	 */
	public function testFactoryShouldReturnLanguageAdapterWhenLanguageAdapterIsSet(ilTermsOfServiceAgreementByLanguageProvider $provider)
	{
		$lng = $this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock();
		$provider->setLanguageAdapter($lng);
		$this->assertEquals($lng, $provider->getLanguageAdapter());
	}
}
