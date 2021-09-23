<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailLanguageHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLanguageHelper
{
    public function getLanguageByIsoCode(string $isoCode) : ilLanguage
    {
        return ilLanguageFactory::_getLanguage($isoCode);
    }

    public function getCurrentLanguage() : ilLanguage
    {
        global $DIC;
        
        return $DIC->language();
    }
}
