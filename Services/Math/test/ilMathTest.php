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

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMathTest extends TestCase
{
    protected EvalMath $eval_math;

    /**
     * @inheritDoc
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
