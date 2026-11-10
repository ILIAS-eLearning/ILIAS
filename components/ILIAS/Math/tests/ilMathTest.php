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
        ilMath::setDefaultAdapter(ilMath::getFirstValidAdapter());
        $this->eval_math = new EvalMath();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('gcdData')]
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
        $this->assertEqualsWithDelta(100.0, (float) $this->eval_math->evaluate('1E+2'), 0.000001);
        $this->assertEqualsWithDelta(5400000.0, (float) $this->eval_math->evaluate('5.4E+6'), 0.001);
        $this->assertEqualsWithDelta(5400000.0, (float) $this->eval_math->evaluate('54E5'), 0.001);
        $this->assertEqualsWithDelta(0.0054, (float) $this->eval_math->evaluate('5.4E-3'), 0.0000001);

        $lower = (float) $this->eval_math->evaluate('5.4E+24');
        $upper = (float) $this->eval_math->evaluate('5.6E+24');
        $this->assertGreaterThan(38000.0, $lower);
        $this->assertGreaterThan($lower, $upper);

        if (extension_loaded('bcmath')) {
            $lower_text = (string) $this->eval_math->evaluate('5.4E+24');
            $upper_text = (string) $this->eval_math->evaluate('5.6E+24');
            $this->assertSame(0, bccomp($lower_text, '5400000000000000000000000', 0), $lower_text);
            $this->assertSame(0, bccomp($upper_text, '5600000000000000000000000', 0), $upper_text);
        }
    }
}
