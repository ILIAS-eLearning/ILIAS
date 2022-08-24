<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';

/**
 * feedback class for assSingleChoice questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssSingleChoiceFeedback extends ilAssConfigurableMultiOptionQuestionFeedback
{
    /**
     * table name for specific feedback
     */
    public const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_sc';

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
