<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
abstract class ilMathBaseAdapterTest extends PHPUnit_Framework_TestCase
{
    const DEFAULT_SCALE = 50;

    /**
     * @var ilMathAdapter
     */
    protected $mathAdapter;

    /**
     * @var EvalMath
     */
    protected $evalMath;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        require_once 'Services/Math/classes/class.ilMath.php';
        require_once 'Services/Math/classes/class.EvalMath.php';

        ilMath::setDefaultAdapter($this->mathAdapter);
        $this->evalMath = new EvalMath();
        parent::setUp();
    }

    /**
     * @dataProvider addData
     */
    public function testAdd($a, $b, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->add($a, $b, $scale));
    }

    /**
     *  @dataProvider subData
     */
    public function testSub($a, $b, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->sub($a, $b, $scale));
    }

    /**
     *  @dataProvider mulData
     */
    public function testMul($a, $b, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->mul($a, $b, $scale));
    }

    /**
     *  @dataProvider divData
     */
    public function testDiv($a, $b, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->div($a, $b, $scale));
    }

    /**
     *  @dataProvider sqrtData
     */
    public function testSqrt($a, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->sqrt($a, $scale));
    }

    /**
     *  @dataProvider powData
     */
    public function testPow($a, $b, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->pow($a, $b, $scale));
    }

    /**
     *  @dataProvider modData
     */
    public function testMod($a, $b, $result)
    {
        $this->assertEquals($result, $this->mathAdapter->mod($a, $b));
    }

    /**
     *  @dataProvider equalsData
     */
    public function testEquals($a, $b, $result, $scale)
    {
        $this->assertEquals($result, $this->mathAdapter->equals($a, $b, $scale));
    }

    /**
     *  @dataProvider calcData
     */
    public function testCalculation($formula, $result, $scale)
    {
        $this->assertEquals($result, ilMath::_applyScale($this->evalMath->evaluate($formula), $scale));
    }

    /**
     *
     */
    public function testDivisionsByZero()
    {
        $this->setExpectedException(ilMathDivisionByZeroException::class);

        $this->mathAdapter->div(1, 0);
    }

    /**
     *
     */
    public function testModuloByZero()
    {
        $this->setExpectedException(ilMathDivisionByZeroException::class);

        $this->mathAdapter->mod(1, 0);
    }

    /**
     * @return array
     */
    public function addData()
    {
        return [
            ['1', '2', '3', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function subData()
    {
        return [
            ['1', '2', '-1', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function mulData()
    {
        return [
            ['1', '2', '2', self::DEFAULT_SCALE],
            ['1', '', '0', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function divData()
    {
        return [
            ['1', '2', '0.5', self::DEFAULT_SCALE],
            ['', '2', '0', self::DEFAULT_SCALE],
        ];
    }

    /**
     * @return array
     */
    public function modData()
    {
        return [
            ['1', '2', '1']
        ];
    }

    /**
     * @return array
     */
    public function sqrtData()
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
    public function powData()
    {
        return [
            ['3', '2', '9', self::DEFAULT_SCALE]
        ];
    }

    /**
     * @return array
     */
    public function equalsData()
    {
        return [
            ['3', '3', true, null],
            ['27.424', '27.424', true, 5]
        ];
    }

    /**
     *
     */
    public function calcData()
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
