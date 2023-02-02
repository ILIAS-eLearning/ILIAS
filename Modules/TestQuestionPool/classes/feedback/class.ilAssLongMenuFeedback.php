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
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssLongMenuFeedback extends ilAssMultiOptionQuestionFeedback
{
    public const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_lome';

    protected function getSpecificQuestionTableName(): string
    {
        return self::SPECIFIC_QUESTION_TABLE_NAME;
    }

    public function getAnswerOptionsByAnswerIndex(): array
    {
        return $this->questionOBJ->getAnswers();
    }

    protected function buildAnswerOptionLabel(int $index, $answers): string
    {
        $counter = $index + 1;
        $caption = 'Longmenu ' . $counter ;
        return $caption;
    }
}
