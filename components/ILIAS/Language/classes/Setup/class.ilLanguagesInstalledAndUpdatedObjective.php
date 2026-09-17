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

    /**
     * @param InstallLanguage|null $install_language Callers wired through
     *        the component graph (see ilLanguageSetupAgent) pass the
     *        resolved instance. Callers that are themselves constructed
     *        deep inside the Setup Objective tree -
     *        ilComponentPluginAdminInitObjective and
     *        ilPluginLanguageUpdatedObjective, reached via
     *        ilPluginDefaultAgent, which only ever receives a plugin name -
     *        have nothing to inject and omit it; a Setup-only instance is
     *        then built here (InstallLanguage::forSetup(), which needs the
     *        concrete class, not just any Activity). This is the single
     *        place that knows how, instead of each of those call sites
     *        reaching into $GLOBALS['DIC'] for a key that Setup never
     *        registers.
     * @param UpdateLanguage|null $update_language Same reasoning as
     *        $install_language above.
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
        return $this->il_setup_language->getInstalledLanguages() ?: ['en'];
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Install/Update languages " . implode(", ", $this->getInstallLanguages());
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
     * Installs every given key that is not yet installed, and refreshes
     * every given key that already is - unconditionally, this Objective's
     * job (label "Install/Update languages") is to keep already-installed
     * languages in sync with the current language files on every setup.php
     * run (e.g. after a core update added or changed translations), not
     * just to install missing ones.
     *
     * Both Activities are handed the same full $language_keys list and each
     * decides for itself what applies to it: InstallLanguage::MODE_INSTALL
     * installs a not-yet-installed key and is a no-op for an already
     * installed one; UpdateLanguage refreshes an already installed key and
     * is a no-op for one that is not installed. Neither call needs the
     * caller to pre-filter by install status. Running UpdateLanguage on the
     * keys InstallLanguage just installed is intentional, not wasted work
     * avoided: it keeps this method's behaviour simple (one full list, two
     * independent Activities) and the freshly installed keys are also
     * freshly refreshed, which is harmless.
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
