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
use ILIAS\Data\RFC\Brick;
use ILIAS\Data\RFC\Intermediate;
use PHPUnit\Framework\TestCase;

class BrickTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(Brick::class, new Brick());
    }

    public function testRange() : void
    {
        $intermediate = new Intermediate("\x0");

        $brick = new Brick();

        $parse = $brick->range(0, 20);

        $result = $brick->apply($parse, $intermediate);

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(Intermediate::class, $result->value());
        $this->assertEquals("\x0", $result->value()->accepted());
        $this->assertTrue($result->value()->done());
    }

    public function testEither() : void
    {
        $intermediate = new Intermediate("\x0");

        $brick = new Brick();

        $parse = $brick->either([
            $brick->range(0x10, 0x20), // False.
            $brick->range(0x0, 0x5), // True.
        ]);

        $result = $brick->apply($parse, $intermediate);

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(Intermediate::class, $result->value());
        $this->assertEquals("\x0", $result->value()->accepted());
        $this->assertTrue($result->value()->done());
    }

    public function testSequence() : void
    {
        $intermediate = new Intermediate('ad');

        $brick = new Brick();

        $parse = $brick->sequence([
            $brick->range(ord('a'), ord('b')),
            $brick->range(ord('c'), ord('d')),
        ]);

        $result = $brick->apply($parse, $intermediate);

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(Intermediate::class, $result->value());
        $this->assertEquals('ad', $result->value()->accepted());
        $this->assertTrue($result->value()->done());
    }

    /**
     * @dataProvider repeatProvider
     */
    public function testRepeat(int $min, ?int $max, array $succeed, array $fail) : void
    {
        $brick = new Brick();
        $parse = $brick->repeat($min, $max, $brick->range(ord('a'), ord('z')));

        foreach ($succeed as $input) {
            $result = $brick->apply($parse, new Intermediate($input));

            $this->assertTrue($result->isOk());
            $this->assertInstanceOf(Intermediate::class, $result->value());
            $this->assertEquals($input, $result->value()->accepted());
            $this->assertTrue($result->value()->done());
        }

        foreach ($fail as $input) {
            $result = $brick->apply($parse, new Intermediate($input));
            $this->assertFalse($result->isOk());
        }
    }

    public function repeatProvider() : array
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
    public function testCharacters(string $method, string $input, bool $isOk) : void
    {
        $intermediate = new Intermediate($input);
        $brick = new Brick();

        $parse = $brick->either(str_split($input)); // No character is allowed to pass.
        if ($isOk) {
            $parse = $brick->repeat(0, null, $brick->$method()); // All characters must pass.
        }

        $result = $brick->apply($parse, $intermediate);

        $this->assertEquals($isOk, $result->isOk());
        if ($isOk) {
            $this->assertInstanceOf(Intermediate::class, $result->value());
            $this->assertEquals($input, $result->value()->accepted());
            $this->assertTrue($result->value()->done());
        }
    }

    public function characterProvider() : array
    {
        $alpha = array_fill(ord('a'), ord('z') - ord('a') + 1, '');
        array_walk($alpha, function (&$value, int $i) {
            $value = chr($i);
        });
        $alpha = implode('', $alpha);
        $alpha .= strtoupper($alpha);

        $digits = '1234567890';

        return [
            'Accepts all digits.' => ['digit', $digits, true],
            'Accepts no characters from a-z or A-Z.' => ['digit', $alpha, false],
            'Accepts characters from a-z and A-Z.' => ['alpha', $alpha, true],
            'Accepts no digits.' => ['alpha', $digits, false],
        ];
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testEmptyString(string $input, bool $isOk) : void
    {
        $intermediate = new Intermediate($input);
        $brick = new Brick();

        $parse = $brick->sequence(['']);
        $result = $brick->apply($parse, $intermediate);

        $this->assertEquals($isOk, $result->isOk());
    }

    public function emptyStringProvider() : array
    {
        return [
            'Test empty input' => ['', true],
            'Test non empty input' => ['x', false],
        ];
    }
}
