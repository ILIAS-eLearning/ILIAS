<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ .'/../classes/class.ilPhantomJsHtmlToPdfTransformerGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Services/Form/classes/class.ilFormSectionHeaderGUI.php';
require_once 'Services/Language/classes/class.ilLanguage.php';

/**
 * Class ilPhantomJsHtmlToPdfTransformerTest
 * @package ilPdfGenerator
 */
class ilPhantomJsHtmlToPdfTransformerGUITest  extends PHPUnit_Framework_TestCase
{

	protected $lng;
	/**
	 * ilPhantomJsHtmlToPdfTransformerGUITest constructor.
	 */
	public function __construct()
	{
		$this->lng = $this->getMockBuilder('ilLanguage')
					->disableOriginalConstructor()
					->getMock();
		$this->lng->method('txt')
			->will($this->returnArgument(0));
		
	}

	protected static function getMethod($name) {
		$class = new ReflectionClass('ilPhantomJsHtmlToPdfTransformerGUI');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public function testBuildJavascriptDelayForm()
	{
		$transformer = self::getMethod('buildJavascriptDelayForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('javascript_delay', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('javascript_delay', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildZoomForm()
	{
		$transformer = self::getMethod('buildZoomForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('zoom', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('zoom', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildMarginForm()
	{
		$transformer = self::getMethod('buildMarginForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('margin', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('margin', $transformer->invokeArgs($obj, array())->getPostVar());
	}
	
	public function testBuildFooterHeightForm()
	{
		$transformer = self::getMethod('buildFooterHeightForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('footer_height', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('footer_height', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildFooterTextForm()
	{
		$transformer = self::getMethod('buildFooterTextForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('footer_text', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('footer_text', $transformer->invokeArgs($obj, array())->getPostVar());
	}
	
	public function testBuildHeaderHeightForm()
	{
		$transformer = self::getMethod('buildHeaderHeightForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('header_height', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('header_height', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildHeaderTextForm()
	{
		$transformer = self::getMethod('buildHeaderTextForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('head_text', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('header_text', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildPrintMediaTypeForm()
	{
		$transformer = self::getMethod('buildPrintMediaTypeForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('print_media_type', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('print_media_type', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildHeaderPageNumbersForm()
	{
		$transformer = self::getMethod('buildHeaderPageNumbersForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('header_show_pages', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('header_show_pages', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildFooterPageNumbersForm()
	{
		$transformer = self::getMethod('buildFooterPageNumbersForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('footer_show_pages', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('footer_show_pages', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildPageSizesForm()
	{
		$transformer = self::getMethod('buildPageSizesForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilSelectInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('page_size', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('page_size', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildOrientationForm()
	{
		$transformer = self::getMethod('buildOrientationForm');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilSelectInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('orientation', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('orientation', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildPageSettingsHeader()
	{
		$transformer = self::getMethod('buildPageSettingsHeader');
		$obj = new ilPhantomJsHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilFormSectionHeaderGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('page_settings', $transformer->invokeArgs($obj, array())->getTitle());
	}

} 