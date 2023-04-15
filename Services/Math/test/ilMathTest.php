<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathTest extends TestCase
{
    /**
     * @var EvalMath
     */
    protected $eval_math;

    /**
     *
     */
    protected function setUp() : void
    {
        require_once 'Services/Math/classes/class.ilMath.php';
        require_once 'Services/Math/classes/class.EvalMath.php';
        $this->eval_math = new EvalMath();
    }

    /**
     * @dataProvider gcdData
     */
    public function testGcd($a, $b, $result)
    {
        $this->assertEquals($result, ilMath::getGreatestCommonDivisor($a, $b));
    }

    /**
     * @dataProvider andData
     */
    public function testAnd($a, $b, $result)
    {
        $this->assertEquals($result, ilMath::_and($a, $b));
    }

    /**
     * @dataProvider orData
     */
    public function testOr($a, $b, $result)
    {
        $this->assertEquals($result, ilMath::_or($a, $b));
    }

    /**
     * @return array
     */
    public function gcdData()
    {
        return [
            ['1254', '5298', '6'],
            ['41414124', '41414124', '41414124']
        ];
    }

    /**
     * @return array
     */
    public function andData()
    {
        return [
            ['11', '2', '2']
        ];
    }

    /**
     * @return array
     */
    public function orData()
    {
        return [
            ['3', '2', '3']
        ];
    }
}
