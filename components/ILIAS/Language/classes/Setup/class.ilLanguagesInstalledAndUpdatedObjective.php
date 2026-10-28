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

use ILIAS\Setup;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;

class ilLanguagesInstalledAndUpdatedObjective extends ilLanguageObjective
{
    protected \ilSetupLanguage $il_setup_language;
    protected InstallLanguage $install_language;
    protected UpdateLanguage $update_language;
    /** @var list<string>|null */
    private ?array $install_languages = null;

    /**
     * @param InstallLanguage|null $install_language null only for callers constructed deep inside the Setup tree
     *        (plugin objectives); a Setup-only instance is then built here instead of reading $GLOBALS['DIC']
     * @param UpdateLanguage|null $update_language see $install_language
     */
    public function __construct(
        \ilSetupLanguage $il_setup_language,
        ?InstallLanguage $install_language = null,
        ?UpdateLanguage $update_language = null
    ) {
        $this->il_setup_language = $il_setup_language;
        $this->install_language = $install_language ?? InstallLanguage::forSetup($il_setup_language);
        $this->update_language = $update_language ?? UpdateLanguage::forSetup($il_setup_language);
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * Return installed languages
     */
    protected function getInstallLanguages(): array
    {
        return $this->install_languages ??= $this->il_setup_language->getInstalledLanguages() ?: ['en'];
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        // No database access here: the Setup CLI calls this before achieve().
        if ($this->install_languages === null) {
            return "Install/Update languages";
        }
        return "Install/Update languages " . implode(", ", $this->install_languages);
    }

    /**
     * @inheritDoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        // Must come first: getInstallLanguages() reads from the database too.
        $this->useSetupDatabase($environment);
        $this->installLanguages($this->getInstallLanguages());

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        // getLabel() is shown between isApplicable() and achieve() and lists the languages.
        if ($environment->getResource(Setup\Environment::RESOURCE_DATABASE) instanceof \ilDBInterface) {
            $this->useSetupDatabase($environment);
            $this->getInstallLanguages();
        }

        return true;
    }

    /**
     * Hands the Setup-provided database resource to ilSetupLanguage, which
     * is the single place resolving it for everything that follows: the
     * Activity needs no database of its own, and ilSetupLanguage's
     * repository and manager re-read it on every call.
     *
     * achieve() must call this before reading anything from
     * ilSetupLanguage, so that the language list is determined against the
     * Setup database too. Overwriting $GLOBALS['ilDB'] around the install -
     * which this used to do, with a TODO attached - is no longer necessary.
     */
    protected function useSetupDatabase(Setup\Environment $environment): void
    {
        $this->il_setup_language->setDbHandler(
            $environment->getResource(Setup\Environment::RESOURCE_DATABASE)
        );
    }

    /**
     * Installs missing languages and refreshes installed ones on every run, so that changed language files take effect.
     *
     * Each Activity validates and throws \RuntimeException only for the
     * keys it actually processes (see InstallLanguage::perform() and
     * UpdateLanguage::perform()); a validation error in the first call
     * prevents the second from running at all, instead of collecting
     * invalid keys from both into a single combined error.
     *
     * @param list<string> $language_keys
     */
    protected function installLanguages(array $language_keys): void
    {
        $this->install_language->perform([
            'language_keys' => $language_keys,
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->update_language->perform([
            'language_keys' => $language_keys,
        ]);
    }
}
