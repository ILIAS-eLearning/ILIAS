<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestDynamicQuestionSetFilterSelection
{
    const ANSWER_STATUS_FILTER_VALUE_ALL_NON_CORRECT = 'allNonCorrect';
    const ANSWER_STATUS_FILTER_VALUE_NON_ANSWERED = 'nonAnswered';
    const ANSWER_STATUS_FILTER_VALUE_WRONG_ANSWERED = 'wrongAnswered';
    
    /**
     * @var integer
     */
    private $answerStatusActiveId = null;
    
    /**
     * @var string
     */
    private $answerStatusSelection = null;

    /**
     * @var array
     */
    private $taxonomySelection = array();

    /**
     * @var array
     */
    private $forcedQuestionIds = array();

    /**
     * @param int $answerStatusActiveId
     */
    public function setAnswerStatusActiveId($answerStatusActiveId)
    {
        $this->answerStatusActiveId = $answerStatusActiveId;
    }

    /**
     * @return int
     */
    public function getAnswerStatusActiveId()
    {
        return $this->answerStatusActiveId;
    }

    /**
     * @param null $answerStatusSelection
     */
    public function setAnswerStatusSelection($answerStatusSelection)
    {
        $this->answerStatusSelection = $answerStatusSelection;
    }

    /**
     * @return null
     */
    public function getAnswerStatusSelection()
    {
        return $this->answerStatusSelection;
    }
    
    /**
     * @return bool
     */
    public function hasAnswerStatusSelection()
    {
        switch ($this->getAnswerStatusSelection()) {
            case self::ANSWER_STATUS_FILTER_VALUE_ALL_NON_CORRECT:
            case self::ANSWER_STATUS_FILTER_VALUE_NON_ANSWERED:
            case self::ANSWER_STATUS_FILTER_VALUE_WRONG_ANSWERED:
                
                return true;
        }
        
        return false;
    }

    public function isAnswerStatusSelectionWrongAnswered()
    {
        return $this->getAnswerStatusSelection() == self::ANSWER_STATUS_FILTER_VALUE_WRONG_ANSWERED;
    }

    /**
     * @param array $taxonomySelection
     */
    public function setTaxonomySelection($taxonomySelection)
    {
        $this->taxonomySelection = $taxonomySelection;
    }

    /**
     * @return array
     */
    public function getTaxonomySelection()
    {
        return $this->taxonomySelection;
    }
    
    /**
     * @param $taxonomyId
     * @return bool
     */
    public function hasSelectedTaxonomy($taxonomyId)
    {
        return isset($this->taxonomySelection[$taxonomyId]);
    }
    
    /**
     * @param integer $taxonomyId
     * @return array
     */
    public function getSelectedTaxonomy($taxonomyId)
    {
        return $this->taxonomySelection[$taxonomyId];
    }

    /**
     * @param array $forcedQuestionIds
     */
    public function setForcedQuestionIds($forcedQuestionIds)
    {
        $this->forcedQuestionIds = $forcedQuestionIds;
    }

    /**
     * @return array
     */
    public function getForcedQuestionIds()
    {
        return $this->forcedQuestionIds;
    }
}
