<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSequence.php';

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
    public function removeQuestion($questionId, ilTestReindexedSequencePositionMap $reindexedSequencePositionMap)
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
