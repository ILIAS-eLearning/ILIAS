<?php declare(strict_types=1);

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
 
namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;

class ArrayEnvironmentTest extends TestCase
{
    protected Setup\ArrayEnvironment $environment;

    public function setUp() : void
    {
        $this->environment = new Setup\ArrayEnvironment([
            "foo" => "FOO",
            "bar" => "BAR"
        ]);
    }

    public function testGetResource() : void
    {
        $this->assertEquals("FOO", $this->environment->getResource("foo"));
        $this->assertEquals("BAR", $this->environment->getResource("bar"));
        $this->assertNull($this->environment->getResource("baz"));
    }

    public function testWithResource() : void
    {
        $env = $this->environment->withResource("baz", "BAZ");

        $this->assertEquals("FOO", $env->getResource("foo"));
        $this->assertEquals("BAR", $env->getResource("bar"));
        $this->assertEquals("BAZ", $env->getResource("baz"));
    }

    public function testSetResourceRejectsDuplicates() : void
    {
        $this->expectException(\RuntimeException::class);

        $env = $this->environment->withResource("baz", "BAZ");
        $env->withResource("baz", "BAZ");
    }

    public function testConfigFor() : void
    {
        $env = $this->environment->withConfigFor("foo", "BAR");
        $this->assertEquals("BAR", $env->getConfigFor("foo"));
    }

    public function testDuplicateConfigFor() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->environment
            ->withConfigFor("foo", "BAR")
            ->withConfigFor("foo", "BAZ")
        ;
    }

    public function testWrongConfigId() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->environment->getConfigFor("foofoo");
    }

    public function testHasConfigFor() : void
    {
        $env = $this->environment->withConfigFor("foo", "BAR");
        $this->assertTrue($env->hasConfigFor("foo"));
        $this->assertFalse($env->hasConfigFor("bar"));
    }
}
