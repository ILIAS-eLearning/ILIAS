<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceDocumentCriteriaEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentCriteriaEvaluation
{
    /**
     * @param ilTermsOfServiceSignableDocument $document
     * @param ilObjUser|null $user
     * @return bool
     */
    public function evaluate(ilTermsOfServiceSignableDocument $document, ilObjUser $user = null) : bool;
}
