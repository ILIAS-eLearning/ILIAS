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

class UpdateLanguage extends LanguageActivity
{
    use DeclaresLanguageKeysOnlyInput;

    /** @param RepositoryProvider|null $repository null in the Setup, whose plugin objectives apply the plugin language files */
    public function __construct(
        ?RBAC $rbac,
        private readonly \ilSetupLanguage $setup_language,
        LanguageFolder $language_folder,
        private readonly ?RepositoryProvider $repository = null,
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
Refreshes one or more already installed languages, re-seeding their base
data (plus a customizing/local file if one exists) from the current
language files; the language files of the active plugins are applied again.
A language that is not installed is left completely untouched - use
InstallLanguage to install it first.
MARKDOWN
        );
    }

    protected function inputSectionTitle(): string
    {
        return 'Update languages';
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('Result of the language update.'),
            [
                'updated_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Already installed languages that were refreshed from the current ' .
                        'language files (base data, plus a customizing/local file if one exists).'
                    ),
                    $f->string($text->simpleDocument('Language key of a refreshed language.'))
                ),
                'not_installed_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Requested languages that are not installed - skipped entirely, since ' .
                        'there is nothing installed yet to refresh. Use InstallLanguage to ' .
                        'install them first.'
                    ),
                    $f->string($text->simpleDocument('Language key of a not-yet-installed language.'))
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

        $currently_installed_language_keys = $this->setup_language->getInstalledLanguages();

        $to_update = [];
        $not_installed_no_op = [];

        foreach ($language_keys as $language_key) {
            if (in_array($language_key, $currently_installed_language_keys, true)) {
                $to_update[] = $language_key;
            } else {
                $not_installed_no_op[] = $language_key;
            }
        }

        $error_language_keys = [];
        foreach ($to_update as $language_key) {
            if (!$this->setup_language->checkLanguageForInstallation($language_key)) {
                $error_language_keys[] = $language_key;
            }
        }

        if ($error_language_keys !== []) {
            throw new InvalidLanguageFilesException($error_language_keys);
        }

        $overlay_write_failed_language_keys = [];
        if ($to_update !== []) {
            $db_languages = $this->setup_language->getAvailableLanguagesForInstallation();
            $local_language_keys = $this->setup_language->getLocalLanguages();

            foreach ($to_update as $language_key) {
                $this->setup_language->flushLanguageForInstallation($language_key);
                if ($this->setup_language->insertLanguageForInstallation($language_key) !== []) {
                    $overlay_write_failed_language_keys[] = $language_key;
                }
                $this->setup_language->registerInstalledLanguage($language_key, $db_languages, $local_language_keys);
            }

            // plugin language files are flushed with the rest, so re-apply them
            $this->repository?->refreshPlugins($to_update);
        }

        return [
            'updated_language_keys' => $to_update,
            'not_installed_language_keys' => $not_installed_no_op,
            'overlay_write_failed_language_keys' => $overlay_write_failed_language_keys,
            'invalid_markup_customizing_entries' => $this->invalidMarkupCustomizingEntries(
                $this->setup_language->getSkippedInvalidMarkupEntries(),
                $to_update
            ),
        ];
    }
}
