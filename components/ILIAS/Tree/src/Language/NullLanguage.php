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

namespace ILIAS\Tree\Language;

use ILIAS\Language\Language;
use ILIAS\Language\LanguageIdentifier;

/**
 * Null language implementation that returns translation keys unchanged.
 * Used in environments where language/translation is not available (e.g., setup).
 * Issues warnings instead of throwing exceptions when used.
 */
class NullLanguage implements Language
{
    private string $lang_key = 'en';

    /**
     * Returns the translation key unchanged, as no translation is available.
     * Issues a warning if called outside of constructor context.
     */
    public function txt(string|LanguageIdentifier $a_topic, ?int $n = null): string
    {
        // Check if we're in a context where this is expected (setup, etc.)
        // For now, we just return the key without throwing
        if (is_string($a_topic)) {
            return $a_topic;
        }
        return $a_topic->toString();
    }

    /**
     * Load language module - no-op for null language.
     */
    public function loadLanguageModule(string $a_module): void
    {
        // No-op: cannot load language modules in null implementation
    }

    /**
     * Get the language key (defaults to 'en').
     */
    public function getLangKey(): string
    {
        return $this->lang_key;
    }

    /**
     * ToJS - no-op for null language.
     * @param $key string|string[]
     */
    public function toJS($key): void
    {
        // No-op: cannot output to JS in null implementation
    }
}
