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
 * feedback class for assErrorText questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssErrorTextFeedback extends ilAssMultiOptionQuestionFeedback
{
    /**
     * returns the answer options mapped by answer index
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     *
     * @return string[] $answerOptionsByAnswerIndex
     */
    public function getAnswerOptionsByAnswerIndex(): array
    {
        return $this->questionOBJ->getErrorData();
    }

    /**
     * builds an answer option label from given (mixed type) index and answer
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     * @param integer $index
     * @param mixed $answer
     */
    protected function buildAnswerOptionLabel(int $index, $answer): string
    {
        $caption = $ordinal = $index + 1;
        $caption .= '. <br />"' . $answer->text_wrong . '" =&gt; ';
        $caption .= '"' . $answer->text_correct . '"';
        $caption .= '</i>';

        return $caption;
    }
}
