<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ .'/../classes/class.ilWebkitHtmlToPdfTransformerGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Services/Form/classes/class.ilFormSectionHeaderGUI.php';
require_once 'Services/Form/classes/class.ilRadioGroupInputGUI.php';
require_once 'Services/Language/classes/class.ilLanguage.php';
require_once 'Services/Administration/classes/class.ilSetting.php';

/**
 * Class ilWebkitHtmlToPdfTransformerGUITest
 * @package ilPdfGenerator
 */
class ilWebkitHtmlToPdfTransformerGUITest  extends PHPUnit_Framework_TestCase
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
		$class = new ReflectionClass('ilWebkitHtmlToPdfTransformerGUI');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public function testBuildMarginBottomForm()
	{
		$transformer = self::getMethod('buildMarginBottomForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('margin_bottom', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('margin_bottom', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildMarginTopForm()
	{
		$transformer = self::getMethod('buildMarginTopForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('margin_top', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('margin_top', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildMarginRightForm()
	{
		$transformer = self::getMethod('buildMarginRightForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('margin_right', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('margin_right', $transformer->invokeArgs($obj, array())->getPostVar());
	}
	public function testBuildMarginLeftForm()
	{
		$transformer = self::getMethod('buildMarginLeftForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('margin_left', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('margin_left', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildJavascriptDelayForm()
	{
		$transformer = self::getMethod('buildMarginBottomForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('margin_bottom', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('margin_bottom', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildPageSizesForm()
	{
		$transformer = self::getMethod('buildPageSizesForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilSelectInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('page_size', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('page_size', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildOrientationsForm()
	{
		$transformer = self::getMethod('buildOrientationsForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilSelectInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('orientation', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('orientation', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildZoomForm()
	{
		$transformer = self::getMethod('buildZoomForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('zoom', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('zoom', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildHeaderForm()
	{
		$transformer = self::getMethod('buildHeaderForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilRadioGroupInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('header_type', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('header_select', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildCheckboxSvgForm()
	{
		$transformer = self::getMethod('buildCheckboxSvgForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('checkbox_svg', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('checkbox_svg', $transformer->invokeArgs($obj, array())->getPostVar());
	}
	
	public function testBuildCheckedRadiobuttonSvgForm()
	{
		$transformer = self::getMethod('buildCheckedRadiobuttonSvgForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('radio_button_checked_svg', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('radio_button_checked_svg', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildRadiobuttonSvgForm()
	{
		$transformer = self::getMethod('buildRadiobuttonSvgForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('radio_button_svg', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('radio_button_svg', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildCheckedCheckboxSvgForm()
	{
		$transformer = self::getMethod('buildCheckedCheckboxSvgForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('checkbox_checked_svg', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('checkbox_checked_svg', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildPrintMediaTypeForm()
	{
		$transformer = self::getMethod('buildPrintMediaTypeForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('print_media_type', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('print_media_type', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildGreyScaleForm()
	{
		$transformer = self::getMethod('buildGreyScaleForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('greyscale', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('greyscale', $transformer->invokeArgs($obj, array())->getPostVar());
	}
	
	public function testBuildLowQualityForm()
	{
		$transformer = self::getMethod('buildLowQualityForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('low_quality', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('low_quality', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildUserStylesheetForm()
	{
		$transformer = self::getMethod('buildUserStylesheetForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('user_stylesheet', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('user_stylesheet', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildEnableFormsForm()
	{
		$transformer = self::getMethod('buildEnableFormsForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('enable_forms', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('enable_forms', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildExternalLinksForm()
	{
		$transformer = self::getMethod('buildExternalLinksForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('external_links', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('external_links', $transformer->invokeArgs($obj, array())->getPostVar());
	}

	public function testBuildFooterForm()
	{
		$transformer = self::getMethod('buildFooterForm');
		$obj = new ilWebkitHtmlToPdfTransformerGUI($this->lng);
		$this->assertInstanceOf('ilRadioGroupInputGUI', $transformer->invokeArgs($obj, array()));
		$this->assertSame('footer_type', $transformer->invokeArgs($obj, array())->getTitle());
		$this->assertSame('footer_select', $transformer->invokeArgs($obj, array())->getPostVar());
	}
	
	
	
} 