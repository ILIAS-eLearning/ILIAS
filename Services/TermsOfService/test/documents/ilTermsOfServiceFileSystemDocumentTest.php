<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceFileSystemDocument.php';
require_once 'Services/TermsOfService/test/ilTermsOfServiceBaseTest.php';

use org\bovigo\vfs;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceFileSystemDocumentTest extends ilTermsOfServiceBaseTest
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 * @var vfs\vfsStreamDirectory
	 */
	protected $client_dir;

	/**
	 * @var vfs\vfsStreamDirectory
	 */
	protected $global_dir;

	/**
	 * @var ilLanguage|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $lng;

	/**
	 * @var array
	 */
	protected $source_files = array();

	/**
	 *
	 */
	public function setUp()
	{
		$this->lng = $this->getMockBuilder('ilLanguage')->setMethods(array('toJSON', 'getInstalledLanguages', 'getLangKey', 'getDefaultLanguage'))->disableOriginalConstructor()->getMock();
		$this->lng->expects($this->any())
				  ->method('getLangKey')
				  ->will($this->returnValue('de'));
		$this->lng->expects($this->any())
				  ->method('getDefaultLanguage')
				  ->will($this->returnValue('fr'));

		vfs\vfsStreamWrapper::register();
		$root             = vfs\vfsStreamWrapper::setRoot(new vfs\vfsStreamDirectory('root'));
		$customizing_dir  = vfs\vfsStream::newDirectory('Customizing')->at($root);
		$this->client_dir = vfs\vfsStream::newDirectory('clients/default/agreement')->at($customizing_dir);
		$this->global_dir = vfs\vfsStream::newDirectory('global/agreement')->at($customizing_dir);
		$this->source_files = array(
			vfs\vfsStream::url(implode('/', array('root', 'Customizing', 'clients', 'default', 'agreement', 'agreement_' . $this->lng->getLangKey() . '.html')))         => $this->lng->getLangKey(),
			vfs\vfsStream::url(implode('/', array('root', 'Customizing', 'clients', 'default', 'agreement', 'agreement_' . $this->lng->getDefaultLanguage() . '.html'))) => $this->lng->getDefaultLanguage(),
			vfs\vfsStream::url(implode('/', array('root', 'Customizing', 'clients', 'default', 'agreement', 'agreement_en.html')))                                       => 'en',
			vfs\vfsStream::url(implode('/', array('root', 'Customizing', 'global', 'agreement', 'agreement_' . $this->lng->getLangKey() . '.html')))                     => $this->lng->getLangKey(),
			vfs\vfsStream::url(implode('/', array('root', 'Customizing', 'global', 'agreement', 'agreement_' . $this->lng->getDefaultLanguage() . '.html')))             => $this->lng->getDefaultLanguage(),
			vfs\vfsStream::url(implode('/', array('root', 'Customizing', 'global', 'agreement', 'agreement_en.html')))                                                   => 'en'
		);
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$document = new ilTermsOfServiceFileSystemDocument($this->lng);
		$this->assertInstanceOf('ilTermsOfServiceSignableDocument', $document);
		$document->setSourceFiles($this->source_files);
		return $document;
	}

	/**
	 * @expectedException ilTermsOfServiceNoSignableDocumentFoundException
	 */
	public function testExceptionIsRaisedWhenNoSingableDocumentCouldBeFoundForCurrentLanguage()
	{
		$this->assertException(ilTermsOfServiceNoSignableDocumentFoundException::class);
		$document = new ilTermsOfServiceFileSystemDocument($this->getMockBuilder('ilLanguage')->setMethods(array('getLangKey', 'getDefaultLanguage', 'toJSON', 'getInstalledLanguages'))->disableOriginalConstructor()->getMock());
		$document->setSourceFiles(array());
		$document->determine();
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testClientDocumentCouldBeRetrievedByCurrentLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfs\vfsStream::newFile('agreement_de.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/clients/default/agreement/agreement_de.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('de', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfs\vfsStream::url('root/Customizing/clients/default/agreement/agreement_de.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testClientDocumentCouldBeRetrievedByDefaultLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfs\vfsStream::newFile('agreement_fr.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/clients/default/agreement/agreement_fr.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('fr', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfs\vfsStream::url('root/Customizing/clients/default/agreement/agreement_fr.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testClientDocumentCouldBeRetrievedByEnglishLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfs\vfsStream::newFile('agreement_en.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/clients/default/agreement/agreement_en.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('en', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfs\vfsStream::url('root/Customizing/clients/default/agreement/agreement_en.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testGlobalDocumentCouldBeRetrievedByCurrentLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfs\vfsStream::newFile('agreement_de.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/global/agreement/agreement_de.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('de', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfs\vfsStream::url('root/Customizing/global/agreement/agreement_de.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testGlobalDocumentCouldBeRetrievedByDefaultLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfs\vfsStream::newFile('agreement_fr.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/global/agreement/agreement_fr.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('fr', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfs\vfsStream::url('root/Customizing/global/agreement/agreement_fr.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testGlobalDocumentCouldBeRetrievedByEnglishLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfs\vfsStream::newFile('agreement_en.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfs\vfsStream::url('root/Customizing/global/agreement/agreement_en.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('en', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfs\vfsStream::url('root/Customizing/global/agreement/agreement_en.html'), $document->getSource());
	}
}