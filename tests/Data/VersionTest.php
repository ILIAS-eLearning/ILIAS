<?php

declare(strict_types=1);

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->f = new Data\Factory();
    }

    public function testSmoke(): void
    {
        $v = $this->f->version("0.1.0");

        $this->assertEquals("0.1.0", (string) $v);
    }

    public function testMajorOnly(): void
    {
        $v = $this->f->version("1");

        $this->assertEquals("1.0.0", (string) $v);
    }

    public function testNoPatchVersion(): void
    {
        $v = $this->f->version("1.2");

        $this->assertEquals("1.2.0", (string) $v);
    }

    public function testSubVersions(): void
    {
        $v = $this->f->version("1.2.3");

        $this->assertEquals(1, $v->getMajor());
        $this->assertEquals(2, $v->getMinor());
        $this->assertEquals(3, $v->getPatch());
    }

    /**
     * @dataProvider greaterThanProvider
     */
    public function testGreaterThan(Data\Version $l, Data\Version $r): void
    {
        $this->assertTrue($l->isGreaterThan($r));
        $this->assertTrue($r->isSmallerThan($l));
        $this->assertTrue($l->isGreaterThanOrEquals($r));
        $this->assertTrue($r->isSmallerThanOrEquals($l));
        $this->assertFalse($l->equals($r));
        $this->assertFalse($r->equals($l));
    }

    public function greaterThanProvider(): array
    {
        $f = new Data\Factory();
        return [
            [$f->version("0.0.2"), $f->version("0.0.1")],
            [$f->version("0.2.0"), $f->version("0.1.0")],
            [$f->version("2.0.0"), $f->version("1.0.0")],
            [$f->version("1.2.3"), $f->version("1.2.2")],
            [$f->version("1.2.3"), $f->version("1.1.3")],
            [$f->version("1.2.3"), $f->version("0.2.3")]
        ];
    }

    /**
     * @dataProvider equalsProvider
     */
    public function testEquals(Data\Version $l, Data\Version $r): void
    {
        $this->assertFalse($l->isGreaterThan($r));
        $this->assertFalse($r->isSmallerThan($l));
        $this->assertTrue($l->isGreaterThanOrEquals($r));
        $this->assertTrue($r->isSmallerThanOrEquals($l));
        $this->assertTrue($l->equals($r));
        $this->assertTrue($r->equals($l));
    }

    public function equalsProvider(): array
    {
        $f = new Data\Factory();
        return [
            [$f->version("0.0.1"), $f->version("0.0.1")],
            [$f->version("0.1.0"), $f->version("0.1.0")],
            [$f->version("1.0.0"), $f->version("1.0.0")],
            [$f->version("1.0.3"), $f->version("1.0.3")],
            [$f->version("1.2.0"), $f->version("1.2.0")],
            [$f->version("1.2.3"), $f->version("1.2.3")]
        ];
    }
}
