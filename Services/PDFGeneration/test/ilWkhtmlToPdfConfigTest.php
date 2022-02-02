<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ .'/../classes/renderer/wkhtmltopdf/class.ilWkhtmlToPdfConfig.php';
use PHPUnit\Framework\TestCase;
/**
 * Class ilWebkitHtmlToPdfTransformerTest
 * @package ilPdfGenerator
 */
class ilWkhtmlToPdfConfigTest  extends TestCase
{

    const COOKIE_STRING = '--cookie "PHPSESSID" "" --cookie "ilClientId" "1" ';
    /**
     * @var ilWkhtmlToPdfConfig
     */
    protected $config;

    protected function setUp() : void
    {
        $this->config = new ilWkhtmlToPdfConfig();
    }

    public function testInstanceCanBeCreated()
    {
        $this->assertInstanceOf('ilWkhtmlToPdfConfig', $this->config);
    }


    public function testDefaultConfig()
    {
        $this->assertFalse($this->config->getEnabledForms());
        $this->assertTrue($this->config->getExternalLinks());
        $this->assertSame(500, $this->config->getJavascriptDelay());
        $this->assertSame(1.0, $this->config->getZoom());
        $this->assertSame('Portrait', $this->config->getOrientation());
        $this->assertSame('A4', $this->config->getPageSize());
        $this->assertSame('0.5cm', $this->config->getMarginLeft());
        $this->assertSame('2cm', $this->config->getMarginRight());
        $this->assertSame('0.5cm', $this->config->getMarginBottom());
        $this->assertSame('2cm', $this->config->getMarginTop());

    }

