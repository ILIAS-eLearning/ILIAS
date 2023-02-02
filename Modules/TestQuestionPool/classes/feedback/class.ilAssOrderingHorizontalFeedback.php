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
 * feedback class for assOrderingHorizontal questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssOrderingHorizontalFeedback extends ilAssMultiOptionQuestionFeedback
{
    public function getAnswerOptionsByAnswerIndex(): array
    {
        if (strpos($this->questionOBJ->ordertext, '::')) {
            return explode('::', $this->questionOBJ->ordertext);
        }
        return explode(' ', $this->questionOBJ->ordertext);
    }

    protected function buildAnswerOptionLabel($index, $answer): string
    {
        return trim($answer);
    }
}
