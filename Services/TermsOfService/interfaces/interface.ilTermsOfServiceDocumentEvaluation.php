<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceDocumentEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentEvaluation
{
    /**
     * Returns an evaluator like this with the passed context user
     * @param ilObjUser $user
     * @return ilTermsOfServiceDocumentEvaluation
     */
    public function withContextUser(ilObjUser $user) : ilTermsOfServiceDocumentEvaluation;

    /**
     * Determines a document based on the context of the concrete implementation
     * @return ilTermsOfServiceSignableDocument
     * @throws ilTermsOfServiceNoSignableDocumentFoundException
     */
    public function document() : ilTermsOfServiceSignableDocument;

    /**
     * @return bool
     */
    public function hasDocument() : bool;

    /**
     * Evaluates the passed document for the context given in the concrete implementation
     * @param ilTermsOfServiceSignableDocument $document
     * @return bool
     */
    public function evaluateDocument(ilTermsOfServiceSignableDocument $document) : bool;
}
