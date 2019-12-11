<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceEvaluableCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceEvaluableCriterion
{
    /**
     * @return \ilTermsOfServiceCriterionConfig
     */
    public function getCriterionValue() : \ilTermsOfServiceCriterionConfig;

    /**
     * @return string
     */
    public function getCriterionId() : string;
}
