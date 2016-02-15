<?php

require_once("libs/composer/vendor/autoload.php");

/**
 * Defines tests that a glyph implementation should pass.
 */
abstract class GlyphTest extends PHPUnit_Framework_TestCase {
    abstract public function getFactoryInstance();
    abstract public function getCounterFactoryInstance();
    
    /**
     * @dataProvider glyph_provider
     */
    public function test_implements_factory_interface($factory_method, $_) {
        $f = $this->getFactoryInstance();

        $this->assertInstanceOf("ILIAS\\UI\\Factory\\Glyph", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Element\\Glyph", $f->$factory_method());
    }

    /**
     * @dataProvider glyph_provider
     */
    public function test_glyph_types($factory_method, $type) {
        $f = $this->getFactoryInstance();
        $g = $f->$factory_method();

        $this->assertNotNull($g);
        $this->assertInstanceOf($type, $g->type());
    }

    /**
     * @dataProvider glyph_provider
     */
    public function test_no_counter($factory_method, $_) {
        $f = $this->getFactoryInstance();
        $cf = $this->getCounterFactoryInstance();
        $g = $f->$factory_method();
        $this->assertNotNull($g);

        $this->assertCount(0, $g->counters());
    }

    /**
     * @dataProvider glyph_provider
     */
    public function test_add_novelty_counter($factory_method, $_) {
        $f = $this->getFactoryInstance();
        $fc = $this->getCounterFactoryInstance();
        $g = $f->$factory_method();
        $this->assertNotNull($g);

        $c = $fc->novelty(0);
        $g2 = $g->addCounter($c);
        $this->assertNotNull($g2);
        $this->assertNotSame($g, $g2);
        $this->assertInstanceOf("ILIAS\\UI\\Element\\Glyph", $g2);
        $counters = $g2->counters();
        $this->assertCount(1, $counters);
        $this->assertContains($c, $counters); 
    }
 
    /**
     * @dataProvider glyph_provider
     */
    public function test_add_status_counter($factory_method, $_) {
        $f = $this->getFactoryInstance();
        $fc = $this->getCounterFactoryInstance();
        $g = $f->$factory_method();
        $this->assertNotNull($g);

        $c = $fc->status(0);
        $g2 = $g->addCounter($c);
        $this->assertNotNull($g2);
        $this->assertNotSame($g, $g2);
        $this->assertInstanceOf("ILIAS\\UI\\Element\\Glyph", $g2);
        $counters = $g2->counters();
        $this->assertCount(1, $counters);
        $this->assertContains($c, $counters);       
    }
 
    /**
     * @dataProvider glyph_provider
     */
    public function test_add_status_and_novelty_counter($factory_method, $_) {
        $f = $this->getFactoryInstance();
        $fc = $this->getCounterFactoryInstance();
        $g = $f->$factory_method();
        $this->assertNotNull($g);

        $c1 = $fc->status(0);
        $c2 = $fc->novelty(0);
        $g2 = $g->addCounter($c1)
                ->addCounter($c2);
        $this->assertNotNull($g2);
        $this->assertNotSame($g, $g2);
        $this->assertInstanceOf("ILIAS\\UI\\Element\\Glyph", $g2);
        $counters = $g2->counters();
        $this->assertCount(2, $counters);
        $this->assertContains($c1, $counters);  
        $this->assertContains($c2, $counters);  
    }

    /**
     * @dataProvider glyph_provider
     */
    public function test_two_counters_only($factory_method, $_) {
        $f = $this->getFactoryInstance();
        $fc = $this->getCounterFactoryInstance();
        $g = $f->$factory_method();
        $this->assertNotNull($g);

        $c1 = $fc->status(0);
        $c2 = $fc->novelty(0);
        $g2 = $g->addCounter($c1)
                ->addCounter($c2);

        $c1_n = $fc->status(0);
        $g3 = $g2->addCounter($c1_n);
        $counters = $g3->counters();
        $this->assertCount(2, $counters);
        $this->assertContains($c1_n, $counters); 
        $this->assertContains($c2, $counters); 
        $this->assertNotContains($c1, $counters); 

        $c2_n = $fc->novelty(0);
        $g3 = $g2->addCounter($c2_n);
        $counters = $g3->counters();
        $this->assertCount(2, $counters);
        $this->assertContains($c1, $counters); 
        $this->assertContains($c2_n, $counters); 
        $this->assertNotContains($c2, $counters); 
    }

    public function glyph_provider() {
        $ns = "ILIAS\\UI\\Element";
        return array
            ( array("up", "$ns\\UpGlyphType")
            , array("down", "$ns\\DownGlyphType")
            , array("add", "$ns\\AddGlyphType")
            , array("remove", "$ns\\RemoveGlyphType")
            , array("previous", "$ns\\PreviousGlyphType")
            , array("next", "$ns\\NextGlyphType")
            , array("calendar", "$ns\\CalendarGlyphType")
            , array("close", "$ns\\CloseGlyphType")
            , array("attachment", "$ns\\AttachmentGlyphType")
            , array("caret", "$ns\\CaretGlyphType")
            , array("drag", "$ns\\DragGlyphType")
            , array("search", "$ns\\SearchGlyphType")
            , array("filter", "$ns\\FilterGlyphType")
            , array("info", "$ns\\InfoGlyphType")
            );
    }

    public function amount_provider() {
        return array
            ( array(-13)
            , array(0)
            , array(23)
            , array(42)
            );
    }
}
