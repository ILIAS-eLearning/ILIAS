<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\HTML as H;
use Lechimp\Formlets\Internal\Values as V;
use Lechimp\Formlets\Internal\RenderDict;
use Lechimp\Formlets\Internal\NopBuilder;
use Lechimp\Formlets\Internal\TextBuilder;
use Lechimp\Formlets\Internal\CombinedBuilder;
use Lechimp\Formlets\Internal\TagBuilder;
use Lechimp\Formlets\Internal\TagBuilderCallbacks;

class TagBuildingMock implements TagBuilderCallbacks {
    public function __construct($attrs, $content) {
        $this->attrs = $attrs;
        $this->content = $content;
    } 

    public function getAttributes(RenderDict $dict, $name) {
        return $this->attrs;
    }

    public function getContent(RenderDict $dict, $name) {
        return $this->content;
    }
}

class BuilderTest extends PHPUnit_Framework_TestCase {
    public function testNopBuilder() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $b = new NopBuilder();
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $r1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $r2); 
    }

    public function testTextBuilder() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $b = new TextBuilder("foobar");
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $r1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $r2);

        $this->assertEquals($r1->render(), "foobar");
        $this->assertEquals($r2->render(), "foobar");
    } 

    public function testTagBuilder() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $m = new TagBuildingMock(array("foo" => "baz"), null);
        $b = new TagBuilder("span", $m);
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLTag", $r1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLTag", $r2);

        $this->assertEquals($r1->render(), "<span foo=\"baz\"/>");
        $this->assertEquals($r2->render(), "<span foo=\"baz\"/>");
    }

    public function testCombinedBuilder() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $b = new CombinedBuilder(new NopBuilder(), new NopBuilder());
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLArray", $r1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLArray", $r2);
    }

    public function testMappedBuilder() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $b = new TextBuilder("foobar");

        $rdict = null;
        $rhtml = null; 

        $transformation = V::fn(function ($dict, $html) use (&$rdict, &$rhtml) {
            $rdict = $dict;
            $rhtml = $html;
            return H::nop();
        });

        $b2 = $b->map($transformation);

        $r1 = $b2->build();
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $r1); 

        $r2 = $b2->buildWithDict($d);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict); 
        $this->assertEquals($d, $rdict); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $r2); 
    }

    public function testMappedTwiceBuilder() {
        $d = new RenderDict(array("foo" => "bar"), V::val(0));
        $b = new TextBuilder("foobar");

        $rdict1 = null;
        $rhtml1 = null; 
        $rdict2 = null;
        $rhtml2 = null; 

        $transformation = V::fn(function ($dict, $html) use (&$rdict1, &$rhtml1) {
            $rdict1 = $dict;
            $rhtml1 = $html;
            return H::nop();
        });

        $transformation2 = V::fn(function ($dict, $html) use (&$rdict2, &$rhtml2) {
            $rdict2 = $dict;
            $rhtml2 = $html;
            return H::text("baz");
        });

        $b2 = $b->map($transformation)->map($transformation2);

        $r1 = $b2->build();
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict2); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $r1); 
        $this->assertEquals($r1->render(), "baz"); 

        $r2 = $b2->buildWithDict($d);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\RenderDict", $rdict2); 
        $this->assertEquals($d, $rdict1); 
        $this->assertEquals($d, $rdict2); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $rhtml1); 
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("Lechimp\Formlets\Internal\HTMLText", $r2);
        $this->assertEquals($r2->render(), "baz");
    }
}
