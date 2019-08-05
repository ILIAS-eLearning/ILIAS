<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailLanguageHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLanguageHelper
{
    /**
     * @param string $isoCode
     * @return ilLanguage
     */
    public function getLanguageByIsoCode(string $isoCode) : ilLanguage
    {
        return ilLanguageFactory::_getLanguage($isoCode);
    }

    /**
     * @return ilLanguage
     */
    public function getCurrentLanguage() : ilLanguage
    {
        global $DIC;
        
        return $DIC->language();
    }
}