<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on button implementation.
 */
class ListingTest extends ILIAS_UI_TestBase
{

    /**
     * @return \ILIAS\UI\Implementation\Component\Listing\Factory
     */
    public function getListingFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Listing\Factory();
    }


    public function test_implements_factory_interface()
    {
        $f = $this->getListingFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Listing\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Listing\\Ordered",
            $f->ordered(array("1"))
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Listing\\Unordered",
            $f->unordered(array("1"))
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Listing\\Descriptive",
            $f->descriptive(array("k1"=>"c1"))
        );
    }


    public function test_ordered_get_items()
    {
        $f = $this->getListingFactory();
        $l = $f->ordered(array("1","2"));

        $items = array("1","2");
        $this->assertEquals($l->getItems(), $items);
    }

    public function test_unordered_get_items()
    {
        $f = $this->getListingFactory();
        $l = $f->unordered(array("1","2"));

        $items = array("1","2");
        $this->assertEquals($l->getItems(), $items);
    }

    public function test_descriptive_get_items()
    {
        $f = $this->getListingFactory();
        $l = $f->descriptive(array("k1"=>"c1","k2"=>"c2"));

        $items = array("k1"=>"c1","k2"=>"c2");
        $this->assertEquals($l->getItems(), $items);
    }

    public function test_ordered_with_items()
    {
        $f = $this->getListingFactory();
        $l = $f->ordered(array())->withItems(array("1","2"));

        $items = array("1","2");
        $this->assertEquals($l->getItems(), $items);
    }

    public function test_unordered_with_items()
    {
        $f = $this->getListingFactory();
        $l = $f->unordered(array())->withItems(array("1","2"));

        $items = array("1","2");
        $this->assertEquals($l->getItems(), $items);
    }

    public function test_descriptive_with_items()
    {
        $f = $this->getListingFactory();
        $l = $f->descriptive(array())->withItems(array("k1"=>"c1","k2"=>"c2"));

        $items = array("k1"=>"c1","k2"=>"c2");
        $this->assertEquals($l->getItems(), $items);
    }


    public function test_render_ordered_listing()
    {
        $f = $this->getListingFactory();
        $r = $this->getDefaultRenderer();
        $l = $f->ordered(array("1","2"));

        $html = $this->normalizeHTML($r->render($l));

        $expected = "<ol>" .
                    "\t\t<li>1</li>" .
                    "\t\t<li>2</li>\t" .
                    "</ol>";

        $this->assertEquals($expected, $html);
    }

    public function test_descriptive_invalid_items2()
    {
        $f = $this->getListingFactory();

        try {
            $f->descriptive(array("1"));
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(get_class($e), "InvalidArgumentException");
        }
    }

    public function test_descriptive_invalid_items3()
    {
        $f = $this->getListingFactory();

        try {
            $f->descriptive(array("1","1"));
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(get_class($e), "InvalidArgumentException");
        }
    }


    public function test_render_unordered_listing()
    {
        $f = $this->getListingFactory();
        $r = $this->getDefaultRenderer();
        $l = $f->unordered(array("1","2"));

        $html = $this->normalizeHTML($r->render($l));

        $expected = "<ul>" .
                "\t\t<li>1</li>" .
                "\t\t<li>2</li>\t" .
                "</ul>";

        $this->assertEquals($expected, $html);
    }

    public function test_render_descriptive_listing()
    {
        $f = $this->getListingFactory();
        $r = $this->getDefaultRenderer();
        $l = $f->descriptive(array("k1"=>"c1","k2"=>"c2"));

        $html = $this->normalizeHTML($r->render($l));

        $expected = "<dl>" .
                "\t\t<dt>k1</dt>" .
                "\t<dd>c1</dd>" .
                "\t\t<dt>k2</dt>" .
                "\t<dd>c2</dd>\t" .
                "</dl>";

        $this->assertEquals($expected, $html);
    }
}
