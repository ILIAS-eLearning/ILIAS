<?php

require_once("libs/composer/vendor/autoload.php");

/**
 * Defines tests that a counter implementation should pass.
 */
abstract class CounterTest extends PHPUnit_Framework_TestCase {
    abstract public function getFactoryInstance();

    public function setUp() {
        assert_options(ASSERT_WARNING, 0);
        assert_options(ASSERT_CALLBACK, null);
    }

    public function tearDown() {
        assert_options(ASSERT_WARNING, 1);
        assert_options(ASSERT_CALLBACK, null);
    }

    public function test_implements_factory_interface() {
        $f = $this->getFactoryInstance();

        $this->assertInstanceOf("ILIAS\\UI\\Factory\\Counter", $f);

        $this->assertInstanceOf("ILIAS\\UI\\Element\\Counter", $f->status(0));
        $this->assertInstanceOf("ILIAS\\UI\\Element\\Counter", $f->novelty(0));
    }

    /**
     * @dataProvider amount_provider
     */
    public function test_status_counter($amount) {
        $f = $this->getFactoryInstance();

        $c = $f->status($amount);

        $this->assertNotNull($c);
        $this->assertInstanceOf("ILIAS\\UI\\Element\\StatusCounterType", $c->type());
        $this->assertEquals($amount, $c->amount());
    }

    /**
     * @dataProvider amount_provider
     */
    public function test_novelty_counter($amount) {
        $f = $this->getFactoryInstance();

        $c = $f->novelty($amount);

        $this->assertNotNull($c);
        $this->assertInstanceOf("ILIAS\\UI\\Element\\NoveltyCounterType", $c->type());
        $this->assertEquals($amount, $c->amount());
    }

    /**
     * @dataProvider no_amount_provider
     */
    public function test_int_amounts_only($no_amount) {
        $f = $this->getFactoryInstance();
        $failed_assertions = 0;

        assert_options(ASSERT_CALLBACK, function() use (&$failed_assertions) {
            $failed_assertions++;
        });

        $f->status($no_amount);
        $f->novelty($no_amount);

        $this->assertEquals(2, $failed_assertions);
    }

    public function amount_provider() {
        return array
            ( array(-13)
            , array(0)
            , array(23)
            , array(42)
            );
    }

    public function no_amount_provider() {
        return array
            ( array("foo")
            , array(9.1)
            , array(array())
            , array(new stdClass())
            );
    }
}
