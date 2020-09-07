<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceDocumentEvaluation
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceDocumentEvaluation
{
    /**
     * @return \ilTermsOfServiceSignableDocument
     * @throws \ilTermsOfServiceNoSignableDocumentFoundException
     */
    public function document() : \ilTermsOfServiceSignableDocument;

    /**
     * @return bool
     */
    public function hasDocument() : bool;
}
