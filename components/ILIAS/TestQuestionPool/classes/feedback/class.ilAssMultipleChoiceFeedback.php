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
 * feedback class for assMultipleChoice questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssMultipleChoiceFeedback extends ilAssConfigurableMultiOptionQuestionFeedback
{
    /**
     * table name for specific feedback
     */
    public const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_mc';

    /**
     * returns the table name for specific question itself
     *
     * @return string $specificFeedbackTableName
     */
    protected function getSpecificQuestionTableName(): string
    {
        return self::SPECIFIC_QUESTION_TABLE_NAME;
    }

    protected function buildAnswerOptionLabel(int $index, $answer): string
    {
        $label = array();

        if (strlen($answer->getImage())) {
            if ($this->questionOBJ->getThumbSize()) {
                $src = $this->questionOBJ->getImagePathWeb() . $this->questionOBJ->getThumbPrefix() . $answer->getImage();
            } else {
                $src = $this->questionOBJ->getImagePathWeb() . $answer->getImage();
            }

            $label[] = "<img src='{$src}' />";
        }

        if (strlen($answer->getAnswertext())) {
            $label[] = $answer->getAnswertext();
        }

        return implode('<br />', $label);
    }
}
