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

namespace ILIAS\Test\Tests\Scoring\Manual;

use ILIAS\Test\Scoring\Manual\TestScoring;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

/**
 * Class TestScoringTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class TestScoringTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $test_scoring = $this->createInstanceOf(TestScoring::class);
        $this->assertInstanceOf(TestScoring::class, $test_scoring);
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testPreserveManualScores(): void
    {
        $test_scoring = $this->createInstanceOf(TestScoring::class);

        $test_scoring->setPreserveManualScores(false);
        $this->assertFalse($test_scoring->getPreserveManualScores());

        $test_scoring->setPreserveManualScores(true);
        $this->assertTrue($test_scoring->getPreserveManualScores());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testQuestionId(): void
    {
        $test_scoring = $this->createInstanceOf(TestScoring::class);

        $questionId = 20;
        $test_scoring->setQuestionId($questionId);
        $this->assertEquals($questionId, $test_scoring->getQuestionId());
    }
}
