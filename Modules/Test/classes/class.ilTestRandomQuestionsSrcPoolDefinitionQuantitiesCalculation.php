<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation
{
    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinition
     */
    protected $sourcePoolDefinition;
    
    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $intersectionQuantitySharingDefinitionList;
    
    /**
     * @var integer
     */
    protected $overallQuestionAmount;
    
    /**
     * @var integer
     */
    protected $exclusiveQuestionAmount;
    
    /**
     * @var integer
     */
    protected $availableSharedQuestionAmount;
    
    /**
     * ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCheck constructor.
     *
     * @param ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition
     */
    public function __construct(ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition)
    {
        $this->setSourcePoolDefinition($sourcePoolDefinition);
    }
    
    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function getSourcePoolDefinition()
    {
        return $this->sourcePoolDefinition;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition
     */
    public function setSourcePoolDefinition($sourcePoolDefinition)
    {
        $this->sourcePoolDefinition = $sourcePoolDefinition;
    }
    
    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    public function getIntersectionQuantitySharingDefinitionList()
    {
        return $this->intersectionQuantitySharingDefinitionList;
    }
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $intersectionQuantitySharingDefinitionList
     */
    public function setIntersectionQuantitySharingDefinitionList($intersectionQuantitySharingDefinitionList)
    {
        $this->intersectionQuantitySharingDefinitionList = $intersectionQuantitySharingDefinitionList;
    }
    
    /**
     * @return int
     */
    public function getOverallQuestionAmount()
    {
        return $this->overallQuestionAmount;
    }
    
    /**
     * @param int $overallQuestionAmount
     */
    public function setOverallQuestionAmount($overallQuestionAmount)
    {
        $this->overallQuestionAmount = $overallQuestionAmount;
    }
    
    /**
     * @return int
     */
    public function getExclusiveQuestionAmount()
    {
        return $this->exclusiveQuestionAmount;
    }
    
    /**
     * @param int $exclusiveQuestionAmount
     */
    public function setExclusiveQuestionAmount($exclusiveQuestionAmount)
    {
        $this->exclusiveQuestionAmount = $exclusiveQuestionAmount;
    }
    
    /**
     * @return int
     */
    public function getAvailableSharedQuestionAmount()
    {
        return $this->availableSharedQuestionAmount;
    }
    
    /**
     * @param int $availableSharedQuestionAmount
     */
    public function setAvailableSharedQuestionAmount($availableSharedQuestionAmount)
    {
        $this->availableSharedQuestionAmount = $availableSharedQuestionAmount;
    }
    
    /**
     * @return int
     */
    protected function getReservedSharedQuestionAmount()
    {
        return $this->getOverallQuestionAmount() - (
            $this->getExclusiveQuestionAmount() + $this->getAvailableSharedQuestionAmount()
        );
    }
    
    /**
     * @return integer
     */
    protected function getRemainingRequiredQuestionAmount()
    {
        $requiredQuestionAmount = $this->getSourcePoolDefinition()->getQuestionAmount();
        $exclusiveQuestionAmount = $this->getExclusiveQuestionAmount();
        
        return $requiredQuestionAmount - $exclusiveQuestionAmount;
    }
    
    /**
     * @return bool
     */
    protected function isRequiredQuestionAmountSatisfiedByOverallQuestionQuantity()
    {
        $requiredQuestionAmount = $this->getSourcePoolDefinition()->getQuestionAmount();
        $overallQuestionAmount = $this->getOverallQuestionAmount();

        return $overallQuestionAmount >= $requiredQuestionAmount;
    }
    
    /**
     * @return bool
     */
    protected function isRequiredQuestionAmountSatisfiedByExclusiveQuestionQuantity()
    {
        $requiredQuestionAmount = $this->getSourcePoolDefinition()->getQuestionAmount();
        $exclusiveQuestionAmount = $this->getExclusiveQuestionAmount();

        return $exclusiveQuestionAmount >= $requiredQuestionAmount;
    }
    
    /**
     * @return bool
     */
    protected function isRemainingRequiredQuestionAmountSatisfiedBySharedQuestionQuantity()
    {
        $remainingRequiredQuestionAmount = $this->getRemainingRequiredQuestionAmount();
        $availableSharedQuestionAmount = $this->getAvailableSharedQuestionAmount();
        
        return $availableSharedQuestionAmount >= $remainingRequiredQuestionAmount;
    }
    
    /**
     * @return bool
     */
    protected function sourcePoolDefinitionIntersectionsExist()
    {
        if ($this->getIntersectionQuantitySharingDefinitionList()->getDefinitionCount() > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function isRequiredAmountGuaranteedAvailable()
    {
        if ($this->isRequiredQuestionAmountSatisfiedByExclusiveQuestionQuantity()) {
            return true;
        }
        
        if ($this->isRemainingRequiredQuestionAmountSatisfiedBySharedQuestionQuantity()) {
            return true;
        }
        
        return false;
    }
    
    public function getDistributionReport(ilLanguage $lng)
    {
        $report = $this->getRuleSatisfactionResultMessage($lng);
        
        if ($this->sourcePoolDefinitionIntersectionsExist()) {
            $report .= ' ' . $this->getConcurrentRuleConflictMessage($lng);
        }
        
        return $report;
    }
    
    protected function getRuleSatisfactionResultMessage(ilLanguage $lng)
    {
        if ($this->isRequiredQuestionAmountSatisfiedByOverallQuestionQuantity()) {
            return sprintf(
                $lng->txt('tst_msg_rand_quest_set_rule_not_satisfied_reserved'),
                $this->getSourcePoolDefinition()->getSequencePosition(),
                $this->getSourcePoolDefinition()->getQuestionAmount(),
                $this->getOverallQuestionAmount()
            );
        }
        
        return sprintf(
            $lng->txt('tst_msg_rand_quest_set_rule_not_satisfied_missing'),
            $this->getSourcePoolDefinition()->getSequencePosition(),
            $this->getSourcePoolDefinition()->getQuestionAmount(),
            $this->getOverallQuestionAmount()
        );
    }
    
    protected function getConcurrentRuleConflictMessage(ilLanguage $lng)
    {
        $definitionsString = '<br />' . $this->buildIntersectionQuestionSharingDefinitionsString($lng);
        
        if ($this->isRequiredQuestionAmountSatisfiedByOverallQuestionQuantity()) {
            return sprintf(
                $lng->txt('tst_msg_rand_quest_set_rule_not_satisfied_reserved_shared'),
                $this->getAvailableSharedQuestionAmount(),
                $definitionsString
            );
        }
        
        return sprintf(
            $lng->txt('tst_msg_rand_quest_set_rule_not_satisfied_missing_shared'),
            $this->getReservedSharedQuestionAmount(),
            $definitionsString
        );
    }
    
    /**
     * @param ilLanguage $lng
     * @return string
     */
    protected function buildIntersectionQuestionSharingDefinitionsString(ilLanguage $lng)
    {
        $definitionsString = array();
        
        foreach ($this->getIntersectionQuantitySharingDefinitionList() as $definition) {
            $definitionsString[] = sprintf(
                $lng->txt('tst_msg_rand_quest_set_rule_label'),
                $definition->getSequencePosition()
            );
        }
        
        $definitionsString = '<ul><li>' . implode('</li><li>', $definitionsString) . '</li></ul>';
        return $definitionsString;
    }
}
