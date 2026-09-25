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

use ILIAS\Data\Description;
use ILIAS\Data\Text;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;

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
        RefineryFactory $refinery,
        Language $language,
        \ilRbacSystem|\Closure $rbac_system,
        int|\Closure $language_folder_ref_id = 0,
        ?\Closure $lng_objects = null,
        ?\Closure $obj_language_factory = null,
    ) {
        parent::__construct($refinery, $language, $rbac_system, $language_folder_ref_id);
        $this->lng_objects = $lng_objects
            ?? static fn(): array => \ilObject::_getObjectsByType('lng');
        $this->obj_language_factory = $obj_language_factory
            ?? static fn(int $obj_id): \ilObjLanguage => new \ilObjLanguage($obj_id);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->markdown(
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

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $f->object(
            $this->markdown('Result of the language uninstallation.'),
            [
                'uninstalled_language_keys' => $f->list(
                    $this->markdown('Languages that were uninstalled.'),
                    $f->string($this->markdown('Language key of an uninstalled language.'))
                ),
                'system_language_keys' => $f->list(
                    $this->markdown(
                        'Requested languages that were left untouched because they are the ' .
                        'current system language - the system language can never be uninstalled.'
                    ),
                    $f->string($this->markdown('Language key of the system language.'))
                ),
                'user_language_keys' => $f->list(
                    $this->markdown(
                        'Requested languages that were left untouched because they are the ' .
                        'language currently in use by the acting session - a language currently ' .
                        'in use can never be uninstalled.'
                    ),
                    $f->string($this->markdown('Language key of the language currently in use.'))
                ),
                'not_installed_language_keys' => $f->list(
                    $this->markdown(
                        'Requested languages that were left untouched because they are not ' .
                        'installed (or not a known language key at all) - there is nothing to ' .
                        'uninstall for them.'
                    ),
                    $f->string($this->markdown('Language key of a not-installed language.'))
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

        // Checked upfront, for all requested keys at once, so a request naming both an
        // unambiguous and an ambiguous key never uninstalls the unambiguous one before
        // rejecting the whole call.
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

        // Not transactional across multiple keys: a failure partway through (e.g. a database
        // error) leaves languages processed so far uninstalled.
        foreach ($language_keys as $language_key) {
            if (!array_key_exists($language_key, $obj_id_by_language_key)) {
                $not_installed_language_keys[] = $language_key;
                continue;
            }

            $language_object = ($this->obj_language_factory)($obj_id_by_language_key[$language_key]);

            if ($language_object->isSystemLanguage()) {
                $system_language_keys[] = $language_key;
            } elseif ($language_object->isUserLanguage()) {
                // Compares against the ambient Language service's lang_user (this request's
                // session), not against $usr_id - a caller acting for another user's $usr_id
                // (e.g. a webservice or background job) does not protect that user's language.
                $user_language_keys[] = $language_key;
            } elseif (!$language_object->isInstalled()) {
                $not_installed_language_keys[] = $language_key;
            } elseif ($language_object->uninstall() !== '') {
                // uninstall() re-checks these same guards internally and returns "" if any
                // still applies - the outcome is decided by this return value, not the checks
                // above alone.
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
