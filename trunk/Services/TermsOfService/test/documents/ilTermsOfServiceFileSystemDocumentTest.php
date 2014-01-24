<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'vfsStream/vfsStream.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceFileSystemDocument.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceFileSystemDocumentTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 * @var vfsStreamDirectory
	 */
	protected $client_dir;

	/**
	 * @var vfsStreamDirectory
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
		vfsStreamWrapper::register();
		$root             = vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
		$customizing_dir  = vfsStream::newDirectory('Customizing')->at($root);
		$this->client_dir = vfsStream::newDirectory('clients/default/agreement')->at($customizing_dir);
		$this->global_dir = vfsStream::newDirectory('global/agreement')->at($customizing_dir);

		$this->lng = $this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock();
		$this->lng->expects($this->any())
			->method('getLangKey')
			->will($this->returnValue('de'));
		$this->lng->expects($this->any())
			->method('getDefaultLanguage')
			->will($this->returnValue('fr'));

		$this->source_files = array(
			vfsStream::url(implode('/', array('root', 'Customizing', 'clients', 'default', 'agreement', 'agreement_' . $this->lng->getLangKey() . '.html')))         => $this->lng->getLangKey(),
			vfsStream::url(implode('/', array('root', 'Customizing', 'clients', 'default', 'agreement', 'agreement_' . $this->lng->getDefaultLanguage() . '.html'))) => $this->lng->getDefaultLanguage(),
			vfsStream::url(implode('/', array('root', 'Customizing', 'clients', 'default', 'agreement', 'agreement_en.html')))                                       => 'en',
			vfsStream::url(implode('/', array('root', 'Customizing', 'global', 'agreement', 'agreement_' . $this->lng->getLangKey() . '.html')))                     => $this->lng->getLangKey(),
			vfsStream::url(implode('/', array('root', 'Customizing', 'global', 'agreement', 'agreement_' . $this->lng->getDefaultLanguage() . '.html')))             => $this->lng->getDefaultLanguage(),
			vfsStream::url(implode('/', array('root', 'Customizing', 'global', 'agreement', 'agreement_en.html')))                                                   => 'en'
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
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @expectedException ilTermsOfServiceNoSignableDocumentFoundException
	 */
	public function testExceptionIsRaisedWhenNoSingableDocumentCouldBeFoundForCurrentLanguage()
	{
		$document = new ilTermsOfServiceFileSystemDocument($this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock());
		$document->determine();
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testClientDocumentCouldBeRetrievedByCurrentLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfsStream::newFile('agreement_de.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfsStream::url('root/Customizing/clients/default/agreement/agreement_de.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('de', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfsStream::url('root/Customizing/clients/default/agreement/agreement_de.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testClientDocumentCouldBeRetrievedByDefaultLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfsStream::newFile('agreement_fr.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfsStream::url('root/Customizing/clients/default/agreement/agreement_fr.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('fr', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfsStream::url('root/Customizing/clients/default/agreement/agreement_fr.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testClientDocumentCouldBeRetrievedByEnglishLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfsStream::newFile('agreement_en.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfsStream::url('root/Customizing/clients/default/agreement/agreement_en.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('en', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfsStream::url('root/Customizing/clients/default/agreement/agreement_en.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testGlobalDocumentCouldBeRetrievedByCurrentLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfsStream::newFile('agreement_de.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfsStream::url('root/Customizing/global/agreement/agreement_de.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('de', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfsStream::url('root/Customizing/global/agreement/agreement_de.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testGlobalDocumentCouldBeRetrievedByDefaultLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfsStream::newFile('agreement_fr.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfsStream::url('root/Customizing/global/agreement/agreement_fr.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('fr', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfsStream::url('root/Customizing/global/agreement/agreement_fr.html'), $document->getSource());
	}

	/**
	 * @param ilTermsOfServiceFileSystemDocument $document
	 * @depends testInstanceCanBeCreated
	 */
	public function testGlobalDocumentCouldBeRetrievedByEnglishLanguage(ilTermsOfServiceFileSystemDocument $document)
	{
		vfsStream::newFile('agreement_en.html', 0777)->withContent('phpunit')->at($this->client_dir);
		file_put_contents(vfsStream::url('root/Customizing/global/agreement/agreement_en.html'), 'phpunit');

		$document->determine();
		$this->assertEquals('en', $document->getIso2LanguageCode());
		$this->assertTrue($document->hasContent());
		$this->assertEquals('phpunit', $document->getContent());
		$this->assertEquals(ilTermsOfServiceFileSystemDocument::SRC_TYPE_FILE_SYSTEM_PATH, $document->getSourceType());
		$this->assertEquals(vfsStream::url('root/Customizing/global/agreement/agreement_en.html'), $document->getSource());
	}
}