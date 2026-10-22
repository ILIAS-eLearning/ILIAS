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

/**
 * The settings of the language administration changed by the Activities.
 *
 * @internal
 */
interface LanguageSettings
{
    public function setLanguageDetectionEnabled(bool $enabled): void;

    public function isPageTranslationEnabled(string $lang_key): bool;

    public function setPageTranslationEnabled(string $lang_key, bool $enabled): void;
}
