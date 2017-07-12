<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ .'/../classes/class.ilPhantomJsHtmlToPdfTransformer.php';
/**
 * Class ilPhantomJsHtmlToPdfTransformerTest
 * @package ilPdfGenerator
 */
class ilPhantomJsHtmlToPdfTransformerTest  extends PHPUnit_Framework_TestCase
{

	protected static function getMethod($name) {
		$class = new ReflectionClass('ilPhantomJsHtmlToPdfTransformer');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$transformer = new ilPhantomJsHtmlToPdfTransformer(true);
		$this->assertInstanceOf('ilPhantomJsHtmlToPdfTransformer', $transformer);
		$this->assertSame('ilPhantomJsHtmlToPdfTransformer', $transformer->getId());
	}


	public function testSettingName()
	{
		$transformer = new ilPhantomJsHtmlToPdfTransformer(true);
		$this->assertSame('pdf_transformer_phantom', $transformer::SETTING_NAME);
	}

	public function testSupportMultiSourcesFiles()
	{
		$transformer = new ilPhantomJsHtmlToPdfTransformer(true);
		$this->assertSame(false, $transformer->supportMultiSourcesFiles());
	}

	public function testGetTitle()
	{
		$transformer = new ilPhantomJsHtmlToPdfTransformer(true);
		$this->assertSame('phantomjs', $transformer->getTitle());
	}

	public function testGetSettings()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$obj->setPageSize('A4');
		$obj->setZoom(1);
		$obj->setOrientation('Landscape');
		$obj->setMargin(2);
		$obj->setJavascriptDelay(200);
		$this->assertSame('\'{"page_size":"A4","zoom":1,"orientation":"Landscape","margin":2,"delay":200,"viewport":null,"header":null,"footer":null}\'' ,  $transformer->invokeArgs($obj, array()));
	}

	public function testGetSettingsWithViewport()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$obj->setPageSize('A4');
		$obj->setZoom(1);
		$obj->setOrientation('Landscape');
		$obj->setMargin(2);
		$obj->setJavascriptDelay(200);
		$obj->setViewport('800*600');
		$this->assertSame('\'{"page_size":"A4","zoom":1,"orientation":"Landscape","margin":2,"delay":200,"viewport":"800*600","header":null,"footer":null}\'', $transformer->invokeArgs($obj, array()));
	}

	public function testHeaderSettingsText()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$obj->setPageSize('A4');
		$obj->setHeaderText('Hello');
		$obj->setHeaderHeight('1cm');
		$obj->setHeaderShowPages(true);
		$obj->setHeaderType(1);
		$this->assertSame('\'{"page_size":"A4","zoom":null,"orientation":null,"margin":null,"delay":null,"viewport":null,"header":{"text":"Hello","height":"1cm","show_pages":true},"footer":null}\'' ,  $transformer->invokeArgs($obj, array()));
	}

	public function testHeaderSettingsWithoutPageNumber()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$obj->setPageSize('A4');
		$obj->setHeaderText('Hello');
		$obj->setHeaderHeight('1cm');
		$obj->setHeaderShowPages(false);
		$obj->setHeaderType(1);
		$this->assertSame('\'{"page_size":"A4","zoom":null,"orientation":null,"margin":null,"delay":null,"viewport":null,"header":{"text":"Hello","height":"1cm","show_pages":false},"footer":null}\'' ,  $transformer->invokeArgs($obj, array()));
	}

	public function testFooterSettingsText()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$obj->setPageSize('A4');
		$obj->setFooterText('Hello');
		$obj->setFooterHeight('1cm');
		$obj->setFooterShowPages(true);
		$obj->setFooterType(1);
		$this->assertSame('\'{"page_size":"A4","zoom":null,"orientation":null,"margin":null,"delay":null,"viewport":null,"header":null,"footer":{"text":"Hello","height":"1cm","show_pages":true}}\'' ,  $transformer->invokeArgs($obj, array()));
	}

	public function testFooterSettingsTextWithoutPageNumber()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$obj->setPageSize('A4');
		$obj->setFooterText('Hello');
		$obj->setFooterHeight('1cm');
		$obj->setFooterShowPages(false);
		$obj->setFooterType(1);
		$this->assertSame('\'{"page_size":"A4","zoom":null,"orientation":null,"margin":null,"delay":null,"viewport":null,"header":null,"footer":{"text":"Hello","height":"1cm","show_pages":false}}\'' ,  $transformer->invokeArgs($obj, array()));
	}

	public function testGetSettingsWithPrintMediaType()
	{
		$transformer = self::getMethod('isPrintMediaType');
		$obj = new ilPhantomJsHtmlToPdfTransformer(true);
		$this->assertSame(false, $transformer->invokeArgs($obj, array()));
		$obj->setPrintMediaType(true);
		$this->assertSame(true, $transformer->invokeArgs($obj, array()));
	}
} 