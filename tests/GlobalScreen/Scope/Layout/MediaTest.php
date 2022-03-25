<?php

namespace ILIAS\GlobalScreen\Scope\Layout;

use PHPUnit\Framework\TestCase;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Css;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Js;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class MediaTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class MediaTest extends TestCase
{
    /**
     * @var string
     */
    public $version;
    /**
     * @var MetaContent
     */
    public $meta_content;
    
    protected function setUp() : void
    {
        parent::setUp();
        $this->version = '1.2.3.4.5.6.7.8.9';
        $this->meta_content = new MetaContent($this->version);
    }
    
    public function testAddCssFile() : void
    {
        $path = '/path/to/file.css';
        $this->meta_content->addCss($path);
        $collection = $this->meta_content->getCss();
        
        $first_item = iterator_to_array($collection->getItems())[0];
        $this->assertInstanceOf(Css::class, $first_item);
        $this->assertEquals($path . '?version=' . $this->version, $first_item->getContent());
        $this->assertEquals(MetaContent::MEDIA_SCREEN, $first_item->getMedia());
    }
    
    public function testAddCssFileWithQuery() : void
    {
        $path = '/path/to/file.css?my=query';
        $this->meta_content->addCss($path);
        $collection = $this->meta_content->getCss();
        
        $first_item = iterator_to_array($collection->getItems())[0];
        $this->assertInstanceOf(Css::class, $first_item);
        $this->assertEquals($path . '&version=' . $this->version, $first_item->getContent());
        $this->assertEquals(MetaContent::MEDIA_SCREEN, $first_item->getMedia());
    }
    
    public function testAddInlineCss() : void
    {
        $css = 'body {background-color:red;}';
        $this->meta_content->addInlineCss($css);
        $collection = $this->meta_content->getInlineCss();
        
        $first_item = iterator_to_array($collection->getItems())[0];
        $this->assertInstanceOf(InlineCss::class, $first_item);
        $this->assertEquals($css, $first_item->getContent());
        $this->assertEquals(MetaContent::MEDIA_SCREEN, $first_item->getMedia());
    }
    
    public function testAddJsFile() : void
    {
        $path = '/path/to/file.js';
        $this->meta_content->addJs($path);
        $collection = $this->meta_content->getJs();
        
        $first_item = iterator_to_array($collection->getItems())[$path];
        $this->assertInstanceOf(Js::class, $first_item);
        $this->assertEquals($path . '?version=' . $this->version, $first_item->getContent());
        $this->assertEquals(2, $first_item->getBatch());
    }
    
    public function testAddJsFileWithQuery() : void
    {
        $path = '/path/to/file.js';
        $path_with_query = $path . '?my=query';
        $this->meta_content->addJs($path_with_query);
        $collection = $this->meta_content->getJs();
        
        $first_item = iterator_to_array($collection->getItems())[$path];
        $this->assertInstanceOf(Js::class, $first_item);
        $this->assertEquals($path_with_query . '&version=' . $this->version, $first_item->getContent());
        $this->assertEquals(2, $first_item->getBatch());
    }
}
