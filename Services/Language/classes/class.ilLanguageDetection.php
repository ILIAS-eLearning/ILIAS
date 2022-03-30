<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Language/classes/class.ilLanguage.php";
require_once "Services/Language/classes/class.ilLanguageDetectorFactory.php";

/**
 * Class ilLanguageDetection
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilLanguageDetection
{
    protected ilLanguageDetectorFactory $factory;

    public function __construct()
    {
        $this->factory = new ilLanguageDetectorFactory();
    }

    /**
     * Return detected language
     */
    public function detect() : string
    {
        global $DIC;
        $ilLog = $DIC->logger()->lang();

        $installed_languages = ilLanguage::_getInstalledLanguages();
        $detected_language = $installed_languages[0];

        foreach ($this->factory->getValidInstances() as $detector) {
            try {
                $language = $detector->getIso2LanguageCode();
                if (in_array($language, $installed_languages)) {
                    $detected_language = $language;
                }
            } catch (ilLanguageException $e) {
                $ilLog->warning($e->getMessage());
            }
        }

        return $detected_language;
    }
}
