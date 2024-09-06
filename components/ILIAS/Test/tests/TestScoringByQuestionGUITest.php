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

use ILIAS\Test\Scoring\Manual\TestScoringByQuestionGUI;
use PHPUnit\Framework\MockObject\Exception;

/**
 * Class TestScoringByQuestionGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class TestScoringByQuestionGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestScoringByQuestionGUI::class, $this->createInstanceOf(TestScoringByQuestionGUI::class));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetDefaultCommand(): void
    {
        $test_scoring_by_question_gui = $this->createInstanceOf(TestScoringByQuestionGUI::class);
        $this->assertEquals('showManScoringByQuestionParticipantsTable', self::callMethod($test_scoring_by_question_gui, 'getDefaultCommand'));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testActiveSubTabId(): void
    {
        $test_scoring_by_question_gui = $this->createInstanceOf(TestScoringByQuestionGUI::class);
        $this->assertEquals('man_scoring_by_qst', self::callMethod($test_scoring_by_question_gui, 'getActiveSubTabId'));
    }
}
