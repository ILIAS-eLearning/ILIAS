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
 * feedback class for assOrderingQuestion questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssOrderingQuestionFeedback extends ilAssMultiOptionQuestionFeedback
{
    /**
     * returns the answer options mapped by answer index
     * (can be overwritten by concrete question type class)
     *
     * @return array $answerOptionsByAnswerIndex
     */
    public function getAnswerOptionsByAnswerIndex(): array
    {
        return $this->questionOBJ->getOrderingElementList()->getElements();
    }

    /**
     * builds an answer option label from given (mixed type) index and answer
     * (can be overwritten by concrete question types)
     *
     * @access protected
     * @param integer $position
     * @param ilAssOrderingElement $orderingElement
     * @return string $answerOptionLabel
     */
    protected function buildAnswerOptionLabel(int $position, $orderingElement): string
    {
        return $orderingElement->getContent();
    }
}
