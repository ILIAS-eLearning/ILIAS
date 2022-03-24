<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;

require_once("libs/composer/vendor/autoload.php");

class JSComponentMock implements \ILIAS\UI\Component\JavaScriptBindable
{
    use JavaScriptBindable;
    use ComponentHelper;
}

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class JavaScriptBindableTest extends TestCase
{
    protected JSComponentMock $mock;

    public function setUp() : void
    {
        $this->mock = new JSComponentMock();
    }

    public function test_withOnLoadCode() : void
    {
        $m = $this->mock->withOnLoadCode(function ($id) {
            return "Its me, $id!";
        });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(Closure::class, $binder);
        $this->assertEquals("Its me, Mario!", $binder("Mario"));
    }

    public function test_withOnLoadCode_false_closure_1() : void
    {
        try {
            $this->mock->withOnLoadCode(function () : void {
            });
            $this->assertFalse("This should not happen...");
        } catch (InvalidArgumentException $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_withOnLoadCode_false_closure_2() : void
    {
        try {
            $this->mock->withOnLoadCode(function ($id, $some_arg) : void {
            });
            $this->assertFalse("This should not happen...");
        } catch (InvalidArgumentException $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_withAdditionalOnLoadCode() : void
    {
        $m = $this->mock
            ->withOnLoadCode(function ($id) {
                return "Its me, $id!";
            })
            ->withAdditionalOnLoadCode(function ($id) {
                return "And again, me: $id.";
            });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(Closure::class, $binder);
        $this->assertEquals("Its me, Mario!\nAnd again, me: Mario.", $binder("Mario"));
    }

    public function test_withAdditionalOnLoadCode_no_previous() : void
    {
        $m = $this->mock
            ->withAdditionalOnLoadCode(function ($id) {
                return "And again, me: $id.";
            });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(Closure::class, $binder);
        $this->assertEquals("And again, me: Mario.", $binder("Mario"));
    }
}
