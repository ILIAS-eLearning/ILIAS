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

namespace ILIAS\Tests\Data\RFC;

use ILIAS\Data\Result;
use ILIAS\Data\RFC\Intermediate;
use PHPUnit\Framework\TestCase;

class IntermediateTest extends TestCase
{
    public function testConstruct() : void
    {
        $intermediate = new Intermediate('input');

        $this->assertInstanceOf(Intermediate::class, $intermediate);
    }

    public function testValue() : void
    {
        $intermediate = new Intermediate('input');

        $this->assertEquals(ord('i'), $intermediate->value());
    }

    public function testAccept() : void
    {
        $intermediate = new Intermediate('input');

        $this->assertEquals(ord('i'), $intermediate->value());

        $next = $intermediate->accept();
        $this->assertTrue($next->isOK());
        $this->assertEquals(ord('n'), $next->value()->value());
    }

    public function testReject() : void
    {
        $intermediate = new Intermediate('input');

        $next = $intermediate->reject();
        $this->assertFalse($next->isOK());
    }

    public function testAccepted() : void
    {
        $intermediate = new Intermediate('input');
        $this->assertEquals('', $intermediate->accepted());
        $this->assertEquals('in', $intermediate->accept()->value()->accept()->value()->accepted());
    }

    public function testDone() : void
    {
        $intermediate = new Intermediate('ab');
        $this->assertFalse($intermediate->done());
        $this->assertTrue($intermediate->accept()->value()->accept()->value()->done());
    }
}
