<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceSignableDocument.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceNullDocument implements ilTermsOfServiceSignableDocument
{
    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getSource()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getSourceType()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getIso2LanguageCode()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function determine()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function exists()
    {
        return false;
    }
}
