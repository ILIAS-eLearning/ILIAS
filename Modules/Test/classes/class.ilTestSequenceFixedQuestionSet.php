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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSequenceFixedQuestionSet extends ilTestSequence
{
    /**
     * @param int $questionId
     * @param ilTestReindexedSequencePositionMap $reindexedSequencePositionMap
     */
    public function removeQuestion($questionId, ilTestReindexedSequencePositionMap $reindexedSequencePositionMap): void
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

        $this->sequencedata['postponed'] = $this->removeArrayValue($this->sequencedata['postponed'], $questionId);
        $this->sequencedata['hidden'] = $this->removeArrayValue($this->sequencedata['hidden'], $questionId);

        $this->optionalQuestions = $this->removeArrayValue($this->optionalQuestions, $questionId);

        $this->alreadyPresentedQuestions = $this->removeArrayValue($this->alreadyPresentedQuestions, $questionId);

        $this->alreadyCheckedQuestions = $this->removeArrayValue($this->alreadyCheckedQuestions, $questionId);
    }

    private function removeArrayValue($array, $value)
    {
        foreach ($array as $key => $val) {
            if ($val == $value) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
