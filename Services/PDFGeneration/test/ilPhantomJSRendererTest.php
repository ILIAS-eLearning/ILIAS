<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../classes/renderer/phantomjs/class.ilPhantomJSRenderer.php';
define("PATH_TO_PHANTOMJS", '');
/**
 * Class ilPhantomJSRendererTest
 * @package ilPdfGenerator
 */
class ilPhantomJSRendererTest extends PHPUnit_Framework_TestCase
{
    protected $default_config = array('path' => '/usr/local/bin/phantomjs',
                                      'page_size' => 'A4',
                                      'margin' => '1cm',
                                      'javascript_delay' => 200,
                                      'viewport' => '',
                                      'orientation' => 'Portrait',
                                      'header_type' => 0,
                                      'header_text' => '',
                                      'header_height' => '0cm',
                                      'header_show_pages' => 0,
                                      'footer_type' => 0,
                                      'footer_text' => '',
                                      'footer_height' => '0cm',
                                      'footer_show_pages' => 0,
                                      'page_type' => 0);


    protected $beckersche_config = array('path' => '/usr/local/bin/phantomjs',
                                      'page_size' => 'A4',
                                      'margin' => '1cm',
                                      'javascript_delay' => 200,
                                      'orientation' => 'Portrait',
                                      'viewport' => '',
                                      'header_type' => 0,
                                      'header_text' => '',
                                      'header_height' => '0cm',
                                      'header_show_pages' => 0,
                                      'footer_type' => 0,
                                      'footer_text' => '',
                                      'footer_height' => '0cm',
                                      'footer_show_pages' => 0,
                                      'page_type' => 0);

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('ilPhantomJSRenderer');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testInstanceCanBeCreated()
    {
        $transformer = new ilPhantomJSRenderer(true);
        $this->assertInstanceOf('ilPhantomJSRenderer', $transformer);
    }


    public function testGetSettings()
    {
        $obj = new ilPhantomJSRenderer(true);
        $config = $obj->getDefaultConfig('Bla', 'Blubb');
        $this->assertSame($this->default_config, $config);
    }

    public function testBeckerscheSettingsText()
    {
        $transformer = self::getMethod('getCommandLineConfig');
        $obj = new ilPhantomJSRenderer(true);
        $config = $this->beckersche_config;
        $this->assertSame('"{\"page_size\":\"A4\",\"orientation\":\"Portrait\",\"margin\":\"1cm\",\"delay\":200,\"viewport\":\"\",\"header\":null,\"footer\":null,\"page_type\":0}"', $transformer->invokeArgs($obj, array($config)));
    }

    public function testHeaderSettingsWithoutPageNumber()
    {
        $transformer = self::getMethod('getCommandLineConfig');
        $obj = new ilPhantomJSRenderer(true);
        $config = $this->beckersche_config;
        $config['pagesize'] = 'A4';
        $config['header_text'] = 'Hello';
        $config['header_height'] = '1cm';
        $config['header_type'] = '1';
        $config['header_show_pages'] = false;

        $this->assertSame('"{\"page_size\":\"A4\",\"orientation\":\"Portrait\",\"margin\":\"1cm\",\"delay\":200,\"viewport\":\"\",\"header\":{\"text\":\"Hello\",\"height\":\"1cm\",\"show_pages\":false},\"footer\":null,\"page_type\":0}"', $transformer->invokeArgs($obj, array($config)));
    }

    public function testFooterSettingsText()
    {
        $transformer = self::getMethod('getCommandLineConfig');
        $obj = new ilPhantomJSRenderer(true);
        $config = $this->beckersche_config;
        $config['pagesize'] = 'A4';
        $config['footer_text'] = 'Hello';
        $config['footer_height'] = '1cm';
        $config['footer_type'] = '1';
        $config['footer_show_pages'] = true;

        $this->assertSame('"{\"page_size\":\"A4\",\"orientation\":\"Portrait\",\"margin\":\"1cm\",\"delay\":200,\"viewport\":\"\",\"header\":null,\"footer\":{\"text\":\"Hello\",\"height\":\"1cm\",\"show_pages\":true},\"page_type\":0}"', $transformer->invokeArgs($obj, array($config)));
    }

    public function testFooterSettingsTextWithoutPageNumber()
    {
        $transformer = self::getMethod('getCommandLineConfig');
        $obj = new ilPhantomJSRenderer(true);
        $config = $this->beckersche_config;
        $config['pagesize'] = 'A4';
        $config['footer_text'] = 'Hello';
        $config['footer_height'] = '1cm';
        $config['footer_type'] = '1';
        $config['footer_show_pages'] = false;

        $this->assertSame('"{\"page_size\":\"A4\",\"orientation\":\"Portrait\",\"margin\":\"1cm\",\"delay\":200,\"viewport\":\"\",\"header\":null,\"footer\":{\"text\":\"Hello\",\"height\":\"1cm\",\"show_pages\":false},\"page_type\":0}"', $transformer->invokeArgs($obj, array($config)));
    }
}
