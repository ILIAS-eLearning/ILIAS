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

/**
 * Class ilAssClozeTestSpecificFeedbackIdentifier
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAssSpecificFeedbackIdentifier
{
    protected int $feedbackId;

    protected int $questionId;

    protected int $questionIndex;

    protected int $answerIndex;

    public function getFeedbackId(): int
    {
        return $this->feedbackId;
    }

    public function setFeedbackId(int $feedbackId): void
    {
        $this->feedbackId = $feedbackId;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function setQuestionId(int $questionId): void
    {
        $this->questionId = $questionId;
    }

    public function getQuestionIndex(): int
    {
        return $this->questionIndex;
    }

    public function setQuestionIndex(int $questionIndex): void
    {
        $this->questionIndex = $questionIndex;
    }

    public function getAnswerIndex(): int
    {
        return $this->answerIndex;
    }

    public function setAnswerIndex(int $answerIndex): void
    {
        $this->answerIndex = $answerIndex;
    }
}
