<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceDocumentEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentEvaluation
{
    /**
     * @param ilObjUser|null $user
     * @return ilTermsOfServiceSignableDocument
     * @throws ilTermsOfServiceNoSignableDocumentFoundException
     */
    public function document(ilObjUser $user = null) : ilTermsOfServiceSignableDocument;

    /**
     * @param ilObjUser|null $user
     * @return bool
     */
    public function hasDocument(ilObjUser $user = null) : bool;

    /**
     * Evaluates the passed document
     * @param ilTermsOfServiceSignableDocument $document
     * @param ilObjUser|null $user
     * @return bool
     */
    public function evaluateDocument(ilTermsOfServiceSignableDocument $document, ilObjUser $user = null) : bool;
}
