<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

// fau: testNav - new class ilTestQuestionConfig.

/**
 * Test Question configuration
 */
class ilTestQuestionConfig
{
    protected $isUnchangedAnswerPossible = false;
    protected $useUnchangedAnswerLabel = '';
    protected $enableFormChangeDetection = true;
    protected $enableBackgroundChangeDetection = false;

    // hey: prevPassSolutions - previous solution adopted
    protected $previousPassSolutionReuseAllowed = false;
    protected $solutionInitiallyPrefilled = false;
    // hey.

    protected $scoreEmptyMcSolutionsEnabled = false;

    protected $workedThrough = false;

    /**
     * ilTestQuestionConfig constructor.
     */
    public function __construct()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->useUnchangedAnswerLabel = $lng->txt('tst_unchanged_answer_is_correct');
    }

    /**
     * Return if the saving of an unchanged answer is supported with an additional checkbox
     * @return bool
     */
    public function isUnchangedAnswerPossible(): bool
    {
        return $this->isUnchangedAnswerPossible;
    }

    /**
     * Set if the saving of an unchanged answer is supported with an additional checkbox
     * @param 	bool 	$isUnchangedAnswerPossible
     * @return  ilTestQuestionConfig
     */
    public function setIsUnchangedAnswerPossible($isUnchangedAnswerPossible): ilTestQuestionConfig
    {
        $this->isUnchangedAnswerPossible = $isUnchangedAnswerPossible;
        return $this;
    }

    /**
     * Return the label to be used for the 'use unchanged answer' checkbox
     * @return string
     */
    public function getUseUnchangedAnswerLabel(): string
    {
        return $this->useUnchangedAnswerLabel;
    }

    /**
     * Return the label to be used for the 'use unchanged answer' checkbox
     * @param 	string 	$useUnchangedAnswerLabel
     * @return 	ilTestQuestionConfig
     */
    public function setUseUnchangedAnswerLabel($useUnchangedAnswerLabel): ilTestQuestionConfig
    {
        $this->useUnchangedAnswerLabel = $useUnchangedAnswerLabel;
        return $this;
    }

    /**
     * Return if the detection of form changes is enabled
     * @return bool
     */
    public function isFormChangeDetectionEnabled(): bool
    {
        return $this->enableFormChangeDetection;
    }

    /**
     * Set if the detection of form changes is enabled
     * @param 	bool 	$enableFormChangeDetection
     * @return	ilTestQuestionConfig
     */
    public function setFormChangeDetectionEnabled($enableFormChangeDetection): ilTestQuestionConfig
    {
        $this->enableFormChangeDetection = $enableFormChangeDetection;
        return $this;
    }

    /**
     * Return if the detection of background changes is enabled
     * @return bool
     */
    public function isBackgroundChangeDetectionEnabled(): bool
    {
        return $this->enableBackgroundChangeDetection;
    }

    /**
     * Set if the detection of background changes is enabled
     * This is set by Java and Flash questions to poll for server-side savings
     *
     * @param $enableBackgroundChangeDetection
     * @return	ilTestQuestionConfig
     */
    public function setBackgroundChangeDetectionEnabled($enableBackgroundChangeDetection): ilTestQuestionConfig
    {
        $this->enableBackgroundChangeDetection = $enableBackgroundChangeDetection;
        return $this;
    }

    // hey: prevPassSolutions - extension or fix or anything sensefull in the current fixing work :-D
    /**
     * @return bool
     */
    public function isPreviousPassSolutionReuseAllowed(): bool
    {
        return $this->previousPassSolutionReuseAllowed;
    }

    /**
     * @param bool $previousPassSolutionReuseAllowed
     */
    public function setPreviousPassSolutionReuseAllowed($previousPassSolutionReuseAllowed): void
    {
        $this->previousPassSolutionReuseAllowed = $previousPassSolutionReuseAllowed;
    }
    // hey.

    // hey: prevPassSolutions - previous solution adopted
    /**
     * @return bool
     */
    public function isSolutionInitiallyPrefilled(): bool
    {
        return $this->solutionInitiallyPrefilled;
    }

    /**
     * @param bool $solutionInitiallyPrefilled
    // hey: prevPassSolutions - streamlined signatures
     * @return ilTestQuestionConfig $this
    // hey.
     */
    public function setSolutionInitiallyPrefilled($solutionInitiallyPrefilled): ilTestQuestionConfig
    {
        $this->solutionInitiallyPrefilled = $solutionInitiallyPrefilled;
        // hey: prevPassSolutions - streamlined signatures
        return $this;
        // hey.
    }

    /**
     * @return bool
     */
    public function isScoreEmptyMcSolutionsEnabled(): bool
    {
        return $this->scoreEmptyMcSolutionsEnabled;
    }

    /**
     * @param bool $scoreEmptyMcSolutionsEnabled
     */
    public function setScoreEmptyMcSolutionsEnabled($scoreEmptyMcSolutionsEnabled): void
    {
        $this->scoreEmptyMcSolutionsEnabled = $scoreEmptyMcSolutionsEnabled;
    }

    /**
     * @return bool
     */
    public function isWorkedThrough(): bool
    {
        return $this->workedThrough;
    }

    /**
     * @param bool $workedThrough
     * @return $this
     */
    public function setWorkedThrough($workedThrough): ilTestQuestionConfig
    {
        $this->workedThrough = $workedThrough;
        return $this;
    }
    // hey.
}
