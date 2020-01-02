<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

class JSComponentMock
{
    use \ILIAS\UI\Implementation\Component\JavaScriptBindable;
}

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class JavaScriptBindableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock = new JSComponentMock();
    }

    public function test_withOnLoadCode()
    {
        $m = $this->mock->withOnLoadCode(function ($id) {
            return "Its me, $id!";
        });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(\Closure::class, $binder);
        $this->assertEquals("Its me, Mario!", $binder("Mario"));
    }

    public function test_withOnLoadCode_false_closure_1()
    {
        try {
            $this->mock->withOnLoadCode(function () {
            });
            $this->assertFalse("This should not happen...");
        } catch (\InvalidArgumentException $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_withOnLoadCode_false_closure_2()
    {
        try {
            $this->mock->withOnLoadCode(function ($id, $some_arg) {
            });
            $this->assertFalse("This should not happen...");
        } catch (\InvalidArgumentException $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_withAdditionalOnLoadCode()
    {
        $m = $this->mock
            ->withOnLoadCode(function ($id) {
                return "Its me, $id!";
            })
            ->withAdditionalOnLoadCode(function ($id) {
                return "And again, me: $id.";
            });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(\Closure::class, $binder);
        $this->assertEquals("Its me, Mario!\nAnd again, me: Mario.", $binder("Mario"));
    }

    public function test_withAdditionalOnLoadCode_no_previous()
    {
        $m = $this->mock
            ->withAdditionalOnLoadCode(function ($id) {
                return "And again, me: $id.";
            });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(\Closure::class, $binder);
        $this->assertEquals("And again, me: Mario.", $binder("Mario"));
    }
}
