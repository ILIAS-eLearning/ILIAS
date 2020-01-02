<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceSignableDocumentFactory
{
    /**
     * @param ilLanguage $lng
     * @return ilTermsOfServiceSignableDocument
     */
    public static function getByLanguageObject(ilLanguage $lng)
    {
        try {
            require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceFileSystemDocument.php';
            $document = new ilTermsOfServiceFileSystemDocument($lng);
            $document->determine();
        } catch (ilTermsOfServiceNoSignableDocumentFoundException $e) {
            require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceNullDocument.php';
            $document = new ilTermsOfServiceNullDocument();
        }

        return $document;
    }
}
