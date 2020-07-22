<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Defines tests that a counter implementation should pass.
 */
class CounterTest extends ILIAS_UI_TestBase
{
    public function getCounterFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Counter\Factory();
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getCounterFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Counter\\Factory", $f);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Counter\\Counter", $f->status(0));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Counter\\Counter", $f->novelty(0));
    }

    /**
     * @dataProvider number_provider
     */
    public function test_status_counter($number)
    {
        $f = $this->getCounterFactory();

        $c = $f->status($number);

        $this->assertNotNull($c);
        $this->assertEquals(C\Counter\Counter::STATUS, $c->getType());
        $this->assertEquals($number, $c->getNumber());
    }

    /**
     * @dataProvider number_provider
     */
    public function test_novelty_counter($number)
    {
        $f = $this->getCounterFactory();

        $c = $f->novelty($number);

        $this->assertNotNull($c);
        $this->assertEquals(C\Counter\Counter::NOVELTY, $c->getType());
        $this->assertEquals($number, $c->getNumber());
    }

    public function test_known_counters_only()
    {
        try {
            new \ILIAS\UI\Implementation\Component\Counter\Counter("FOO", 1);
            $this->assertFalse("We should not get here");
        } catch (\InvalidArgumentException $e) {
        }
    }

    /**
     * @dataProvider no_number_provider
     */
    public function test_int_numbers_only($no_number)
    {
        $f = $this->getCounterFactory();

        try {
            $f->status($no_number);
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function number_provider()
    {
        return array( array(-13)
            , array(0)
            , array(23)
            , array(42)
            );
    }

    public function no_number_provider()
    {
        return array( array("foo")
            , array(9.1)
            , array(array())
            , array(new stdClass())
            );
    }

    public static $canonical_css_classes = array( "status" => "badge badge-notify il-counter-status"
        , "novelty" => "badge badge-notify il-counter-novelty"
        );

    /**
     * @dataProvider	counter_type_and_number_provider
     */
    public function test_render_status($type, $number)
    {
        $f = $this->getCounterFactory();
        $r = $this->getDefaultRenderer();
        $c = $f->$type($number);

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[$type];
        $expected = "<span class=\"$css_classes\">$number</span>";
        $this->assertEquals($expected, $html);
    }

    public function counter_type_and_number_provider()
    {
        return array( array("status", 42)
            , array("novelty", 13)
            , array("status", 1)
            , array("novelty", 23)
            );
    }
}
