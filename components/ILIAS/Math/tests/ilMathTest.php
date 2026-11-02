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
    protected function setUp(): void
    {
        $this->eval_math = new EvalMath();
    }

    /**
     * @dataProvider gcdData
     */
    public function testGcd(string $a, string $b, string $result): void
    {
        $this->assertEquals($result, ilMath::getGreatestCommonDivisor($a, $b));
    }

    /**
     * @return array<int, array<string>>
     */
    public static function gcdData(): array
    {
        return [
            ['1254', '5298', '6'],
            ['41414124', '41414124', '41414124']
        ];
    }

    public function testPlusScientificNotationIsPowerOfTen(): void
    {
        $lower = $this->eval_math->evaluate('5.4E+24');
        $upper = $this->eval_math->evaluate('5.6E+24');
        $without_plus = $this->eval_math->evaluate('54E23');

        $this->assertNotFalse($lower);
        $this->assertNotFalse($upper);
        $this->assertEqualsWithDelta(5.4e24, (float) $lower, 1.0);
        $this->assertEqualsWithDelta(5.6e24, (float) $upper, 1.0);
        $this->assertEqualsWithDelta(5.4e24, (float) $without_plus, 1.0);
    }
}
