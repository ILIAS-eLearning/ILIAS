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

use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Data\Description;
use ILIAS\Data\Text;
use ILIAS\Language\LanguageFolder;
use ILIAS\Language\LanguageFolderLegacyProxy;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class InstallLanguage extends LanguageActivity
{
    use ParsesLanguageKeyList;

    public const string MODE_INSTALL = 'install';
    public const string MODE_INSTALL_LOCAL = 'install_local';

    public function __construct(
        ?RBAC $rbac,
        private readonly \ilSetupLanguage $setup_language,
        LanguageFolder $language_folder,
    ) {
        parent::__construct($rbac, $language_folder);
    }

    public static function forSetup(\ilSetupLanguage $setup_language): self
    {
        return new self(
            null,
            $setup_language,
            // never read: without RBAC, isAllowedToPerform() throws first
            new LanguageFolderLegacyProxy()
        );
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            <<<'MARKDOWN'
Installs one or more languages in the ILIAS system, or applies just their
customizing/local language file, depending on the chosen mode.

Mode "install" fully installs a language that is not yet installed (base
data, plus a customizing/local file if one exists) and leaves an already
installed language completely untouched. Mode "install_local" instead
(re-)applies only the customizing/local file on top of an already installed
language, without touching its base data, and leaves a not-yet-installed
language completely untouched.

In both modes, invalid language files (including a shipped `.po`) reject the
request as a whole before anything is written.
MARKDOWN
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        $language_keys = $f->text(
            'Language keys',
            'Comma-separated list of language keys, e.g. de, fr, it.'
        )->withRequired(true)->withDedicatedName('language_keys');

        $mode = $f->select(
            'Mode',
            [
                self::MODE_INSTALL => 'Install',
                self::MODE_INSTALL_LOCAL => 'Install local',
            ],
            'Whether to fully install the given languages, or to only ' .
            '(re-)apply their customizing/local file on top of an existing ' .
            'installation.'
        )->withRequired(true)->withDedicatedName('mode');

        return $f->section([
            'language_keys' => $language_keys,
            'mode' => $mode,
        ], 'Install languages');
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('Result of the language installation.'),
            [
                'installed_language_keys' => $f->list(
                    $text->simpleDocument('Newly installed languages without a custom language file.'),
                    $f->string($text->simpleDocument('Language key of a newly installed language.'))
                ),
                'installed_with_local_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Languages for which a custom/local language file was (re-)installed - ' .
                        'either as part of a fresh installation (mode "install"), or applied on ' .
                        'top of an already installed language (mode "install_local").'
                    ),
                    $f->string($text->simpleDocument('Language key of a language with an installed custom language file.'))
                ),
                'already_installed_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Languages for which this run changed nothing: mode "install" was ' .
                        'requested for a language that was already installed (a no-op by design ' .
                        '- use mode "install_local" to (re-)apply a customizing file instead), ' .
                        'or mode "install_local" was requested for an installed language that ' .
                        'has no customizing/local file to apply.'
                    ),
                    $f->string($text->simpleDocument('Language key of an already installed language.'))
                ),
                'not_installed_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Languages requested with mode "install_local" that are not installed - ' .
                        'skipped entirely, since there is nothing installed yet to apply local ' .
                        'changes on top of.'
                    ),
                    $f->string($text->simpleDocument('Language key of a not-yet-installed language.'))
                ),
                'invalid_local_language_files' => $f->list(
                    $text->simpleDocument('Local language files with an invalid file name.'),
                    $f->string($text->simpleDocument('File name of an invalid local language file.'))
                ),
                'overlay_write_failed_language_keys' => $this->overlayWriteFailedOutputField($f),
                'invalid_markup_customizing_entries' => $this->invalidMarkupCustomizingOutputField($f),
            ]
        );
    }

    public function perform(mixed $parameters): array
    {
        if (!is_array($parameters)) {
            throw new InvalidInputException('Parameters must be an array.');
        }

        $language_keys = $this->toLanguageKeyList($parameters['language_keys'] ?? null);
        $mode = $this->toMode($parameters['mode'] ?? null);

        $currently_installed_language_keys = $this->setup_language->getInstalledLanguages();

        $to_fully_install = [];
        $to_apply_local_changes = [];
        $already_installed_no_op = [];
        $not_installed_no_op = [];

        foreach ($language_keys as $language_key) {
            $is_installed = in_array($language_key, $currently_installed_language_keys, true);
            if ($mode === self::MODE_INSTALL) {
                if ($is_installed) {
                    $already_installed_no_op[] = $language_key;
                } else {
                    $to_fully_install[] = $language_key;
                }
            } elseif ($is_installed) {
                $to_apply_local_changes[] = $language_key;
            } else {
                $not_installed_no_op[] = $language_key;
            }
        }

        $error_language_keys = [];
        // "install_local" merges the shipped .po as well, so an invalid one is refused before writing too
        foreach (array_merge($to_fully_install, $to_apply_local_changes) as $language_key) {
            if (!$this->setup_language->checkLanguageForInstallation($language_key)) {
                $error_language_keys[] = $language_key;
            }
        }

        if ($error_language_keys !== []) {
            // a language without a language file is the caller's mistake, an invalid file the server's
            $unknown_language_keys = array_values(array_diff(
                array_intersect($error_language_keys, $to_fully_install),
                $this->setup_language->getInstallableLanguagesForInstallation()
            ));
            if ($unknown_language_keys !== []) {
                throw new InvalidInputException('Unknown languages: ' . implode(', ', $unknown_language_keys));
            }
            throw new InvalidLanguageFilesException($error_language_keys);
        }

        $installed_language_keys = [];
        $installed_with_local_language_keys = [];
        $invalid_local_language_files = [];
        $overlay_write_failed_language_keys = [];

        $affected_language_keys = array_merge($to_fully_install, $to_apply_local_changes);
        if ($affected_language_keys !== []) {
            $db_languages = $this->setup_language->getAvailableLanguagesForInstallation();
            $local_language_keys = $this->setup_language->getLocalLanguages();
            $invalid_local_language_files = $this->setup_language->getInvalidLocalLanguageFiles(
                $affected_language_keys
            );

            foreach ($to_fully_install as $language_key) {
                $this->setup_language->flushLanguageForInstallation($language_key);
                if ($this->setup_language->insertLanguageForInstallation($language_key) !== []) {
                    $overlay_write_failed_language_keys[] = $language_key;
                }
                $this->setup_language->registerInstalledLanguage($language_key, $db_languages, $local_language_keys);

                if (in_array($language_key, $local_language_keys, true)) {
                    $installed_with_local_language_keys[] = $language_key;
                } else {
                    $installed_language_keys[] = $language_key;
                }
            }

            foreach ($to_apply_local_changes as $language_key) {
                if ($this->setup_language->insertLanguageForApplyingLocalChanges($language_key) !== []) {
                    $overlay_write_failed_language_keys[] = $language_key;
                }
                $this->setup_language->registerInstalledLanguage($language_key, $db_languages, $local_language_keys);

                if (in_array($language_key, $local_language_keys, true)) {
                    $installed_with_local_language_keys[] = $language_key;
                } else {
                    // "install_local" had nothing to apply: same as "already installed"
                    $already_installed_no_op[] = $language_key;
                }
            }
        }

        return [
            'installed_language_keys' => $installed_language_keys,
            'installed_with_local_language_keys' => $installed_with_local_language_keys,
            'already_installed_language_keys' => $already_installed_no_op,
            'not_installed_language_keys' => $not_installed_no_op,
            'invalid_local_language_files' => $invalid_local_language_files,
            'overlay_write_failed_language_keys' => $overlay_write_failed_language_keys,
            'invalid_markup_customizing_entries' => $this->invalidMarkupCustomizingEntries(
                $this->setup_language->getSkippedInvalidMarkupEntries(),
                $affected_language_keys
            ),
        ];
    }

    private function toMode(mixed $value): string
    {
        if ($value === self::MODE_INSTALL || $value === self::MODE_INSTALL_LOCAL) {
            return $value;
        }

        throw new InvalidInputException(
            'mode must be either "' . self::MODE_INSTALL . '" or "' . self::MODE_INSTALL_LOCAL . '".'
        );
    }

    /**
     * @param array{language_keys: string, mode: string} $grind_result
     * @return array{language_keys: list<string>, mode: string}
     */
    protected function normalizeParameters(array $grind_result): array
    {
        return [
            'language_keys' => $this->toLanguageKeyList($grind_result['language_keys']),
            'mode' => $this->toMode($grind_result['mode']),
        ];
    }
}
