<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityDocumentCriteriaEvaluation
 */
interface ilAccessibilityDocumentCriteriaEvaluation
{
    /**
     * @param ilAccessibilitySignableDocument $document
     * @return bool
     */
    public function evaluate(ilAccessibilitySignableDocument $document) : bool;
}
