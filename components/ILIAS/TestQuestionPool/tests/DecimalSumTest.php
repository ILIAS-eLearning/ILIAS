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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\TestQuestionPool\Scoring\DecimalSum;

class DecimalSumTest extends TestCase
{
    private DecimalSum $decimal_sum;

    protected function setUp(): void
    {
        $this->decimal_sum = new DecimalSum();
    }

    public function testSumEmptyListReturnsZero(): void
    {
        $this->assertSame(0.0, $this->decimal_sum->sum([]));
    }

    public function testSumCancellingValuesReturnsZero(): void
    {
        $this->assertSame(0.0, $this->decimal_sum->sum([-0.5, 0.34, 0.33, -0.5, 0.33]));
    }

    public function testSumTypicalDecimalAddition(): void
    {
        $this->assertSame(0.3, $this->decimal_sum->sum([0.1, 0.2]));
    }
}
