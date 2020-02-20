<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssKprimChoiceFeedback extends ilAssConfigurableMultiOptionQuestionFeedback
{
    /**
     * @var assKprimChoice
     */
    protected $questionOBJ;
    
    const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_kprim';

    protected function getSpecificQuestionTableName()
    {
        return self::SPECIFIC_QUESTION_TABLE_NAME;
    }
    
    /**
     * @param int $index
     * @param ilAssKprimChoiceAnswer $answer
     * @return string
     */
    protected function buildAnswerOptionLabel($index, $answer)
    {
        $label = array();
        
        if (strlen($answer->getImageFile())) {
            if ($this->questionOBJ->getThumbSize()) {
                $src = $answer->getThumbWebPath();
            } else {
                $src = $answer->getImageWebPath();
            }
            
            $label[] = "<img src='{$src}' />";
        }
        
        if (strlen($answer->getAnswertext())) {
            $label[] = $answer->getAnswertext();
        }
        
        return implode('<br />', $label);
    }
}
