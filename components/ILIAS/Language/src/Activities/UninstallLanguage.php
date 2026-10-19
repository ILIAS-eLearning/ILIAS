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
use ILIAS\Language\Language;
use ILIAS\Language\LanguageFolder;

class UninstallLanguage extends LanguageActivity
{
    use DeclaresLanguageKeysOnlyInput;
    use ResolvesLanguageKeysToObjIds;

    private readonly \Closure $lng_objects;
    private readonly \Closure $obj_language_factory;

    /**
     * @param \Closure|null $lng_objects (): list<array{obj_id: int, title: string}>
     * @param \Closure|null $obj_language_factory (int $obj_id): \ilObjLanguage
     */
    public function __construct(
        Language $language,
        RBAC $rbac,
        LanguageFolder $language_folder,
        ?\Closure $lng_objects = null,
        ?\Closure $obj_language_factory = null,
    ) {
        parent::__construct($language, $rbac, $language_folder);
        $this->lng_objects = $lng_objects
            ?? static fn(): array => \ilObject::_getObjectsByType('lng');
        $this->obj_language_factory = $obj_language_factory
            ?? static fn(int $obj_id): \ilObjLanguage => new \ilObjLanguage($obj_id);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            <<<'MARKDOWN'
Uninstalls one or more already installed languages, flushing their base data
(and any customizing/local data) and resetting the status of the according
language to "not installed". Any user preference still pointing at an
uninstalled language is reset to the system default.

A language is left completely untouched, and reported separately, if it is
not installed, if it is the current system language, or if it is the
language currently in use by the acting session - none of these may ever be
uninstalled.
MARKDOWN
        );
    }

    protected function inputSectionTitle(): string
    {
        return 'Uninstall languages';
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('Result of the language uninstallation.'),
            [
                'uninstalled_language_keys' => $f->list(
                    $text->simpleDocument('Languages that were uninstalled.'),
                    $f->string($text->simpleDocument('Language key of an uninstalled language.'))
                ),
                'system_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Requested languages that were left untouched because they are the ' .
                        'current system language - the system language can never be uninstalled.'
                    ),
                    $f->string($text->simpleDocument('Language key of the system language.'))
                ),
                'user_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Requested languages that were left untouched because they are the ' .
                        'language currently in use by the acting session - a language currently ' .
                        'in use can never be uninstalled.'
                    ),
                    $f->string($text->simpleDocument('Language key of the language currently in use.'))
                ),
                'not_installed_language_keys' => $f->list(
                    $text->simpleDocument(
                        'Requested languages that were left untouched because they are not ' .
                        'installed (or not a known language key at all) - there is nothing to ' .
                        'uninstall for them.'
                    ),
                    $f->string($text->simpleDocument('Language key of a not-installed language.'))
                ),
            ]
        );
    }

    public function perform(mixed $parameters): array
    {
        if (!is_array($parameters)) {
            throw new InvalidInputException('Parameters must be an array.');
        }

        $language_keys = $this->toLanguageKeyList($parameters['language_keys'] ?? null);
        [$obj_id_by_language_key, $ambiguous_language_keys] = $this->resolveObjIdsByLanguageKey();

        // checked upfront, so one ambiguous key rejects the whole call before anything is uninstalled
        $requested_ambiguous_language_keys = array_values(
            array_intersect($language_keys, array_keys($ambiguous_language_keys))
        );
        if ($requested_ambiguous_language_keys !== []) {
            throw new AmbiguousLanguageTitleException(
                'Multiple language objects share the title(s) "'
                . implode('", "', $requested_ambiguous_language_keys)
                . '" - cannot unambiguously resolve which one(s) to uninstall.'
            );
        }

        $uninstalled_language_keys = [];
        $system_language_keys = [];
        $user_language_keys = [];
        $not_installed_language_keys = [];

        // not transactional: a failure partway leaves the languages processed so far uninstalled
        foreach ($language_keys as $language_key) {
            if (!array_key_exists($language_key, $obj_id_by_language_key)) {
                $not_installed_language_keys[] = $language_key;
                continue;
            }

            $language_object = ($this->obj_language_factory)($obj_id_by_language_key[$language_key]);

            if ($language_object->isSystemLanguage()) {
                $system_language_keys[] = $language_key;
            } elseif ($language_object->isUserLanguage()) {
                // compares against this request's session language, not $usr_id: another user's
                // language is not protected
                $user_language_keys[] = $language_key;
            } elseif (!$language_object->isInstalled()) {
                $not_installed_language_keys[] = $language_key;
            } elseif ($language_object->uninstall() !== '') {
                // uninstall() re-checks the guards and returns "" if one applies: its result decides
                $uninstalled_language_keys[] = $language_key;
            } else {
                $not_installed_language_keys[] = $language_key;
            }
        }

        return [
            'uninstalled_language_keys' => $uninstalled_language_keys,
            'system_language_keys' => $system_language_keys,
            'user_language_keys' => $user_language_keys,
            'not_installed_language_keys' => $not_installed_language_keys,
        ];
    }
}
