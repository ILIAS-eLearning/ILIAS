<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ .'/../classes/class.ilWebkitHtmlToPdfTransformer.php';
require_once __DIR__ .'/../classes/class.ilPDFGenerationConstants.php';
/**
 * Class ilWebkitHtmlToPdfTransformerTest
 * @package ilPdfGenerator
 */
class ilWebkitHtmlToPdfTransformerTest  extends PHPUnit_Framework_TestCase
{

	protected static function getMethod($name) {
		$class = new ReflectionClass('ilWebkitHtmlToPdfTransformer');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$transformer = new ilWebkitHtmlToPdfTransformer(true);
		$this->assertInstanceOf('ilWebkitHtmlToPdfTransformer', $transformer);
		$this->assertSame('ilWebkitHtmlToPdfTransformer', $transformer->getId());
	}


	public function testSettingName()
	{
		$transformer = new ilWebkitHtmlToPdfTransformer(true);
		$this->assertSame('pdf_transformer_webkit', $transformer::SETTING_NAME);
	}

	public function testSupportMultiSourcesFiles()
	{
		$transformer = new ilWebkitHtmlToPdfTransformer(true);
		$this->assertSame(true, $transformer->supportMultiSourcesFiles());
	}

	public function testGetTitle()
	{
		$transformer = new ilWebkitHtmlToPdfTransformer(true);
		$this->assertSame('webkit', $transformer->getTitle());
	}

