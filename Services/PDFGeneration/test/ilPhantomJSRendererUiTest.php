<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilPhantomJSRendererUiTest
 * @package ilPdfGenerator
 */
class ilPhantomJSRendererUiTest  extends TestCase
{

	protected $lng;

	protected $form;

	protected function setUp(): void
	{
		$GLOBALS["DIC"] = new \ILIAS\DI\Container();

		$this->lng = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();
		$this->lng->method('txt')
			->will($this->returnArgument(0));

		$this->form = new ilPhantomJSRenderer(true);
		$this->callMethod($this->form, 'setLanguage', array($this->lng));
		$this->setGlobalVariable('lng', $this->lng);
		$this->setGlobalVariable('ilCtrl', null);
	}
	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setGlobalVariable($name, $value)
	{
		global $DIC;

		$GLOBALS[$name] = $value;

		unset($DIC[$name]);
		$DIC[$name] = function ($c) use ($name) {
			return $GLOBALS[$name];
		};
	}

	protected static function getMethod($name) {
		$class = new ReflectionClass('ilPhantomJSRenderer');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public static function callMethod($obj, $name, array $args) {
		$class = new ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

	public function testBuildJavascriptDelayForm()
	{
		$transformer = self::getMethod('buildJavascriptDelayForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('javascript_delay', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('javascript_delay', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildMarginForm()
	{
		$transformer = self::getMethod('buildMarginForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('margin', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('margin', $transformer->invokeArgs($this->form, array())->getPostVar());
	}
	
	public function testBuildFooterHeightForm()
	{
		$transformer = self::getMethod('buildFooterHeightForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('footer_height', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('footer_height', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildFooterTextForm()
	{
		$transformer = self::getMethod('buildFooterTextForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('footer_text', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('footer_text', $transformer->invokeArgs($this->form, array())->getPostVar());
	}
	
	public function testBuildHeaderHeightForm()
	{
		$transformer = self::getMethod('buildHeaderHeightForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('header_height', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('header_height', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildHeaderTextForm()
	{
		$transformer = self::getMethod('buildHeaderTextForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('head_text', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('header_text', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildHeaderPageNumbersForm()
	{
		$transformer = self::getMethod('buildHeaderPageNumbersForm');
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('header_show_pages', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('header_show_pages', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildFooterPageNumbersForm()
	{
		$transformer = self::getMethod('buildFooterPageNumbersForm');
		$this->assertInstanceOf('ilCheckboxInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('footer_show_pages', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('footer_show_pages', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildViewPortForm()
	{
		$transformer = self::getMethod('buildViewPortForm');
		$this->assertInstanceOf('ilTextInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('viewport', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('viewport', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildPageSizesForm()
	{
		$transformer = self::getMethod('buildPageSizesForm');
		$this->assertInstanceOf('ilSelectInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('page_size', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('page_size', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

	public function testBuildOrientationForm()
	{
		$transformer = self::getMethod('buildOrientationForm');
		$this->assertInstanceOf('ilSelectInputGUI', $transformer->invokeArgs($this->form, array()));
		$this->assertSame('orientation', $transformer->invokeArgs($this->form, array())->getTitle());
		$this->assertSame('orientation', $transformer->invokeArgs($this->form, array())->getPostVar());
	}

} 