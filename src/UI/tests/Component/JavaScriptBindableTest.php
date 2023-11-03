<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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

    public function setUp(): void
    {
        $this->mock = new JSComponentMock();
    }

    public function testWithOnLoadCode(): void
    {
        $m = $this->mock->withOnLoadCode(function ($id) {
            return "Its me, $id!";
        });

        $binder = $m->getOnLoadCode();
        $this->assertInstanceOf(Closure::class, $binder);
        $this->assertEquals("Its me, Mario!", $binder("Mario"));
    }

    public function testWithOnLoadCodeFalseClosure1(): void
    {
        try {
            $this->mock->withOnLoadCode(function (): void {
            });
            $this->assertFalse("This should not happen...");
        } catch (InvalidArgumentException $exception) {
            $this->assertTrue(true);
        }
    }

    public function testWithOnLoadCodeFalseClosure2(): void
    {
        try {
            $this->mock->withOnLoadCode(function ($id, $some_arg): void {
            });
            $this->assertFalse("This should not happen...");
        } catch (InvalidArgumentException $exception) {
            $this->assertTrue(true);
        }
    }

    public function testWithAdditionalOnLoadCode(): void
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

    public function testWithAdditionalOnLoadCodeNoPrevious(): void
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
