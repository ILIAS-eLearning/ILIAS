<?php

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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Activities;

use ILIAS\Administration\Setting;

/**
 * No component offers the settings yet, so they are taken from the legacy container on every call.
 *
 * @internal Only the Language component shall provide an instance of this.
 */
final class LanguageSettingsLegacyProxy implements LanguageSettings
{
    public function setLanguageDetectionEnabled(bool $enabled): void
    {
        $this->settings()->set('lang_detection', $enabled ? '1' : '0');
    }

    public function isPageTranslationEnabled(string $lang_key): bool
    {
        return (bool) $this->settings()->get('lang_translate_' . $lang_key, '0');
    }

    public function setPageTranslationEnabled(string $lang_key, bool $enabled): void
    {
        $this->settings()->set('lang_translate_' . $lang_key, $enabled ? '1' : '0');
    }

    private function settings(): Setting
    {
        global $DIC;

        return $DIC->settings();
    }
}
