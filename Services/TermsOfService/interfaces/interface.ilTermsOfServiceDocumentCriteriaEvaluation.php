<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceDocumentCriteriaEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentCriteriaEvaluation
{
    /**
     * @param \ilTermsOfServiceSignableDocument $document
     * @return bool
     */
    public function evaluate(\ilTermsOfServiceSignableDocument $document) : bool;
}
