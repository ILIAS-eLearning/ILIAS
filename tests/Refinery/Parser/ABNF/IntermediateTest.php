<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\Parser\ABNF;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\Parser\ABNF\Intermediate;
use ILIAS\Refinery\Parser\ABNF\Character;
use PHPUnit\Framework\TestCase;
use stdClass;

class IntermediateTest extends TestCase
{
    public function testConstruct(): void
    {
        $intermediate = new Intermediate('input');

        $this->assertInstanceOf(Intermediate::class, $intermediate);
    }

    public function testValue(): void
    {
        $intermediate = new Intermediate('input');

        $this->assertEquals(ord('i'), $intermediate->value());
    }

    /**
     * @depends testValue
     */
    public function testAccept(): void
    {
        $intermediate = new Intermediate('input');

        $this->assertEquals(ord('i'), $intermediate->value());

        $next = $intermediate->accept();
        $this->assertTrue($next->isOK());
        $this->assertEquals(ord('n'), $next->value()->value());
    }

    public function testReject(): void
    {
        $intermediate = new Intermediate('input');

        $next = $intermediate->reject();
        $this->assertFalse($next->isOK());
    }

    /**
     * @depends testValue
     * @depends testAccept
     */
    public function testAccepted(): void
    {
        $expected = 'in';
        $intermediate = new Intermediate('input');
        $this->assertEquals([], $intermediate->accepted());
        $accepted = $intermediate->accept()->value()->accept()->value()->accepted();
        $this->assertTrue(is_array($accepted));
        $actual = '';
        foreach ($accepted as $character) {
            $this->assertInstanceOf(Character::class, $character);
            $actual .= $character->value();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * @depends testAccept
     */
    public function testDone(): void
    {
        $intermediate = new Intermediate('ab');
        $this->assertFalse($intermediate->done());
        $this->assertTrue($intermediate->accept()->value()->accept()->value()->done());
    }

    /**
     * @depends testAccepted
     * @depends testAccepted
     * @depends testValue
     */
    public function testOnlyTodo(): void
    {
        $intermediate = new Intermediate('ab');
        $intermediate = $intermediate->accept()->value()->onlyTodo();
        $this->assertEmpty($intermediate->accepted());
        $this->assertEquals(ord('b'), $intermediate->value());
    }

    /**
     * @depends testAccept
     */
    public function testTransformOnlyWithCharacters(): void
    {
        $intermediate = new Intermediate('hej');
        $intermediate = $intermediate->accept()->value();

        $ok = new Ok('transformed');

        $result = $intermediate->transform(function (string $accepted) use ($ok): Result {
            $this->assertEquals('h', $accepted);
            return $ok;
        });

        $this->assertEquals($ok, $result);
    }

    /**
     * @depends testAccepted
     */
    public function testPush(): void
    {
        $intermediate = new Intermediate('hej');
        $value = new stdClass();
        $this->assertEmpty($intermediate->accepted());
        $this->assertEquals([$value], $intermediate->push([$value])->accepted());
    }

    /**
     * @depends testPush
     * @depends testAccept
     */
    public function testTransformWithTransformedValues(): void
    {
        $intermediate = new Intermediate('hej');
        $intermediate = $intermediate->accept()->value();

        $dummy = new stdClass();
        $ok = new Ok('transformed');

        $intermediate = $intermediate->push([$dummy]);

        $result = $intermediate->transform(function (array $accepted) use ($dummy, $ok): Result {
            $this->assertEquals(2, count($accepted));
            $this->assertInstanceOf(Character::class, $accepted[0]);
            $this->assertEquals('h', $accepted[0]->value());
            $this->assertEquals($dummy, $accepted[1]);
            return $ok;
        });

        $this->assertEquals($ok, $result);
    }
}
