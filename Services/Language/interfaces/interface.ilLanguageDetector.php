<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilLanguageDetector
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
interface ilLanguageDetector
{
    /**
     * Returns the detected ISO2 language code
     * @return string
     */
    public function getIso2LanguageCode();
}
