<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceDocumentCriteriaEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentCriteriaEvaluation
{
    /**
     * Evaluates a document for the context given by the concrete implementation
     * @param ilTermsOfServiceSignableDocument $document
     * @return bool
     */
    public function evaluate(ilTermsOfServiceSignableDocument $document) : bool;

    /**
     * Returns a criteria evaluator like this with the passed context user
     * @param ilObjUser $user
     * @return ilTermsOfServiceDocumentCriteriaEvaluation
     */
    public function withContextUser(ilObjUser $user) : ilTermsOfServiceDocumentCriteriaEvaluation;
}
