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
 */

declare(strict_types=1);

namespace ILIAS\Language;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class LanguageLegacyInitialisationAdapter implements Language
{
    public function txt(string $a_topic, string $a_default_lang_fallback_mod = ""): string
    {
        return $this->getLegacyLanguageInstance()->txt($a_topic, $a_default_lang_fallback_mod);
    }

    public function loadLanguageModule(string $a_module): void
    {
        $this->getLegacyLanguageInstance()->loadLanguageModule($a_module);
    }

    public function getLangKey(): string
    {
        return $this->getLegacyLanguageInstance()->getLangKey();
    }

    public function toJS($key): void
    {
        $this->getLegacyLanguageInstance()->toJS($key);
    }

    protected function getLegacyLanguageInstance(): Language
    {
        global $DIC;
        return $DIC->language();
    }
}
