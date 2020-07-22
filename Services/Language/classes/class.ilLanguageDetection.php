<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Language/classes/class.ilLanguage.php';
require_once 'Services/Language/classes/class.ilLanguageDetectorFactory.php';

/**
 * Class ilLanguageDetection
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilLanguageDetection
{
    /**
     * @var ilLanguageDetectorFactory
     */
    protected $factory;

    /**
     *
     */
    public function __construct()
    {
        $this->factory = new ilLanguageDetectorFactory();
    }

    /**
     * @return string
     */
    public function detect()
    {
        global $DIC;
        $ilLog = $DIC->logger()->root();

        $installed_languages = ilLanguage::_getInstalledLanguages();
        $detected_language = $installed_languages[0];

        foreach ($this->factory->getValidInstances() as $detector) {
            try {
                $language = $detector->getIso2LanguageCode();
                if (in_array($language, $installed_languages)) {
                    $detected_language = $language;
                }
            } catch (ilLanguageException $e) {
                $ilLog->info(__METHOD__ . ' ' . $e->getMessage());
            }
        }

        return $detected_language;
    }
}
