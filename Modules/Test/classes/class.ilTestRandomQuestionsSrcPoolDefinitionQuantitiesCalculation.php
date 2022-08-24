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
    public function getSourcePoolDefinition(): ilTestRandomQuestionSetSourcePoolDefinition
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
    public function getIntersectionQuantitySharingDefinitionList(): ilTestRandomQuestionSetSourcePoolDefinitionList
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
    public function getOverallQuestionAmount(): int
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
    public function getExclusiveQuestionAmount(): int
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
    public function getAvailableSharedQuestionAmount(): int
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
    protected function getReservedSharedQuestionAmount(): int
    {
        return $this->getOverallQuestionAmount() - (
            $this->getExclusiveQuestionAmount() + $this->getAvailableSharedQuestionAmount()
        );
    }

    /**
     * @return integer
     */
    protected function getRemainingRequiredQuestionAmount(): int
    {
        $requiredQuestionAmount = $this->getSourcePoolDefinition()->getQuestionAmount();
        $exclusiveQuestionAmount = $this->getExclusiveQuestionAmount();

        return $requiredQuestionAmount - $exclusiveQuestionAmount;
    }

    /**
     * @return bool
     */
    protected function isRequiredQuestionAmountSatisfiedByOverallQuestionQuantity(): bool
    {
        $requiredQuestionAmount = $this->getSourcePoolDefinition()->getQuestionAmount();
        $overallQuestionAmount = $this->getOverallQuestionAmount();

        return $overallQuestionAmount >= $requiredQuestionAmount;
    }

    /**
     * @return bool
     */
    protected function isRequiredQuestionAmountSatisfiedByExclusiveQuestionQuantity(): bool
    {
        $requiredQuestionAmount = $this->getSourcePoolDefinition()->getQuestionAmount();
        $exclusiveQuestionAmount = $this->getExclusiveQuestionAmount();

        return $exclusiveQuestionAmount >= $requiredQuestionAmount;
    }

    /**
     * @return bool
     */
    protected function isRemainingRequiredQuestionAmountSatisfiedBySharedQuestionQuantity(): bool
    {
        $remainingRequiredQuestionAmount = $this->getRemainingRequiredQuestionAmount();
        $availableSharedQuestionAmount = $this->getAvailableSharedQuestionAmount();

        return $availableSharedQuestionAmount >= $remainingRequiredQuestionAmount;
    }

    /**
     * @return bool
     */
    protected function sourcePoolDefinitionIntersectionsExist(): bool
    {
        if ($this->getIntersectionQuantitySharingDefinitionList()->getDefinitionCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isRequiredAmountGuaranteedAvailable(): bool
    {
        if ($this->isRequiredQuestionAmountSatisfiedByExclusiveQuestionQuantity()) {
            return true;
        }

        if ($this->isRemainingRequiredQuestionAmountSatisfiedBySharedQuestionQuantity()) {
            return true;
        }

        return false;
    }

    public function getDistributionReport(ilLanguage $lng): string
    {
        $report = $this->getRuleSatisfactionResultMessage($lng);

        if ($this->sourcePoolDefinitionIntersectionsExist()) {
            $report .= ' ' . $this->getConcurrentRuleConflictMessage($lng);
        }

        return $report;
    }

    protected function getRuleSatisfactionResultMessage(ilLanguage $lng): string
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

    protected function getConcurrentRuleConflictMessage(ilLanguage $lng): string
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
    protected function buildIntersectionQuestionSharingDefinitionsString(ilLanguage $lng): string
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
