<?php
use PHPUnit\Framework\TestCase;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
        $this->eval_math = new EvalMath();
    }

    /**
     * @dataProvider gcdData
     */
    public function testGcd(string $a, string $b, string $result) : void
    {
        $this->assertEquals($result, ilMath::getGreatestCommonDivisor($a, $b));
    }

    /**
     * @return array<int, array<string>>
     */
    public function gcdData() : array
    {
        return [
            ['1254', '5298', '6'],
            ['41414124', '41414124', '41414124']
        ];
    }
}
