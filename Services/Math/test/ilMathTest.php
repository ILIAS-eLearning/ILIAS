<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EvalMath
     */
    protected $eval_math;

    /**
     *
     */
    protected function setUp()
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
     * @return array
     */
    public function gcdData()
    {
        return [
            ['1254', '5298', '6'],
            ['41414124', '41414124', '41414124']
        ];
    }
}
