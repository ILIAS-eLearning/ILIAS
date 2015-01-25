<?php

require_once("formlets/html.php");
require_once("formlets/builders.php");

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
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $b = new NopBuilder();
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("HTMLNop", $r1); 
        $this->assertInstanceOf("HTMLNop", $r2); 
    }

    public function testTextBuilder() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $b = new TextBuilder("foobar");
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("HTMLText", $r1); 
        $this->assertInstanceOf("HTMLText", $r2);

        $this->assertEquals($r1->render(), "foobar");
        $this->assertEquals($r2->render(), "foobar");
    } 

    public function testTagBuilder() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $m = new TagBuildingMock(array("foo" => "baz"), null);
        $b = new TagBuilder("span", $m);
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("HTMLTag", $r1); 
        $this->assertInstanceOf("HTMLTag", $r2);

        $this->assertEquals($r1->render(), "<span foo=\"baz\"/>");
        $this->assertEquals($r2->render(), "<span foo=\"baz\"/>");
    }

    public function testCombinedBuilder() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $b = new CombinedBuilder(new NopBuilder(), new NopBuilder());
    
        $r1 = $b->build();
        $r2 = $b->buildWithDict($d);   

        $this->assertInstanceOf("HTMLArray", $r1); 
        $this->assertInstanceOf("HTMLArray", $r2);
    }

    public function testMappedBuilder() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $b = new TextBuilder("foobar");

        $rdict = null;
        $rhtml = null; 

        $transformation = _fn(function ($dict, $html) use (&$rdict, &$rhtml) {
            $rdict = $dict;
            $rhtml = $html;
            return html_nop();
        });

        $b2 = $b->map($transformation);

        $r1 = $b2->build();
        $this->assertInstanceOf("RenderDict", $rdict); 
        $this->assertInstanceOf("HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("HTMLNop", $r1); 

        $r2 = $b2->buildWithDict($d);
        $this->assertInstanceOf("RenderDict", $rdict); 
        $this->assertEquals($d, $rdict); 
        $this->assertInstanceOf("HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("HTMLNop", $r2); 
    }

    public function testMappedTwiceBuilder() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $b = new TextBuilder("foobar");

        $rdict1 = null;
        $rhtml1 = null; 
        $rdict2 = null;
        $rhtml2 = null; 

        $transformation = _fn(function ($dict, $html) use (&$rdict1, &$rhtml1) {
            $rdict1 = $dict;
            $rhtml1 = $html;
            return html_nop();
        });

        $transformation2 = _fn(function ($dict, $html) use (&$rdict2, &$rhtml2) {
            $rdict2 = $dict;
            $rhtml2 = $html;
            return html_text("baz");
        });

        $b2 = $b->map($transformation)->map($transformation2);

        $r1 = $b2->build();
        $this->assertInstanceOf("RenderDict", $rdict1); 
        $this->assertInstanceOf("RenderDict", $rdict2); 
        $this->assertInstanceOf("HTMLText", $rhtml1); 
        $this->assertInstanceOf("HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("HTMLText", $r1); 
        $this->assertEquals($r1->render(), "baz"); 

        $r2 = $b2->buildWithDict($d);
        $this->assertInstanceOf("RenderDict", $rdict1); 
        $this->assertInstanceOf("RenderDict", $rdict2); 
        $this->assertEquals($d, $rdict1); 
        $this->assertEquals($d, $rdict2); 
        $this->assertInstanceOf("HTMLText", $rhtml1); 
        $this->assertInstanceOf("HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("HTMLText", $r2);
        $this->assertEquals($r2->render(), "baz");
    }
}

