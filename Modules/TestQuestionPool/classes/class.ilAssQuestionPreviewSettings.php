<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionPreviewSettings
{
    private $contextRefId = null;

    /**
     * @var bool
     */
    protected $reachedPointsEnabled = false;

    private $genericFeedbackEnabled = false;

    private $specificFeedbackEnabled = false;

    private $hintProvidingEnabled = false;

    private $bestSolutionEnabled = false;

    public function __construct($contextRefId)
    {
        $this->contextRefId = $contextRefId;
    }

    public function init(): void
    {
        if ($this->isTestRefId()) {
            $this->initSettingsWithTestObject();
        } else {
            $this->initSettingsFromPostParameters();
        }
    }

    public function isTestRefId(): bool
    {
        $objectType = ilObject::_lookupType($this->contextRefId, true);

        return $objectType == 'tst';
    }

    private function initSettingsWithTestObject(): void
    {
        /* @var ilObjTest $testOBJ */
        $testOBJ = ilObjectFactory::getInstanceByRefId($this->contextRefId);
        $testOBJ->loadFromDb();

        $this->setGenericFeedbackEnabled($testOBJ->getGenericAnswerFeedback());
        $this->setSpecificFeedbackEnabled($testOBJ->getSpecificAnswerFeedback());
        $this->setHintProvidingEnabled($testOBJ->isOfferingQuestionHintsEnabled());
        $this->setBestSolutionEnabled($testOBJ->getInstantFeedbackSolution());
        $this->setReachedPointsEnabled($testOBJ->getAnswerFeedbackPoints());
    }

    private function initSettingsFromPostParameters(): void
    {
        // get from post or from toolbar instance if possible

        $this->setGenericFeedbackEnabled(true);
        $this->setSpecificFeedbackEnabled(true);
        $this->setHintProvidingEnabled(true);
        $this->setBestSolutionEnabled(true);
        $this->setReachedPointsEnabled(true);
    }

    public function setContextRefId($contextRefId): void
    {
        $this->contextRefId = $contextRefId;
    }

    public function getContextRefId()
    {
        return $this->contextRefId;
    }

    /**
     * @return bool
     */
    public function isReachedPointsEnabled(): bool
    {
        return $this->reachedPointsEnabled;
    }

    /**
     * @param bool $reachedPointsEnabled
     */
    public function setReachedPointsEnabled(bool $reachedPointsEnabled): void
    {
        $this->reachedPointsEnabled = $reachedPointsEnabled;
    }

    public function setGenericFeedbackEnabled($genericFeedbackEnabled): void
    {
        $this->genericFeedbackEnabled = $genericFeedbackEnabled;
    }

    public function isGenericFeedbackEnabled(): bool
    {
        return $this->genericFeedbackEnabled;
    }

    public function setSpecificFeedbackEnabled($specificFeedbackEnabled): void
    {
        $this->specificFeedbackEnabled = $specificFeedbackEnabled;
    }

    public function isSpecificFeedbackEnabled(): bool
    {
        return $this->specificFeedbackEnabled;
    }

    public function setHintProvidingEnabled(bool $hintProvidingEnabled): void
    {
        $this->hintProvidingEnabled = $hintProvidingEnabled;
    }

    public function isHintProvidingEnabled(): bool
    {
        return $this->hintProvidingEnabled;
    }

    public function setBestSolutionEnabled($bestSolutionEnabled): void
    {
        $this->bestSolutionEnabled = $bestSolutionEnabled;
    }

    public function isBestSolutionEnabled(): bool
    {
        return $this->bestSolutionEnabled;
    }

    public function isInstantFeedbackNavigationRequired(): bool
    {
        if ($this->isGenericFeedbackEnabled()) {
            return true;
        }

        if ($this->isSpecificFeedbackEnabled()) {
            return true;
        }

        if ($this->isBestSolutionEnabled()) {
            return true;
        }

        return false;
    }

    public function isHintProvidingNavigationRequired(): bool
    {
        return $this->isHintProvidingEnabled();
    }
}
