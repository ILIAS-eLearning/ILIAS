<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;

class ArrayEnvironmentTest extends TestCase
{
    /**
     * @var Setup\ArrayEnvironment
     */
    protected $environment;

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
}