	public function testGetCommandLineConfigSimple()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$obj = new ilWebkitHtmlToPdfTransformer(true);
		$obj->setOrientation('Portrait');
		$obj->setPageSize('A1');
		$obj->setZoom(0.5);
		$obj->setJavascriptDelay(500);
		$obj->setMarginLeft('2');
		$obj->setMarginRight('2');
		$obj->setMarginTop('2');
		$obj->setMarginBottom('2');
		$expec = ' --zoom 0.5 --disable-external-links --disable-forms --orientation Portrait --page-size A1 --javascript-delay 500 --margin-bottom 2 --margin-left 2 --margin-right 2 --margin-top 2 --quiet ';
		$this->assertSame($expec, $transformer->invokeArgs($obj, array()));
	}

	public function testGetPathToTestHTML()
	{
		$transformer = new ilWebkitHtmlToPdfTransformer(true);
		$this->assertSame('Services/PDFGeneration/templates/default/test_complex.html', $transformer->getPathToTestHTML());
	}

	/**
	 * @var ilWebkitHtmlToPdfTransformer
	 */
	protected $obj;

	/**
	 * @before
	 */
	public function setupSomeFixtures()
	{
		$this->obj = new ilWebkitHtmlToPdfTransformer(true);
		$this->obj->setPageSize('A4');
		$this->obj->setZoom(1);
		$this->obj->setOrientation('Landscape');
		$this->obj->setJavascriptDelay(100);
		$this->obj->setMarginLeft('1');
		$this->obj->setMarginRight('2');
		$this->obj->setMarginTop('3');
		$this->obj->setMarginBottom('4');
	}

	protected $default_start = ' --zoom 1 --disable-external-links --disable-forms ';

	protected $default_end = '--orientation Landscape --page-size A4 --javascript-delay 100 --margin-bottom 4 --margin-left 1 --margin-right 2 --margin-top 3 ';

	protected $default_quiet = '--quiet ';

	public function testGetCommandLineConfigOnObject()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$expec = $this->default_start . $this->default_end . $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithGrayscale()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setGreyscale(true);
		$expec = $this->default_start . '--grayscale ' .  $this->default_end .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithHeaderTextWithoutLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setHeaderType(ilPDFGenerationConstants::HEADER_TEXT);
		$this->obj->setHeaderTextLeft('Left');
		$this->obj->setHeaderTextCenter('Center');
		$this->obj->setHeaderTextRight('Right');
		$this->obj->setHeaderTextSpacing(2);
		$expec = $this->default_start . $this->default_end .'--header-left "Left" --header-center "Center" --header-right "Right" --header-spacing 2 ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithHeaderTextWithLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setHeaderType(ilPDFGenerationConstants::HEADER_TEXT);
		$this->obj->setHeaderTextLeft('Left');
		$this->obj->setHeaderTextCenter('Center');
		$this->obj->setHeaderTextRight('Right');
		$this->obj->setHeaderTextLine(true);
		$expec = $this->default_start . $this->default_end .'--header-left "Left" --header-center "Center" --header-right "Right" --header-spacing  --header-line ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithHeaderHtmlWithoutLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setHeaderType(ilPDFGenerationConstants::HEADER_HTML);
		$this->obj->setHeaderHtml('<div><b>Test</b></div>');
		$this->obj->setHeaderHtmlSpacing(2);
		$expec = $this->default_start . $this->default_end .'--header-html "<div><b>Test</b></div>" --header-spacing 2 ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithHeaderHtmlWithHeaderTextConfigured()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setHeaderType(ilPDFGenerationConstants::HEADER_HTML);
		$this->obj->setHeaderHtml('<div><b>Test</b></div>');
		$this->obj->setHeaderHtmlSpacing(1);
		$this->obj->setHeaderTextLeft('Left');
		$this->obj->setHeaderTextCenter('Center');
		$this->obj->setHeaderTextRight('Right');
		$expec = $this->default_start . $this->default_end .'--header-html "<div><b>Test</b></div>" --header-spacing 1 ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithHeaderHtmlWithLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setHeaderType(ilPDFGenerationConstants::HEADER_HTML);
		$this->obj->setHeaderHtml('<div><b>Test</b></div>');
		$this->obj->setHeaderHtmlLine(true);
		$expec = $this->default_start . $this->default_end .'--header-html "<div><b>Test</b></div>" --header-spacing  --header-line ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithFooterTextWithoutLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setFooterType(ilPDFGenerationConstants::FOOTER_TEXT);
		$this->obj->setFooterTextLeft('Left');
		$this->obj->setFooterTextCenter('Center');
		$this->obj->setFooterTextRight('Right');
		$this->obj->setFooterTextSpacing(2);
		$expec = $this->default_start . $this->default_end .'--footer-left "Left" --footer-center "Center" --footer-right "Right" --footer-spacing 2 ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithFooterTextWithLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setFooterType(ilPDFGenerationConstants::FOOTER_TEXT);
		$this->obj->setFooterTextLeft('Left');
		$this->obj->setFooterTextCenter('Center');
		$this->obj->setFooterTextRight('Right');
		$this->obj->setFooterTextLine(true);
		$expec = $this->default_start . $this->default_end .'--footer-left "Left" --footer-center "Center" --footer-right "Right" --footer-spacing  --footer-line ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithFooterHtmlWithoutLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setFooterType(ilPDFGenerationConstants::FOOTER_HTML);
		$this->obj->setFooterHtml('<div><b>Test</b></div>');
		$this->obj->setFooterHtmlSpacing(2);
		$expec = $this->default_start . $this->default_end .'--footer-html "<div><b>Test</b></div>" --footer-spacing 2 ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithFooterHtmlWithLine()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setFooterType(ilPDFGenerationConstants::FOOTER_HTML);
		$this->obj->setFooterHtml('<div><b>Test</b></div>');
		$this->obj->setFooterHtmlLine(true);
		$expec = $this->default_start . $this->default_end .'--footer-html "<div><b>Test</b></div>" --footer-spacing  --footer-line ' .  $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithEnabledForms()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setEnabledForms(true);
		$expec = ' --zoom 1 --disable-external-links --enable-forms '.$this->default_end . $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithEnabledExternalLinks()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setExternalLinks(true);
		$expec = ' --zoom 1 --enable-external-links --disable-forms '.$this->default_end . $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithEnabledLowQuality()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setLowQuality(true);
		$expec = ' --zoom 1 --disable-external-links --disable-forms --lowquality '.$this->default_end . $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	protected $default_margin_args = '--margin-bottom 4 --margin-left 1 --margin-right 2 --margin-top 3 --quiet ';

	public function testGetCommandLineConfigWithEnabledPrintMediaType()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setPrintMediaType(true);
		$expec = ' --zoom 1 --disable-external-links --disable-forms --orientation Landscape --print-media-type --page-size A4 --javascript-delay 100 '. $this->default_margin_args;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithEnabledCustomStyleSheet()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setUserStylesheet('my_super_css_class.css');
		$expec = ' --zoom 1 --disable-external-links --disable-forms --user-style-sheet "my_super_css_class.css" '.$this->default_end . $this->default_quiet;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithCheckbox()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setCheckboxSvg('checkbox.svg');
		$expec = ' --zoom 1 --disable-external-links --disable-forms --orientation Landscape --page-size A4 --javascript-delay 100 --checkbox-svg "checkbox.svg" '. $this->default_margin_args;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithCheckedCheckbox()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setCheckboxCheckedSvg('checkbox_checked.svg');
		$expec = ' --zoom 1 --disable-external-links --disable-forms --orientation Landscape --page-size A4 --javascript-delay 100 --checkbox-checked-svg "checkbox_checked.svg" '. $this->default_margin_args;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithRadiobutton()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setRadioButtonSvg('radiobutton.svg');
		$expec = ' --zoom 1 --disable-external-links --disable-forms --orientation Landscape --page-size A4 --javascript-delay 100 --radiobutton-svg "radiobutton.svg" '. $this->default_margin_args;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testGetCommandLineConfigWithCheckedRadiobutton()
	{
		$transformer = self::getMethod('getCommandLineConfig');
		$this->obj->setRadioButtonCheckedSvg('radiobutton_checked.svg');
		$expec = ' --zoom 1 --disable-external-links --disable-forms --orientation Landscape --page-size A4 --javascript-delay 100 --radiobutton-checked-svg "radiobutton_checked.svg" '. $this->default_margin_args;
		$this->assertSame($expec, $transformer->invokeArgs($this->obj, array()));
	}

	public function testRedirectLogMethod()
	{
		$transformer = self::getMethod('redirectLog');
		$this->assertSame(' 2>&1 ', $transformer->invokeArgs($this->obj, array()));
	}

} 