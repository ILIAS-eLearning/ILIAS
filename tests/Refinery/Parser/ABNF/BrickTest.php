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

use Closure;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\Parser\ABNF\Brick;
use ILIAS\Refinery\Parser\ABNF\Intermediate;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

class BrickTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Brick::class, new Brick());
    }

    public function testApply(): void
    {
        $expected = 'aloha';
        $ok = $this->getMockBuilder(Result::class)->getMock();
        $ok->method('isOk')->willReturn(true);
        $ok->method('value')->willReturn($expected);
        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->method('done')->willReturn(true);
        $intermediate->method('transform')->willReturnCallback(fn () => $ok);
        $brick = new Brick();
        $result = $brick->apply(static fn (Intermediate $x, Closure $cc): Result => (
            $cc(new Ok($intermediate))
        ), 'abcde');

        $this->assertTrue($result->isOk());
        $this->assertEquals($expected, $result->value());
    }

    public function testThatAllInputMustBeConsumed(): void
    {
        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->expects(self::once())->method('done')->willReturn(false);
        $intermediate->expects(self::never())->method('transform');

        $brick = new Brick();
        $result = $brick->apply(static fn (Intermediate $x, Closure $cc): Result => (
            $cc(new Ok($intermediate))
        ), 'abcde');

        $this->assertFalse($result->isOk());
    }

    public function testAFailingParser(): void
    {
        $brick = new Brick();
        $result = $brick->apply(static fn (Intermediate $x, Closure $cc): Result => (
            $cc(new Error('something happened'))
        ), 'abcde');

        $this->assertFalse($result->isOk());
    }

    public function testToTransformation(): void
    {
        $brick = new Brick();
        $parser = $brick->sequence(['foo']);
        $transformation = $brick->toTransformation($parser);
        $this->assertInstanceOf(Transformation::class, $transformation);
        $result = $transformation->applyTo(new Ok('foo'));
        $this->assertTrue($result->isOk());
        $this->assertEquals('foo', $result->value());
    }

    public function testToTransformationFailed(): void
    {
        $brick = new Brick();
        $parser = $brick->sequence(['foo']);
        $transformation = $brick->toTransformation($parser);
        $this->assertInstanceOf(Transformation::class, $transformation);
        $result = $transformation->applyTo(new Ok('fox'));
        $this->assertFalse($result->isOk());
    }

    public function testRange(): void
    {
        $brick = new Brick();

        $parse = $brick->range(0, 0x14);

        foreach (array_fill(0, 0x14 + 1, null) as $i => $_) {
            $result = $brick->apply($parse, chr($i));
            $this->assertTrue($result->isOk());
            $this->assertEquals(chr($i), $result->value());
        }
    }

    public function testOutOfRange(): void
    {
        $brick = new Brick();

        $parse = $brick->range(1, 0x14);

        $this->assertFalse($brick->apply($parse, "\x0")->isOk());
        $this->assertFalse($brick->apply($parse, "\x15")->isOk());
    }

    public function testEither(): void
    {
        $brick = new Brick();

        $parse = $brick->either([
            'a' => $brick->range(0x10, 0x20), // False.
            $brick->range(0x21, 0x30), // False without key.
            'b' => $brick->range(0x0, 0x5), // True.
        ]);

        $result = $brick->apply($parse, "\x0");

        $this->assertTrue($result->isOk());
        $this->assertEquals("\x0", $result->value()['b']);
    }

    public function testSequence(): void
    {
        $brick = new Brick();

        $parse = $brick->sequence([
            'first' => $brick->range(ord('a'), ord('b')),
            'second' => $brick->range(ord('c'), ord('d')),
        ]);

        $result = $brick->apply($parse, 'ad');

        $this->assertTrue($result->isOk());
        $this->assertEquals('a', $result->value()['first']);
        $this->assertEquals('d', $result->value()['second']);
    }

    /**
     * @dataProvider repeatProvider
     */
    public function testRepeat(int $min, ?int $max, array $succeed, array $fail): void
    {
        $brick = new Brick();
        $parse = $brick->repeat($min, $max, $brick->range(ord('a'), ord('z')));

        foreach ($succeed as $input) {
            $result = $brick->apply($parse, $input);

            $this->assertTrue($result->isOk());
            $this->assertEquals($input, $result->value());
        }

        foreach ($fail as $input) {
            $result = $brick->apply($parse, $input);
            $this->assertFalse($result->isOk());
        }
    }

    public function repeatProvider(): array
    {
        return [
            'Ranges are inclusive' => [3, 3, ['abc'], ['ab', 'abcd']],
            'Null is used for infinity' => [0, null, ['', 'abcdefghijklmop'], []],
            'Minimum is 0' => [-1, 3, ['', 'a', 'ab', 'abc'], ['abcd']],
            'Minimum of the end range is the start range' => [3, 2, ['abc'], ['ab', 'abcd']],
        ];
    }

    /**
     * @dataProvider characterProvider
     */
    public function testCharacters(string $method, string $input, bool $isOk): void
    {
        $brick = new Brick();

        $parse = $brick->either(str_split($input)); // No character is allowed to pass.
        if ($isOk) {
            $parse = $brick->repeat(0, null, $brick->$method()); // All characters must pass.
        }

        $result = $brick->apply($parse, $input);

        $this->assertEquals($isOk, $result->isOk());
        if ($isOk) {
            $this->assertEquals($input, $result->value());
        }
    }

    private function breakIntoPieces(int $x, string $break_me): array
    {
        $len = (int) floor(strlen($break_me) / $x);

        return array_map(
            fn ($i) => substr($break_me, $i * $len, $len),
            range(0, $x - !(strlen($break_me) % $x))
        );
    }

    public function characterProvider(): array
    {
        $alpha = array_fill(ord('a'), ord('z') - ord('a') + 1, '');
        array_walk($alpha, function (&$value, int $i) {
            $value = chr($i);
        });
        $alpha = implode('', $alpha);
        $alpha .= strtoupper($alpha);

        // Circumvent error when running this test with Xdebug. The default value of xdebug.max_nesting_level will kill the test.
        $alpha_parts = $this->breakIntoPieces(3, $alpha);

        $digits = '1234567890';

        return [
            'Accepts all digits.' => ['digit', $digits, true],
            'Accepts no characters from a-z or A-Z.' => ['digit', $alpha, false],
            'Accepts characters from a-z and A-Z (Part 1).' => ['alpha', $alpha_parts[0], true],
            'Accepts characters from a-z and A-Z (Part 2).' => ['alpha', $alpha_parts[1], true],
            'Accepts characters from a-z and A-Z (Part 3).' => ['alpha', $alpha_parts[2], true],
            'Accepts no digits.' => ['alpha', $digits, false],
        ];
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testEmptyString(string $input, bool $isOk): void
    {
        $brick = new Brick();

        $parse = $brick->sequence(['']);
        $result = $brick->apply($parse, $input);

        $this->assertEquals($isOk, $result->isOk());
    }

    public function emptyStringProvider(): array
    {
        return [
            'Test empty input' => ['', true],
            'Test non empty input' => ['x', false],
        ];
    }

    public function testTransfromation(): void
    {
        $brick = new Brick();

        $intermediate = $this->getMockBuilder(Intermediate::class)->disableOriginalConstructor()->getMock();
        $intermediate->method('done')->willReturn(true);

        $ok = $this->getMockBuilder(Result::class)->getMock();
        $ok->method('isOk')->willReturn(true);
        $ok->method('value')->willReturn($intermediate);
        $ok->method('map')->willReturn($ok);
        $ok->method('then')->willReturn($ok);
        $ok->method('except')->willReturn($ok);

        $transformation = $this->getMockBuilder(Transformation::class)->getMock();
        $transformation->expects(self::once())->method('applyTo')->willReturn($ok);

        $parser = $brick->transformation($transformation, fn ($x, $cc) => $cc(new Ok($x)));

        $result = $brick->apply($parser, 'a');

        $this->assertEquals($ok, $result);
    }

    private function string(): Closure
    {
        return static function (string $string): Ok {
            return new Ok($string);
        };
    }
}