    public function testDefaultConfigCommandline()
    {
        $cmd = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet ' . self::COOKIE_STRING;

        $this->assertSame($cmd, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigSimple()
    {
        $this->config->setOrientation('Portrait');
        $this->config->setPageSize('A1');
        $this->config->setZoom(0.5);
        $this->config->setJavascriptDelay(500);
        $this->config->setMarginLeft('2');
        $this->config->setMarginRight('2');
        $this->config->setMarginTop('2');
        $this->config->setMarginBottom('2');
        $exp = ' --zoom 0.5 --enable-external-links --disable-forms --orientation Portrait --page-size A1 --javascript-delay 500 --margin-bottom 2 --margin-left 2 --margin-right 2 --margin-top 2 --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    protected string $default_start = ' --zoom 1 --enable-external-links --disable-forms ';
    protected string $default_end = '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet ' . self::COOKIE_STRING;
    protected string $default_quiet = '--quiet ';
    protected string $second_quiet = '--quiet --cookie "PHPSESSID" "" --cookie "ilClientId" "1" ';

    public function testGetCommandLineConfigOnObject()
    {
        $exp = $this->default_start . $this->default_end;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithGrayscale()
    {
        $this->config->setGreyscale(true);
        $exp = $this->default_start . '--grayscale ' .  $this->default_end;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithHeaderTextWithoutLine()
    {
        $this->config->setHeaderType(ilPDFGenerationConstants::HEADER_TEXT);
        $this->config->setHeaderTextLeft('Left');
        $this->config->setHeaderTextCenter('Center');
        $this->config->setHeaderTextRight('Right');
        $this->config->setHeaderTextSpacing(2);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --header-left "Left" --header-center "Center" --header-right "Right" --header-spacing 2 --quiet --cookie "PHPSESSID" "" --cookie "ilClientId" "1" ';
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithHeaderTextWithLine()
    {
        $this->config->setHeaderType(ilPDFGenerationConstants::HEADER_TEXT);
        $this->config->setHeaderTextLeft('Left');
        $this->config->setHeaderTextCenter('Center');
        $this->config->setHeaderTextRight('Right');
        $this->config->setHeaderTextLine(true);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --header-left "Left" --header-center "Center" --header-right "Right" --header-spacing 0 --header-line --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithHeaderHtmlWithoutLine()
    {
        $this->config->setHeaderType(ilPDFGenerationConstants::HEADER_HTML);
        $this->config->setHeaderHtml('<div><b>Test</b></div>');
        $this->config->setHeaderHtmlSpacing(2);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --header-html "<div><b>Test</b></div>" --header-spacing 2 --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithHeaderHtmlWithHeaderTextConfigured()
    {
        $this->config->setHeaderType(ilPDFGenerationConstants::HEADER_HTML);
        $this->config->setHeaderHtml('<div><b>Test</b></div>');
        $this->config->setHeaderHtmlSpacing(1);
        $this->config->setHeaderTextLeft('Left');
        $this->config->setHeaderTextCenter('Center');
        $this->config->setHeaderTextRight('Right');
        $exp = $this->default_start .'--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --header-html "<div><b>Test</b></div>" --header-spacing 1 --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithHeaderHtmlWithLine()
    {
        $this->config->setHeaderType(ilPDFGenerationConstants::HEADER_HTML);
        $this->config->setHeaderHtml('<div><b>Test</b></div>');
        $this->config->setHeaderHtmlLine(true);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --header-html "<div><b>Test</b></div>" --header-spacing 0 --header-line --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithFooterTextWithoutLine()
    {
        $this->config->setFooterType(ilPDFGenerationConstants::FOOTER_TEXT);
        $this->config->setFooterTextLeft('Left');
        $this->config->setFooterTextCenter('Center');
        $this->config->setFooterTextRight('Right');
        $this->config->setFooterTextSpacing(2);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --footer-left "Left" --footer-center "Center" --footer-right "Right" --footer-spacing 2 --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithFooterTextWithLine()
    {
        $this->config->setFooterType(ilPDFGenerationConstants::FOOTER_TEXT);
        $this->config->setFooterTextLeft('Left');
        $this->config->setFooterTextCenter('Center');
        $this->config->setFooterTextRight('Right');
        $this->config->setFooterTextLine(true);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --footer-left "Left" --footer-center "Center" --footer-right "Right" --footer-spacing 0 --footer-line --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithFooterHtmlWithoutLine()
    {
        $this->config->setFooterType(ilPDFGenerationConstants::FOOTER_HTML);
        $this->config->setFooterHtml('<div><b>Test</b></div>');
        $this->config->setFooterHtmlSpacing(2);
        $exp = $this->default_start .'--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --footer-html "<div><b>Test</b></div>" --footer-spacing 2 --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithFooterHtmlWithLine()
    {
        $this->config->setFooterType(ilPDFGenerationConstants::FOOTER_HTML);
        $this->config->setFooterHtml('<div><b>Test</b></div>');
        $this->config->setFooterHtmlLine(true);
        $this->config->setFooterHtmlSpacing(1);
        $exp = $this->default_start . '--orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --footer-html "<div><b>Test</b></div>" --footer-spacing 1 --footer-line --quiet ' . self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithEnabledForms()
    {
        $this->config->setEnabledForms(true);
        $exp = ' --zoom 1 --enable-external-links --enable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithEnabledExternalLinks()
    {
        $this->config->setExternalLinks(true);
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithEnabledLowQuality()
    {
        $this->config->setLowQuality(true);
        $exp = ' --zoom 1 --enable-external-links --disable-forms --lowquality --orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    protected string $default_margin_args = '--margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm ';

    public function testGetCommandLineConfigWithEnabledPrintMediaType()
    {
        $this->config->setPrintMediaType(true);
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --print-media-type --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithEnabledCustomStyleSheet()
    {
        $this->config->setUserStylesheet('my_super_css_class.css');
        $exp = ' --zoom 1 --enable-external-links --disable-forms --user-style-sheet "my_super_css_class.css" --orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithCheckbox()
    {
        $this->config->setCheckboxSvg('checkbox.svg');
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --checkbox-svg "checkbox.svg" --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithCheckedCheckbox()
    {
        $this->config->setCheckboxCheckedSvg('checkbox_checked.svg');
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --checkbox-checked-svg "checkbox_checked.svg" --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithRadiobutton()
    {
        $this->config->setRadioButtonSvg('radiobutton.svg');
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --radiobutton-svg "radiobutton.svg" --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithCheckedRadiobutton()
    {
        $this->config->setRadioButtonCheckedSvg('radiobutton_checked.svg');
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --radiobutton-checked-svg "radiobutton_checked.svg" --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithDisabledExternalLinks()
    {
        $this->config->setExternalLinks(false);
        $exp = ' --zoom 1 --disable-external-links --disable-forms --orientation Portrait --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testGetCommandLineConfigWithLandscape()
    {
        $this->config->setOrientation('Landscape');
        $exp = ' --zoom 1 --enable-external-links --disable-forms --orientation Landscape --page-size A4 --javascript-delay 500 --margin-bottom 0.5cm --margin-left 0.5cm --margin-right 2cm --margin-top 2cm --quiet '. self::COOKIE_STRING;
        $this->assertSame($exp, $this->config->getCommandLineConfig());
    }

    public function testSupportMultiSourceFiles()
    {
        $this->assertTrue($this->config->supportMultiSourcesFiles());
    }

    public function testSetPathShouldReturnPath()
    {
        $this->config->setPath('/MY/LITTLE/PATH');
        $this->assertSame('/MY/LITTLE/PATH', $this->config->getPath());
        $this->assertSame('/usr/local/bin/wkhtmltopdf', $this->config->getWKHTMLToPdfDefaultPath());
    }

    public function testGetConfigShouldReturnConfigObject()
    {
        $this->assertSame(array(), $this->config->getConfig());
    }

    public function testReadConfigFromObject()
    {
        $this->config->setExternalLinks(false);
        $this->config->setEnabledForms(true);
        $cfg = new ilWkhtmlToPdfConfig($this->config);
        $this->assertTrue($cfg->getEnabledForms());
        $this->assertFalse($cfg->getExternalLinks());
    }

    public function testReadConfigFromJson()
    {
        $json = array(
            "zoom" => "0.4",
            "enable_forms" => "true",
            "external_links" => "true",
            "user_stylesheet" => "my_style_sheet.css",
            "low_quality" => "0",
            "greyscale" => "0",
            "orientation" => "Landscape",
            "page_size" => "A1",
            "margin_left" => "1cm",
            "margin_right" => "2cm",
            "footer_html_spacing" => 3,
            "footer_html" => "<div>my html </div>",
            "footer_text_line" => "1",
            "footer_text_center" => "my footer text",
            "footer_text_spacing" => 1,
            "footer_text_right" => "right text",
            "footer_text_left" => "left text",
            "footer_select" => "0",
            "head_html_spacing" => "1",
            "head_html_line" => "0",
            "head_text_line" => "1",
            "head_text_spacing" => "1",
            "head_text_right" => "head text right",
            "head_text_center" => "head text center",
            "head_text_left" => "head text left",
            "header_select" => "1",
            "radio_button_checked_svg" => "r_c.svg",
            "radio_button_svg" => "r.svg",
            "checkbox_checked_svg" => "c_c.svg",
            "checkbox_svg" => "c.svg",
            "javascript_delay" => "231",
            "print_media_type" => "1",
            "margin_top" => "5cm",
            "margin_bottom" => "6cm",
        );
        $cfg = new ilWkhtmlToPdfConfig($json);
        $this->assertSame(1, $cfg->getHeaderHtmlSpacing());
        $this->assertSame(false, $cfg->isHeaderHtmlLine());
        $this->assertSame(true, $cfg->isHeaderTextLine());
        $this->assertSame(1, $cfg->getHeaderTextSpacing());
        $this->assertSame("head text right", $cfg->getHeaderTextRight());
        $this->assertSame("head text center", $cfg->getHeaderTextCenter());
        $this->assertSame("head text left", $cfg->getHeaderTextLeft());
        $this->assertSame(1, $cfg->getHeaderType());
        $this->assertSame("r_c.svg", $cfg->getRadioButtonCheckedSvg());
        $this->assertSame("r.svg", $cfg->getRadioButtonSvg());
        $this->assertSame("c_c.svg", $cfg->getCheckboxCheckedSvg());
        $this->assertSame("c.svg", $cfg->getCheckboxSvg());
        $this->assertSame(231, $cfg->getJavascriptDelay());
        $this->assertSame(true, $cfg->getPrintMediaType());
        $this->assertSame('5cm', $cfg->getMarginTop());
        $this->assertSame('6cm', $cfg->getMarginBottom());
        $this->assertSame(0.4, $cfg->getZoom());
        $this->assertSame(true, $cfg->getExternalLinks());
        $this->assertSame('my_style_sheet.css', $cfg->getUserStylesheet());
        $this->assertSame(false, $cfg->getLowQuality());
        $this->assertSame(false, $cfg->getGreyscale());
        $this->assertSame('Landscape', $cfg->getOrientation());
        $this->assertSame('A1', $cfg->getPageSize());
        $this->assertSame('1cm', $cfg->getMarginLeft());
        $this->assertSame('2cm', $cfg->getMarginRight());
        $this->assertSame(3, $cfg->getFooterHtmlSpacing());
        $this->assertSame('<div>my html </div>', $cfg->getFooterHtml());
        $this->assertSame('my footer text', $cfg->getFooterTextCenter());
        $this->assertSame(true, $cfg->isFooterTextLine());
        $this->assertSame(1, $cfg->getFooterTextSpacing());
        $this->assertSame('right text', $cfg->getFooterTextRight());
        $this->assertSame('left text', $cfg->getFooterTextLeft());
        $this->assertSame(0, $cfg->getFooterType());
    }
}