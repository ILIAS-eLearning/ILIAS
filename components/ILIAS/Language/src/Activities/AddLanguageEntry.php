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
use ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy;
use ILIAS\Data\Description;
use ILIAS\Data\Text;
use ILIAS\Language\Language;
use ILIAS\Language\LanguageFolder;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class AddLanguageEntry extends LanguageActivity
{
    public function __construct(
        private readonly Language $lng,
        RBAC $rbac,
        LanguageFolder $language_folder,
        private readonly RepositoryProvider $repository,
    ) {
        parent::__construct($rbac, $language_folder);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            <<<'MARKDOWN'
Adds one new "adjust language variables" entry (a module/identifier pair) to
every currently installed language for which a value was given. An installed
language for which no value (or only a blank one) was given is left
completely untouched.

"de" and "en" are mandatory whenever they are installed: a missing or blank
value for either rejects the request as a whole - nothing is written for any
language, not even ones with a perfectly valid value given.

An identifier that already exists in another module or in another spelling
is rejected as well, unless the module already holds it in this spelling.
MARKDOWN
        );
    }

    /**
     * Signature must stay exactly the Activity's (no extra parameter), so the installed languages
     * are resolved here and may differ from perform()'s snapshot; perform() then fails closed.
     */
    public function getInputDescription(FieldFactory $f): FormInput
    {
        $installed_language_keys = $this->repository->getInstalledLanguages();

        // labels need the "meta" module loaded by the caller; loading it here would clobber keys in the shared $lng
        $module = $f->text(
            'Module',
            'Name of the language module the new entry belongs to.'
        )->withRequired(true)->withDedicatedName('module');

        $identifier = $f->text(
            'Identifier',
            'Identifier (topic) of the new language entry.'
        )->withRequired(true)->withDedicatedName('identifier');

        $translation_fields = [];
        foreach ($installed_language_keys as $lang_key) {
            $is_mandatory = in_array($lang_key, ['de', 'en'], true);
            $translation_fields[$lang_key] = $f->text(
                $this->lng->txt('meta_l_' . $lang_key)
            )->withRequired($is_mandatory)->withDedicatedName($lang_key);
        }

        $translations = $f->group(
            $translation_fields,
            'Translations',
            'Value of the new entry for each installed language; "de" and "en" are mandatory ' .
            '(if installed) - a missing/blank value for either rejects the whole request, ' .
            'nothing is written for any language. Every other installed language is optional ' .
            'and simply skipped if left blank.'
        )->withDedicatedName('translations');

        return $f->section([
            'module' => $module,
            'identifier' => $identifier,
            'translations' => $translations,
        ], 'Add a language entry');
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('Result of adding the language entry.'),
            [
                'module' => $f->string($text->simpleDocument('Language module the new entry was added to.')),
                'identifier' => $f->string($text->simpleDocument('Identifier of the new language entry.')),
                'added_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Installed languages for which a non-blank value was given - the entry was ' .
                        'written for them.'
                    ),
                    $f->string($text->simpleDocument('Language key of a language the entry was added for.'))
                ),
                'skipped_empty_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Installed languages for which no value (or only a blank one) was given - ' .
                        'left completely untouched, since there is nothing to write for them.'
                    ),
                    $f->string($text->simpleDocument('Language key of a language with no given value.'))
                ),
                'overlay_write_failed_language_keys' => $this->overlayWriteFailedOutputField($f),
            ]
        );
    }

    /**
     * @param mixed $parameters may carry a trusted `usr_id` (int), set by maybePerformAs();
     *        it is recorded as the author of the local change, without it no author is attributed.
     */
    public function perform(mixed $parameters): array
    {
        if (!is_array($parameters)) {
            throw new InvalidInputException('Parameters must be an array.');
        }

        $module = $parameters['module'] ?? null;
        $identifier = $parameters['identifier'] ?? null;
        $translations = $parameters['translations'] ?? null;
        $usr_id = $parameters['usr_id'] ?? null;

        if (!is_string($module) || $module === ''
            || !is_string($identifier) || $identifier === ''
            || !is_array($translations)
            || ($usr_id !== null && !is_int($usr_id))
        ) {
            throw new InvalidInputException(
                'The module, identifier and translations parameters are required; usr_id, if given, must be an int.'
            );
        }

        $duplicates = $this->repository->findDuplicatesOf($module, $identifier);
        if ($duplicates !== []) {
            throw new InvalidInputException(
                'The identifier must be unique across all modules, it already exists in another module or in another spelling: '
                . implode(', ', $duplicates) . '.'
            );
        }

        $installed_language_keys = $this->repository->getInstalledLanguages();

        $missing_mandatory_language_keys = [];
        foreach (['de', 'en'] as $mandatory_lang_key) {
            if (!in_array($mandatory_lang_key, $installed_language_keys, true)) {
                continue;
            }

            $value = $translations[$mandatory_lang_key] ?? '';
            $value = is_string($value) ? trim($value) : '';

            if (!$value) {
                $missing_mandatory_language_keys[] = $mandatory_lang_key;
            }
        }

        if ($missing_mandatory_language_keys !== []) {
            throw new InvalidInputException(
                'A value is required for: ' . implode(', ', $missing_mandatory_language_keys) . '.'
            );
        }

        // all languages are checked before anything is written; only language keys are named, never values
        $values = [];
        foreach ($installed_language_keys as $lang_key) {
            $value = $translations[$lang_key] ?? '';
            $values[$lang_key] = is_string($value) ? trim($value) : '';
        }
        $invalid_values = (new TranslationMarkupPolicy())->findInvalidValues($values);
        if ($invalid_values !== []) {
            throw new InvalidInputException(
                'The value contains markup that is not allowed for: ' . implode(', ', array_keys($invalid_values)) . '.'
            );
        }

        // such a form would be dropped from the module's content: reject instead of reporting success
        $languages_without_form = $this->repository->languagesWithoutPluralForm(
            $module,
            $identifier,
            array_keys(array_filter($values))
        );
        if ($languages_without_form !== []) {
            throw new InvalidInputException(
                'The plural message has no such form for: ' . implode(', ', $languages_without_form) . '.'
            );
        }

        // '' (no usr_id) means "no remarks" for replaceLangEntry()
        $login = is_int($usr_id) ? $this->repository->lookupLogin($usr_id) : '';
        $local_change = gmdate('Y-m-d H:i:s');

        $added_language_keys = [];
        $skipped_empty_language_keys = [];
        $overlay_write_failed_language_keys = [];

        // not transactional: a failure partway leaves the languages processed so far written
        foreach ($installed_language_keys as $lang_key) {
            $value = $translations[$lang_key] ?? '';
            $value = is_string($value) ? trim($value) : '';

            // "0" counts as blank too (legacy behaviour)
            if (!$value) {
                $skipped_empty_language_keys[] = $lang_key;
                continue;
            }

            // before anything is written, so lng_data does not get the entry either
            if ($this->repository->hasUnreadableContent($lang_key, $module)) {
                $overlay_write_failed_language_keys[] = $lang_key;
                continue;
            }
            $this->repository->replaceLangEntry($module, $identifier, $lang_key, $value, $local_change, $login);
            if (!$this->repository->updateModuleCache($lang_key, $module, $identifier, $value, $login)) {
                $overlay_write_failed_language_keys[] = $lang_key;
            }

            $added_language_keys[] = $lang_key;
        }

        return [
            'module' => $module,
            'identifier' => $identifier,
            'added_language_keys' => $added_language_keys,
            'skipped_empty_language_keys' => $skipped_empty_language_keys,
            'overlay_write_failed_language_keys' => $overlay_write_failed_language_keys,
        ];
    }

    /**
     * @param array{module: string, identifier: string, translations: array<string, string>} $grind_result
     * @return array{module: string, identifier: string, translations: array<string, string>}
     */
    protected function normalizeParameters(array $grind_result): array
    {
        return [
            'module' => $this->toNonEmptyString($grind_result['module'], 'module'),
            'identifier' => $this->toNonEmptyString($grind_result['identifier'], 'identifier'),
            'translations' => $grind_result['translations'],
        ];
    }

    private function toNonEmptyString(mixed $value, string $field): string
    {
        if (!is_string($value) || trim($value) === '') {
            // withRequired(true) lets whitespace-only strings pass
            throw new InvalidInputException("The $field parameter must be a non-empty string.");
        }

        return trim($value);
    }
}
