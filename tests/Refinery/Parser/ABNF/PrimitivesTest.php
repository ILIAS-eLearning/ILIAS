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

namespace ILIAS\Tests\Refinery\Parser\ABNF;

use Closure;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\Parser\ABNF\Intermediate;
use ILIAS\Refinery\Parser\ABNF\Primitives;
use PHPUnit\Framework\TestCase;
use Exception;

class PrimitivesTest extends TestCase
{
    public function testSimpleEither(): void
    {
        $primitives = new Primitives();

        $parse = $primitives->simpleEither([
            'a', // False.
            "\x0", // True.
        ]);

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->expects(self::exactly(2))->method('value')->willReturn(ord("\x0"));
        $intermediate->method('accept')->willReturn(new Ok($intermediate));
        $intermediate->method('reject')->willReturn(new Error('Failed'));

        $result = $parse($intermediate, static fn (Result $x): Result => $x);

        $this->assertTrue($result->isOk());
        $this->assertEquals($intermediate, $result->value());
    }

    public function testSimpleSequence(): void
    {
        $primitives = new Primitives();

        $parse = $primitives->simpleSequence(['a', 'd']);

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->expects(self::exactly(2))->method('value')->willReturnOnConsecutiveCalls(ord('a'), ord('d'));
        $intermediate->method('accept')->willReturn(new Ok($intermediate));

        $result = $parse($intermediate, static fn (Result $x): Result => $x);

        $this->assertTrue($result->isOk());
        $this->assertEquals($intermediate, $result->value());
    }

    public function testUntilZero(): void
    {
        $primitives = new Primitives();
        $parser = $primitives->until(0, static function (): void {
            throw new Exception('Should not be called.');
        });

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();

        $result = $parser($intermediate, static fn (Result $x): Result => $x);
        $this->assertTrue($result->isOk());
        $this->assertEquals($intermediate, $result->value());
    }

    public function testUntilN(): void
    {
        $n = 8;
        foreach (array_fill(0, $n + 1, null) as $i => $_) {
            $primitives = new Primitives();
            $called = 0;
            $end_called = 0;
            $parser = $primitives->until($n, static function (Intermediate $x, Closure $cc) use (&$called): Result {
                $called++;
                return $cc(new Ok($x));
            });

            $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();

            $result = $parser($intermediate, static function (Result $x) use (&$end_called, $i): Result {
                $end_called++;
                return $end_called <= $i ? new Error('Failed') : $x;
            });
            if ($i > $n) {
                $this->assertEquals($i, $n);
                $this->assertFalse($result->isOk());
            } else {
                $this->assertEquals($i, $called);
                $this->assertEquals($i + 1, $end_called);
                $this->assertTrue($result->isOk());
                $this->assertEquals($intermediate, $result->value());
            }
        }
    }

    public function testUntilSuccess(): void
    {
        $success_after = 20;
        $primitives = new Primitives();
        $called = 0;
        $end_called = 0;
        $parser = $primitives->until(null, static function (Intermediate $x, Closure $cc) use (&$called): Result {
            $called++;
            return $cc(new Ok($x));
        });

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();

        $result = $parser($intermediate, static function (Result $x) use (&$end_called, $success_after): Result {
            $end_called++;
            return $end_called <= $success_after ? new Error('Failed') : $x;
        });
        $this->assertEquals($success_after, $called);
        $this->assertEquals($success_after + 1, $end_called);
        $this->assertTrue($result->isOk());
        $this->assertEquals($intermediate, $result->value());
    }

    public function testUntilChildFails(): void
    {
        $fail_after = 20;
        $primitives = new Primitives();
        $called = 0;
        $end_called = 0;
        $parser = $primitives->until(null, static function (Intermediate $x, Closure $cc) use (&$called, $fail_after): Result {
            $called++;
            return $called <= $fail_after ? $cc(new Ok($x)) : $cc(new Error('Failed.'));
        });

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();

        $result = $parser($intermediate, static function (Result $x) use (&$end_called): Result {
            $end_called++;
            return new Error('Failed.');
        });
        $this->assertEquals($fail_after + 1, $called);
        $this->assertEquals(($fail_after + 1) * 2, $end_called);
        $this->assertFalse($result->isOk());
    }

    public function testParserFromParser(): void
    {
        $primitives = new Primitives();

        $parser = static function (): void {
            throw new Exception('Should not be called.');
        };

        $this->assertEquals($parser, $primitives->parserFrom($parser));
    }

    public function testParserFromString(): void
    {
        $primitives = new Primitives();

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->method('value')->willReturnOnConsecutiveCalls(ord('h'), ord('e'), ord('l'), ord('l'), ord('o'));
        $intermediate->method('accept')->willReturn(new Ok($intermediate));

        $parser = $primitives->parserFrom('hello');

        $result = $parser($intermediate, static fn (Result $x): Result => $x);
        $this->assertTrue($result->isOk());
    }

    public function testParserFromEmptyString(): void
    {
        $primitives = new Primitives();

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->expects(self::never())->method('value');
        $intermediate->expects(self::never())->method('accept');
        $intermediate->expects(self::never())->method('reject');

        $parser = $primitives->parserFrom('');

        $result = $parser($intermediate, fn ($x) => $x);
        $this->assertTrue($result->isOk());
        $this->assertEquals($intermediate, $result->value());
    }
}
