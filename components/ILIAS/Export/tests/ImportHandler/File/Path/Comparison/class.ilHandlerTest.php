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

namespace Test\ImportHandler\File\Path\Comparison;

use ImportHandler\File\Path\Comparison\ilHandler as ilFilePathComparison;
use ImportHandler\File\Path\Comparison\Operator;
use ImportHandler\File\Path\Comparison\Operator as ilFilePathComparisonOperator;
use PHPUnit\Framework\TestCase;

class ilHandlerTest extends TestCase
{
    protected function setUp(): void
    {

    }

    public function testComparison(): void
    {
        $comp1 = new ilFilePathComparison(ilFilePathComparisonOperator::EQUAL, 'Args');
        $comp2 = new ilFilePathComparison(ilFilePathComparisonOperator::LOWER_EQUAL, '');
        $comp3 = new ilFilePathComparison(ilFilePathComparisonOperator::GREATER, '2');

        $this->assertEquals(
            ilFilePathComparisonOperator::toString(ilFilePathComparisonOperator::EQUAL) . 'Args',
            $comp1->toString()
        );

        $this->assertEquals(
            ilFilePathComparisonOperator::toString(ilFilePathComparisonOperator::LOWER_EQUAL),
            $comp2->toString()
        );

        $this->assertEquals(
            ilFilePathComparisonOperator::toString(ilFilePathComparisonOperator::GREATER) . '2',
            $comp3->toString()
        );
    }
}
