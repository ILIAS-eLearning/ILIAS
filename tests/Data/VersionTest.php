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
        $this->assertFalse($l->isSmallerThan($r));
        $this->assertTrue($r->isSmallerThan($l));
        $this->assertFalse($r->isGreaterThan($l));
        $this->assertTrue($l->isGreaterThanOrEquals($r));
        $this->assertFalse($l->isSmallerThanOrEquals($r));
        $this->assertTrue($r->isSmallerThanOrEquals($l));
        $this->assertFalse($r->isGreaterThanOrEquals($l));
        $this->assertFalse($l->equals($r));
        $this->assertFalse($r->equals($l));
    }

    public function greaterThanProvider(): array
    {
        $f = new Data\Factory();
        return [
            'Patch version is greater (2>1), major and minor versions are equal (0)' => [$f->version("0.0.2"), $f->version("0.0.1")],
            'Minor version is greater (2>1), major and patch versions are equal (0)' => [$f->version("0.2.0"), $f->version("0.1.0")],
            'Major version is greater (2>1), minor and patch versions are equal (0)' => [$f->version("2.0.0"), $f->version("1.0.0")],
            'Patch version is greater (3>2), major (1) and minor (2) versions are equal' => [$f->version("1.2.3"), $f->version("1.2.2")],
            'Minor version is greater (2>1), major (1) and patch (3) versions are equal' => [$f->version("1.2.3"), $f->version("1.1.3")],
            'Major version is greater (1>0), minor (2) and patch (3) versions are equal' => [$f->version("1.2.3"), $f->version("0.2.3")],
            'Minor version is greater (5>1), patch is smaller (0<1), minor version is equal (1)' => [$f->version("1.5.0"), $f->version("1.1.1")],
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
        $this->assertTrue($l->isSmallerThanOrEquals($r));
        $this->assertTrue($r->isSmallerThanOrEquals($l));
        $this->assertTrue($r->isGreaterThanOrEquals($l));
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
