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
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Setup\ShippedLanguageFilesCompiledObjective;

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
     * The directory configuration is part of the hash: with a class-only hash Setup could keep the
     * default-directory instance, which refreshes languages without the component-contributed modules.
     */
    public function getHash(): string
    {
        $directories = [];
        foreach ($this->il_setup_language->getLanguageFileDirectoryManager()->getAllDirectories() as $directory) {
            $directories[] = implode('|', [
                $directory::class,
                $directory->getPrefix(),
                $directory->getPath(),
                $directory->getSuffix(),
            ]);
        }

        return hash("sha256", implode("\n", [
            self::class,
            $this->il_setup_language->usesDefaultLanguageFileDirectories() ? 'default-directories' : 'configured-directories',
            ...$directories,
        ]));
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
        $preconditions = [new ilDatabaseInitializedObjective()];
        // with the default directories the build would serve no module at all
        if (!$this->il_setup_language->usesDefaultLanguageFileDirectories()) {
            $preconditions[] = new ShippedLanguageFilesCompiledObjective(
                $this->il_setup_language->getLanguageFileDirectoryManager(),
                dirname(__DIR__, 5)
            );
        }

        return $preconditions;
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        // Must come first: getInstallLanguages() reads from the database too.
        $this->useSetupDatabase($environment);
        $this->useSetupClientDataDir($environment);

        $language_keys = [];
        $invalid_language_keys = [];
        foreach ($this->getInstallLanguages() as $language_key) {
            if ($this->il_setup_language->checkLanguageForInstallation($language_key)) {
                $language_keys[] = $language_key;
            } else {
                $invalid_language_keys[] = $language_key;
            }
        }
        if ($invalid_language_keys !== []) {
            // an invalid language file only skips that language, the Setup run goes on
            $this->inform(
                $environment,
                'Skipped languages with invalid language files (left unchanged): '
                . implode(', ', $invalid_language_keys)
            );
        }

        $this->informAboutMissingClientDataDirectory($environment, $language_keys);
        $this->informAboutUnwritableOverlayDirectories($environment, $language_keys);
        $this->informAboutUnwrittenOverlays($environment, $this->installLanguages($language_keys));
        $this->informAboutSkippedInvalidMarkupEntries($environment);
        $this->informAboutInvalidMarkupInLocalChanges($environment, $language_keys);

        return $environment;
    }

    /**
     * Customizing entries with disallowed markup are not applied; the languages are installed regardless.
     */
    protected function informAboutSkippedInvalidMarkupEntries(Setup\Environment $environment): void
    {
        foreach ($this->il_setup_language->getSkippedInvalidMarkupEntries() as $language_key => $entries) {
            $this->inform(
                $environment,
                PlainLogText::of(
                    'WARNING: Customizing entries of language ' . $language_key . ' were not applied, their '
                    . 'value contains HTML that is not allowed in language texts: '
                    . PlainLogText::keyList(array_map('strval', array_keys($entries)))
                )
            );
        }
    }

    /**
     * Local changes written before the markup check existed are only reported, never changed:
     * that is the administrator's decision.
     *
     * @param list<string> $language_keys
     */
    protected function informAboutInvalidMarkupInLocalChanges(Setup\Environment $environment, array $language_keys): void
    {
        $policy = new TranslationMarkupPolicy();
        $manager = $this->il_setup_language->getLanguageFileDirectoryManager();
        $client_data_dir = $this->il_setup_language->getClientDataDir();
        foreach ($language_keys as $language_key) {
            $values = [];
            try {
                foreach ($this->il_setup_language->getLocalChanges($language_key) as $module => $entries) {
                    foreach ($entries as $identifier => $value) {
                        $values[$module . '#:#' . $identifier] = (string) $value;
                    }
                }
                foreach ($manager->getDirectories() as $directory) {
                    $module = $directory->getPrefix();
                    foreach (MigratedLanguageFileSync::loadLocalChanges($manager, $language_key, $module, $client_data_dir) ?? [] as $identifier => $entry) {
                        $values[$module . '#:#' . $identifier] = $entry['value'];
                    }
                }
            } catch (\Throwable $t) {
                error_log(PlainLogText::of(sprintf(
                    'Could not check the local changes of language "%s" for markup: %s',
                    $language_key,
                    $t->getMessage()
                )));
                continue;
            }

            $keys = array_map('strval', array_keys($policy->findInvalidValues($values)));
            if ($keys === []) {
                continue;
            }
            error_log(PlainLogText::of(sprintf(
                'Local changes of language "%s" contain markup that is not allowed: %s',
                $language_key,
                implode(', ', $keys)
            )));
            $this->inform(
                $environment,
                PlainLogText::of(
                    'WARNING: Local changes of language ' . $language_key . ' contain HTML that is not allowed '
                    . 'in language texts (left unchanged - please correct them in the administration): '
                    . PlainLogText::keyList($keys)
                )
            );
        }
    }

    /**
     * Typically during "setup install", before the client directory exists: the overlay of local changes
     * cannot be written, the web serves the shipped values (the build) until the next "setup update".
     *
     * @param list<string> $language_keys
     */
    protected function informAboutMissingClientDataDirectory(Setup\Environment $environment, array $language_keys): void
    {
        if (
            $language_keys === []
            || $this->il_setup_language->getClientDataDir() !== null
            || !$this->il_setup_language->hasMigratedModules($language_keys)
        ) {
            return;
        }

        $this->inform(
            $environment,
            'WARNING: The client data directory is not known or does not exist yet, so the local changes '
            . '(e.g. lang/*.lang.local) of modules maintained in PO files are not written to their overlay in '
            . 'this run - these modules show their shipped texts without local changes until then. Run "setup '
            . 'update" (as the web server user) once the client data directory exists.'
        );
    }

    /**
     * Checked before writing. Does not abort: the database is written regardless, the files are
     * repaired by the next run with sufficient permissions.
     *
     * @param list<string> $language_keys
     */
    protected function informAboutUnwritableOverlayDirectories(Setup\Environment $environment, array $language_keys): void
    {
        $unwritable_directories = $this->il_setup_language->findUnwritableOverlayDirectories($language_keys);
        if ($unwritable_directories !== []) {
            $this->inform(
                $environment,
                'WARNING: The PO/MO language files of modules maintained in PO files cannot be written to '
                . implode(', ', $unwritable_directories) . ' (not writable, or a file occupies the '
                . 'directory\'s place). The languages are still installed/updated in the database, but these '
                . 'modules keep showing outdated texts. Setup must be run as the web server user (the owner '
                . 'of the client data directory) - re-run "setup update" as that user to repair the files.'
            );
        }

        $this->informIfNotRunAsClientDataDirectoryOwner($environment, $language_keys);
    }

    /**
     * Writable for Setup is not enough: files created by another user (typically root) cannot be
     * replaced by the web server later. The owner of the client data directory counts as web server user.
     *
     * @param list<string> $language_keys
     */
    protected function informIfNotRunAsClientDataDirectoryOwner(Setup\Environment $environment, array $language_keys = []): void
    {
        $client_data_dir = $this->il_setup_language->getClientDataDir();
        if ($client_data_dir === null || !function_exists('posix_geteuid')) {
            return;
        }
        $owner = @fileowner($client_data_dir);
        $current_user = posix_geteuid();
        if ($owner === false || ($owner === $current_user && $current_user !== 0)) {
            return;
        }
        if (!$this->il_setup_language->hasMigratedModules($language_keys)) {
            return;
        }

        $overlay_root = rtrim($client_data_dir, '/') . '/lang';
        if ($owner === 0) {
            $this->inform(
                $environment,
                sprintf(
                    'WARNING: Setup runs as user id %d, and the client data directory %s belongs to root. '
                    . 'Running Setup as root is not supported: the web server cannot replace the PO/MO '
                    . 'language files and directories of modules maintained in PO files that this run '
                    . 'creates. Fix: run "chown -R <web server user> %s" after this run, and run Setup '
                    . 'as the web server user from now on.',
                    $current_user,
                    $client_data_dir,
                    $overlay_root
                )
            );
            return;
        }

        $owner_info = function_exists('posix_getpwuid') ? @posix_getpwuid($owner) : false;
        $owner_name = is_array($owner_info) ? (string) $owner_info['name'] : (string) $owner;
        $unwritable_for_owner = $this->il_setup_language->findUnwritableOverlayDirectories($language_keys, $owner);

        $this->inform(
            $environment,
            sprintf(
                'WARNING: Setup runs as user id %d, but the client data directory %s belongs to user id %d. '
                . 'Running Setup as another user than the web server user (the owner of the client data '
                . 'directory) is not supported%s: the PO/MO language files and directories of modules '
                . 'maintained in PO files that this run creates belong to user id %d and cannot be '
                . 'replaced by the web server later on.%s Fix: run "chown -R %s %s" after this run, and '
                . 'run Setup as user %s from now on.',
                $current_user,
                $client_data_dir,
                $owner,
                $current_user === 0 ? ' (this includes root)' : '',
                $current_user,
                $unwritable_for_owner === []
                    ? ''
                    : ' Already not writable for user id ' . $owner . ': ' . implode(', ', $unwritable_for_owner) . '.',
                $owner_name,
                $overlay_root,
                $owner_name
            )
        );
    }

    /**
     * @param list<string> $language_keys languages whose PO/MO overlay could not be written
     */
    protected function informAboutUnwrittenOverlays(Setup\Environment $environment, array $language_keys): void
    {
        if ($language_keys === []) {
            return;
        }

        $this->inform(
            $environment,
            'WARNING: The PO/MO language files of modules maintained in PO files could not be written for '
            . 'the languages ' . implode(', ', $language_keys) . ' (see the PHP error log for details). '
            . 'Setup must be run as the web server user - re-run "setup update" as that user.'
        );
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
     * Must run before anything is read from ilSetupLanguage, so that the language list comes from the Setup database.
     */
    protected function useSetupDatabase(Setup\Environment $environment): void
    {
        $this->il_setup_language->setDbHandler(
            $environment->getResource(Setup\Environment::RESOURCE_DATABASE)
        );
    }

    /**
     * Taken from the Setup environment, which knows it before ilias.ini.php is written;
     * otherwise ilSetupLanguage falls back to ilias.ini.php.
     */
    protected function useSetupClientDataDir(Setup\Environment $environment): void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        if (!$ini instanceof ilIniFile || $client_id === null) {
            return;
        }

        $client_data_dir = \ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths::fromDataDirAndClientId(
            (string) $ini->readVariable('clients', 'datadir'),
            (string) $client_id
        );
        if ($client_data_dir !== null) {
            $this->il_setup_language->setClientDataDir($client_data_dir);
        }
    }

    protected function inform(Setup\Environment $environment, string $message): void
    {
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof Setup\AdminInteraction) {
            $io->inform($message);
            return;
        }
        error_log($message);
    }

    /**
     * Installs missing languages and refreshes installed ones on every run, so that changed language files take effect.
     *
     * Both Activities get the full list and ignore keys that do not apply to them; UpdateLanguage also
     * runs on just-installed keys, which is harmless.
     *
     * An instance with only the default directories never refreshes: that would delete the lng_data
     * rows of component-contributed modules.
     *
     * @param list<string> $language_keys
     * @return list<string> the languages whose PO/MO overlay could not be written (see
     *         InstallLanguage/UpdateLanguage, "overlay_write_failed_language_keys")
     */
    protected function installLanguages(array $language_keys): array
    {
        if ($language_keys === []) {
            return [];
        }

        $install_result = $this->install_language->perform([
            'language_keys' => $language_keys,
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);
        $overlay_write_failed = $install_result['overlay_write_failed_language_keys'] ?? [];

        if (!$this->il_setup_language->usesDefaultLanguageFileDirectories()) {
            $update_result = $this->update_language->perform([
                'language_keys' => $language_keys,
            ]);
            $overlay_write_failed = array_merge(
                $overlay_write_failed,
                $update_result['overlay_write_failed_language_keys'] ?? []
            );
        }

        return array_values(array_unique($overlay_write_failed));
    }
}
