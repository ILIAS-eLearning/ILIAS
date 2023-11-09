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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestSequenceFixedQuestionSet extends ilTestSequence
{
    /**
     * @param ilTestReindexedSequencePositionMap $reindexedSequencePositionMap
     */
    public function removeQuestion(int $question_id, ilTestReindexedSequencePositionMap $reindexedSequencePositionMap): void
    {
        foreach ($this->sequencedata['sequence'] as $key => $oldSequenceElement) {
            $newSequenceElement = $reindexedSequencePositionMap->getNewSequencePosition($oldSequenceElement);

            if ($newSequenceElement) {
                $this->sequencedata['sequence'][$key] = $newSequenceElement;
            } else {
                unset($this->sequencedata['sequence'][$key]);
            }
        }

        $this->sequencedata['sequence'] = array_values($this->sequencedata['sequence']);

        $this->sequencedata['postponed'] = $this->removeArrayValue($this->sequencedata['postponed'], $question_id);
        $this->sequencedata['hidden'] = $this->removeArrayValue($this->sequencedata['hidden'], $question_id);

        $this->optionalQuestions = $this->removeArrayValue($this->optionalQuestions, $question_id);

        $this->alreadyPresentedQuestions = $this->removeArrayValue($this->alreadyPresentedQuestions, $question_id);

        $this->alreadyCheckedQuestions = $this->removeArrayValue($this->alreadyCheckedQuestions, $question_id);
    }

    private function removeArrayValue(array $array, int $value): array
    {
        foreach ($array as $key => $val) {
            if ($val == $value) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
