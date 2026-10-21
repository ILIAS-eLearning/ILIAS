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
 * Test double for TranslationRepository: serves what the test hands in, no files, build or database.
 * Plural forms follow the Germanic rule (n == 1 selects form 0, every other n form 1).
 */
final class TranslationInMemoryRepository implements TranslationRepository
{
    /**
     * @param array<string, array<string, array<string, string>>> $texts module => language => identifier => value
     * @param array<string, array<string, array<string, list<string>>>> $plurals module => language => identifier => forms
     */
    public function __construct(
        private array $texts = [],
        private array $plurals = [],
    ) {
    }

    public function getKeys(string $module, string $lang_key, ?string $client_data_dir): ?array
    {
        if (!isset($this->texts[$module][$lang_key]) && !isset($this->plurals[$module][$lang_key])) {
            return null;
        }
        $keys = [];
        foreach (array_merge(array_keys($this->texts[$module][$lang_key] ?? []), array_keys($this->plurals[$module][$lang_key] ?? [])) as $key) {
            $keys[(string) $key] = $module;
        }

        return $keys;
    }

    public function getText(string $module, string $lang_key, string $key, ?string $client_data_dir): ?string
    {
        return $this->texts[$module][$lang_key][$key] ?? null;
    }

    public function getPluralText(string $module, string $lang_key, string $key, int $n, ?string $client_data_dir): ?string
    {
        $forms = $this->plurals[$module][$lang_key][$key] ?? null;

        return $forms === null ? null : $forms[$n === 1 ? 0 : 1] ?? null;
    }
}
