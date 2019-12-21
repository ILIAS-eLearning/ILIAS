<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityEvaluableCriterion
 */
interface ilAccessibilityEvaluableCriterion
{
    /**
     * @return ilAccessibilityCriterionConfig
     */
    public function getCriterionValue() : ilAccessibilityCriterionConfig;

    /**
     * @return string
     */
    public function getCriterionId() : string;
}
