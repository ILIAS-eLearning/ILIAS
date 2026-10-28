<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Language;
use ILIAS\Language\Setup\InstalledLanguageRepository;

/**
 * In-memory stand-in for InstalledLanguageRepository (the real one needs a database); only
 * getInstalledLanguages() is supported, every other method throws.
 */
final class FakeInstalledLanguageRepository implements InstalledLanguageRepository
{
    public function __construct(
        private readonly \Closure $installed_languages,
    ) {
    }

    public function getInstalledLanguages(): array
    {
        return ($this->installed_languages)();
    }

    public function getInstalledLocalLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getAvailableLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getLocalChanges(string $lang_key, string $min_date = "", string $max_date = ""): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getLanguageEntries(string $lang_key): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getLocalLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getInstallableLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getInvalidLocalLanguageFiles(array $language_keys): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function checkLanguage(string $lang_key): bool
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function checkLocalLanguageFile(string $lang_key): bool
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }
}
