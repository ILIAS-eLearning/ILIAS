<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Language/interfaces/interface.ilLanguageDetector.php';

/**
 * Class ilDefaultLanguageDetector
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilDefaultLanguageDetector implements ilLanguageDetector
{
    /**
     * @var ilIniFile
     */
    protected $ini;

    /**
     * @param $ini ilIniFile
     */
    public function __construct(ilIniFile $ini)
    {
        $this->ini = $ini;
    }

    /**
     * Returns the detected ISO2 language code
     * @return string
     */
    public function getIso2LanguageCode()
    {
        return $this->ini->readVariable('language', 'default');
    }
}
