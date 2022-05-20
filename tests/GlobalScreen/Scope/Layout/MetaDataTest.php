<?php

namespace ILIAS\GlobalScreen\Scope\Layout;

use PHPUnit\Framework\TestCase;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Css;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Js;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaData\MetaDatum;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class MetaDataTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaDataTest extends TestCase
{
    /**
     * @var MetaContent 
     */
    public $meta_content;
    
    protected function setUp() : void
    {
        parent::setUp();
        $this->meta_content = new MetaContent('1.0');
    }
    
    public function testAddMetaDatum() : void
    {
        $key = 'key';
        $value = 'value';
        $this->meta_content->addMetaDatum($key, $value);
        $collection = $this->meta_content->getMetaData();
        
        $first_item = iterator_to_array($collection->getItems())[0];
        $this->assertInstanceOf(MetaDatum::class, $first_item);
        $this->assertEquals($key, $first_item->getKey());
        $this->assertEquals($value, $first_item->getValue());
    }
    
}
