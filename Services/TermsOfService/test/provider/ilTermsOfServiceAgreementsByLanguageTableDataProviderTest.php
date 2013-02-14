<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'vfsStream/vfsStream.php';

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
		vfsStreamWrapper::register();
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

		$installed_languages = array('en', 'de');

		$lng->expects($this->once())->method('getInstalledLanguages')->will($this->onConsecutiveCalls($installed_languages));
		$provider->setLanguageAdapter($lng);

		$root = vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
		$global_dir = vfsStream::newDirectory('Customizing/global/agreement')->at($root);
		vfsStream::newFile('agreement_en.html', 0777)->at($global_dir);
		//file_put_contents(vfsStream::url('./Customizing/global/agreement/agreement_en.html'), 'phpunit');
//var_dump(is_file('./Customizing/global/agreement/agreement_en.html'));
		$client_dir = vfsStream::newDirectory('./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_de.html')->at($root);
		vfsStream::newFile('agreement_de.html', 0777)->at($client_dir);
		//file_put_contents(vfsStream::url('./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_de.html'), 'phpunit');
//var_dump(is_file('./Customizing/clients/' . CLIENT_ID . '/agreement/agreement_de.html'));
//exit();
		$data = $provider->getList(array(), array());
		$this->assertArrayHasKey('items', $data);
		$this->assertArrayHasKey('cnt', $data);
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
