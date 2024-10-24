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

namespace ILIAS\Test\Tests\Results\Data;

use ILIAS\Test\Results\Data\QuestionResult;
use PHPUnit\Framework\TestCase;

class QuestionResultTest extends TestCase
{
    public function testTestQuestionResultBasicProperties(): void
    {
        $qr = new QuestionResult(
            $id = 66,
            $type = 'some type',
            $title = 'a question title',
            $question_score = 7.6,
            $usr_score = 3.8,
            $usr_solution = 'usr did this',
            $best_solution = 'he had better done that',
            $feedback = 'give it another try',
            $worked_through = true,
            $answered = true,
            $requested_hints = 2,
            $recapitulation = 'some recap'
        );

        $this->assertEquals($id, $qr->getId());
        $this->assertEquals($type, $qr->getType());
        $this->assertEquals($title, $qr->getTitle());
        $this->assertEquals($question_score, $qr->getQuestionScore());
        $this->assertEquals($usr_score, $qr->getUserScore());
        $this->assertEquals(50, $qr->getUserScorePercent());
        $this->assertEquals(QuestionResult::CORRECT_PARTIAL, $qr->getCorrect());
        $this->assertEquals($feedback, $qr->getFeedback());
        $this->assertTrue($qr->isWorkedThrough());
        $this->assertTrue($qr->isAnswered());
        $this->assertEquals($recapitulation, $qr->getContentForRecapitulation());
        $this->assertEquals($requested_hints, $qr->getNumberOfRequestedHints());
    }
}
