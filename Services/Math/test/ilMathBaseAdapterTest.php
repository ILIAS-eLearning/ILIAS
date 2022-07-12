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

use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMathBaseAdapterTest extends TestCase
{
    protected const DEFAULT_SCALE = 50;

    protected ilMathAdapter $mathAdapter;
    protected EvalMath $evalMath;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        ilMath::setDefaultAdapter($this->mathAdapter);
        $this->evalMath = new EvalMath();
        parent::setUp();
    }

    /**
     * This method is used as a 'Comparator' for two numeric strings and is equal to the ScalarComparator behaviour of PHPUnit 5.x
     * In PHPUnit 8 the ScalarComparators uses a strict string comparison, so numbers with a different amount of trailing
     * 0 decimals are not equal anymore
     * @see \SebastianBergmann\Comparator\ScalarComparator
     * @param string $actual
     * @param string $expected
     */
    private function assertEqualNumbers(string $actual, string $expected) : void
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder("\n--- Expected\n+++ Actual\n"));

        /** @noinspection PhpUnitTestsInspection */
        $this->assertTrue($actual == $expected, $differ->diff($actual, $expected));
    }

    /**
     * @dataProvider addData
     */
    public function testAdd(string $a, string $b, string $result, int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->add($a, $b, $scale));
    }

    /**
     *  @dataProvider subData
     */
    public function testSub(string $a, string $b, string $result, int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->sub($a, $b, $scale));
    }

    /**
     *  @dataProvider mulData
     */
    public function testMul(string $a, string $b, string $result, int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->mul($a, $b, $scale));
    }

    /**
     *  @dataProvider divData
     */
    public function testDiv(string $a, string $b, string $result, int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->div($a, $b, $scale));
    }

    /**
     *  @dataProvider sqrtData
     */
    public function testSqrt(string $a, string $result, ?int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->sqrt($a, $scale));
    }

    /**
     *  @dataProvider powData
     */
    public function testPow(string $a, string $b, string $result, ?int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->pow($a, $b, $scale));
    }

    /**
     * @dataProvider modData
     * @throws ilMathDivisionByZeroException
     */
    public function testMod(string $a, string $b, string $result) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->mod($a, $b));
    }

    /**
     *  @dataProvider equalsData
     */
    public function testEquals(string $a, string $b, bool $result, ?int $scale) : void
    {
        $this->assertEqualNumbers($result, $this->mathAdapter->equals($a, $b, $scale));
    }

    /**
     *  @dataProvider calcData
     */
    public function testCalculation(string $formula, string $result, int $scale) : void
    {
        $this->assertEqualNumbers($result, ilMath::_applyScale($this->evalMath->evaluate($formula), $scale));
    }

    /**
     *
     */
    public function testDivisionsByZero() : void
    {
        $this->expectException(ilMathDivisionByZeroException::class);

        $this->mathAdapter->div(1, 0);
    }

    /**
     *
     */
    public function testModuloByZero() : void
    {
        $this->expectException(ilMathDivisionByZeroException::class);

        $this->mathAdapter->mod(1, 0);
    }

    /**
     * @return array
     */
    public function addData() : array
    {
        return [
            ['1', '2', '3', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function subData() : array
    {
        return [
            ['1', '2', '-1', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function mulData() : array
    {
        return [
            'Multiplication with integer operands' => ['1', '2', '2', self::DEFAULT_SCALE],
            'Multiplication with empty string operand' => ['1', '', '0', self::DEFAULT_SCALE],
            'Multiplication with decimal operands' => ['1.5', '2.5', '3.75', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function divData() : array
    {
        return [
            'Division with integer operands' => ['1', '2', '0.5', self::DEFAULT_SCALE],
            'Division with empty string operand' => ['', '2', '0', self::DEFAULT_SCALE],
            'Division with decimal operands' => ['3.75', '2.5', '1.5', self::DEFAULT_SCALE],
        ];
    }

    /**
     * @return array
     */
    public function modData() : array
    {
        return [
            ['1', '2', '1']
        ];
    }

    /**
     * @return array
     */
    public function sqrtData() : array
    {
        return [
            ['9', '3', self::DEFAULT_SCALE],
            ['4294967296', '65536', self::DEFAULT_SCALE],
            ['12345678901234567890', '3513641828', null],
            ['12345678901234567890', '3513641828.82', 2]
        ];
    }

    /**
     * @return array
     */
    public function powData() : array
    {
        return [
            ['3', '2', '9', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function equalsData() : array
    {
        return [
            ['3', '3', true, null],
            ['27.424', '27.424', true, 5]
        ];
    }

    /**
     *
     */
    public function calcData() : array
    {
        return [
            ['3+5', '8', self::DEFAULT_SCALE],
            ['-3+5', '2', self::DEFAULT_SCALE],
            ['3*6+5', '23', self::DEFAULT_SCALE],
            ['10/2', '5', self::DEFAULT_SCALE],
            ['13/60', '0.2166666666666', 13],
            ['(-(-8)-sqrt((-8)^2-4*(7)))/(2)', '1', self::DEFAULT_SCALE],
            ['(-(-8)+sqrt((-8)^2-4*(7)))/(2)', '7', self::DEFAULT_SCALE],
            ['(-(-41)-sqrt((-41)^2-4*(1)*(5)))/(2*(1))', '0.122', 3],
            ['(-(-41)+sqrt((-41)^2-4*(1)*(5)))/(2*(1))', '40.877', 3],
            ['4^2-2*4+0.5*-16', '0', self::DEFAULT_SCALE],
            ['-2^2-2*-2+0.5*-16', '-8', self::DEFAULT_SCALE]
        ];
    }
}
