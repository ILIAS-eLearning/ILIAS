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

namespace ILIAS\Language\ComponentTranslation;

/**
 * The values the ILIAS modules serve at runtime: the shipped ones with the local changes on top (see ilLanguage).
 * Read-only. Strings and arrays instead of objects (deviates from repository-pattern.md): hot path of every txt().
 */
interface TranslationRepository
{
    /**
     * Identifier => $module, `null` if $module is not served for $lang_key.
     *
     * @param string|null $client_data_dir where the local changes are looked up, none without
     * @return array<string, string>|null
     */
    public function getKeys(string $module, string $lang_key, ?string $client_data_dir): ?array;

    /**
     * `null` if there is none (not served, not translated, or a problem, see MigratedTranslations::getProblem()).
     */
    public function getText(string $module, string $lang_key, string $key, ?string $client_data_dir): ?string;

    /**
     * The form the plural rule of $lang_key selects for $n; `null` if $key is no plural message there.
     */
    public function getPluralText(string $module, string $lang_key, string $key, int $n, ?string $client_data_dir): ?string;
}
