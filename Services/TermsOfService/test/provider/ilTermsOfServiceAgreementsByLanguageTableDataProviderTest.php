<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;

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
		if(!defined('CLIENT_ID'))
		{
			define('CLIENT_ID', 'phpunit');
		}

		vfs\vfsStreamWrapper::register();

		parent::setUp();
	}

	/**
	 * @return ilTermsOfServiceAgreementByLanguageProvider
	 */
	public function testAgreementByLanguageProviderCanBeCreatedByFactory()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';
		$factory = new ilTermsOfServiceTableDataProviderFactory();
		$factory->setLanguageAdapter($this->getMockBuilder('ilLanguage')->setMethods(array('toJSON', 'getInstalledLanguages'))->disableOriginalConstructor()->getMock());
		$provider = $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE);

		$this->assertInstanceOf('ilTermsOfServiceAgreementByLanguageProvider', $provider);
		$this->assertInstanceOf('ilTermsOfServiceTableDataProvider', $provider);

		return $provider;
	}

	/**
	 * @param ilTermsOfServiceAgreementByLanguageProvider $provider
	 * @depends testAgreementByLanguageProviderCanBeCreatedByFactory
	 */
	public function testProviderReturnsAResultForEveryInstalledLanguage(ilTermsOfServiceAgreementByLanguageProvider $provider)
	{
		$client_rel_path = implode('/', array('clients', 'default', 'agreement'));
		$global_rel_path = implode('/', array('global', 'agreement'));

		$root = vfs\vfsStreamWrapper::setRoot(new vfs\vfsStreamDirectory('root'));
		$customizing_dir = vfs\vfsStream::newDirectory('Customizing')->at($root);

		$client_dir = vfs\vfsStream::newDirectory($client_rel_path)->at($customizing_dir);
		vfs\vfsStream::newFile('agreement_de.html', 0777)->at($client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/' . $client_rel_path . '/agreement_de.html'), 'phpunit');

		$global_dir = vfs\vfsStream::newDirectory($global_rel_path)->at($customizing_dir);
		vfs\vfsStream::newFile('agreement_en.html', 0777)->at($global_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/' . $global_rel_path . '/agreement_en.html'), 'phpunit');

		$provider->setSourceDirectories(array(
			vfs\vfsStream::url('root/Customizing/' . $client_rel_path),
			vfs\vfsStream::url('root/Customizing/' . $global_rel_path)
		));

		$lng                 = $this->getMockBuilder('ilLanguage')->setMethods(array('toJSON', 'getInstalledLanguages'))->disableOriginalConstructor()->getMock();
		$installed_languages = array('en', 'de', 'fr');
		$lng->expects($this->once())->method('getInstalledLanguages')->will($this->onConsecutiveCalls($installed_languages));
		$provider->setLanguageAdapter($lng);

		$data = $provider->getList(array(), array());
		$this->assertArrayHasKey('items', $data);
		$this->assertArrayHasKey('cnt', $data);
		$this->assertCount(count($installed_languages), $data['items']);
		$this->assertEquals(count($installed_languages), $data['cnt']);

		for($i = 0; $i < count($installed_languages); $i++)
		{
			$this->assertArrayHasKey('language', $data['items'][$i]);
			$this->assertArrayHasKey('agreement', $data['items'][$i]);
			$this->assertArrayHasKey('agreement_document', $data['items'][$i]);
			$this->assertArrayHasKey('agreement_document_modification_ts', $data['items'][$i]);

			if($installed_languages[$i] == 'fr')
			{
				$this->assertFalse(file_exists($data['items'][$i]['agreement_document']));
			}
			else
			{
				$this->assertTrue(file_exists($data['items'][$i]['agreement_document']));
			}
		}
	}

	/**
	 * @param ilTermsOfServiceAgreementByLanguageProvider $provider
	 * @depends           testAgreementByLanguageProviderCanBeCreatedByFactory
	 */
	public function testProviderShouldReturnLanguageAdapterWhenLanguageAdapterIsSet(ilTermsOfServiceAgreementByLanguageProvider $provider)
	{
		$expected = $this->getMockBuilder('ilLanguage')->setMethods(array('toJSON', 'getInstalledLanguages'))->disableOriginalConstructor()->getMock();

		$provider->setLanguageAdapter($expected);
		$this->assertEquals($expected, $provider->getLanguageAdapter());
	}

	/**
	 * @param ilTermsOfServiceAgreementByLanguageProvider $provider
	 * @depends           testAgreementByLanguageProviderCanBeCreatedByFactory
	 */
	public function testProviderShouldReturnSourceDirectoriesWhenSourceDirectoriesAreSet(ilTermsOfServiceAgreementByLanguageProvider $provider)
	{
		$expected = array('/phpunit', '/ilias');

		$provider->setSourceDirectories($expected);
		$this->assertEquals($expected, $provider->getSourceDirectories());
	}
}
