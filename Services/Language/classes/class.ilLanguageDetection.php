<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    public function detect(): string
    {
        global $DIC;
        $ilLog = $DIC->logger()->lang();

        $installed_languages = ilLanguage::_getInstalledLanguages();
        $detected_language = $installed_languages[0];

        foreach ($this->factory->getValidInstances() as $detector) {
            try {
                $language = $detector->getIso2LanguageCode();
                if (in_array($language, $installed_languages, true)) {
                    $detected_language = $language;
                }
            } catch (ilLanguageException $e) {
                $ilLog->warning($e->getMessage());
            }
        }

        return $detected_language;
    }
}
