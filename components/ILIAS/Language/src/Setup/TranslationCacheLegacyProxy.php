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

namespace ILIAS\Language\Setup;

/**
 * Delegates to ilCachedLanguage at call time, so it can be built at build time without the global cache.
 */
final class TranslationCacheLegacyProxy implements TranslationCache
{
    public function invalidate(string $lang_key): void
    {
        // avoid flushing the whole cache (see mantis #28818)
        \ilCachedLanguage::getInstance($lang_key)->deleteInCache();
    }
}
