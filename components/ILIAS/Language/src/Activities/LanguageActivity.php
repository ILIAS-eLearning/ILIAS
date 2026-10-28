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
use ILIAS\Component\Activities\ActivityImpl;
use ILIAS\Component\Activities\ActivityType;
use ILIAS\Data\Description;
use ILIAS\Data\Result;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\ComponentTranslation\DuplicateIdentifierMessage;
use ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy;
use ILIAS\Language\LanguageFolder;
use ILIAS\UI\Component\Input\Factory as InputFactory;

abstract class LanguageActivity extends ActivityImpl
{
    use GrindsFormInput;

    /** @param RBAC|null $rbac null only for forSetup(); isAllowedToPerform() then throws. */
    public function __construct(
        private readonly ?RBAC $rbac,
        private readonly LanguageFolder $language_folder,
        protected readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getType(): ActivityType
    {
        return ActivityType::Command;
    }

    /** The database was written, but the PO/MO overlay of a module was not: callers must not report plain success. */
    protected function overlayWriteFailedOutputField(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->list(
            $text->simpleDocument(
                'Languages whose database content was written, but for which the per-installation ' .
                'PO/MO files of at least one module maintained in PO files could not be written ' .
                '(typically missing write permissions of the web server user on the client data ' .
                'directory) - the details are logged.'
            ),
            $f->string($text->simpleDocument('Language key of a language with an unwritten PO/MO overlay.'))
        );
    }

    protected function invalidMarkupCustomizingOutputField(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->list(
            $text->simpleDocument(
                'Entries of the customizing/local language files that were not applied because their ' .
                'value contains HTML that is not allowed in language texts - the languages were ' .
                'installed regardless.'
            ),
            $f->string($text->simpleDocument(
                'Language key and entry, followed by the markup that is not allowed, language-neutral ' .
                '("<language>: <module>#:#<identifier>: <markup>, ..."; without violations only ' .
                '"<language>: <module>#:#<identifier>").'
            ))
        );
    }

    protected function duplicateCustomizingOutputField(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->list(
            $text->simpleDocument(
                'Entries of the customizing/local language files that were not applied because their ' .
                'identifier already exists in another module or in another spelling - the languages ' .
                'were installed regardless.'
            ),
            $f->string($text->simpleDocument(
                'Language key and entry, followed by the entries it would duplicate ' .
                '("<language>: <module>#:#<identifier> (<module>: <identifier>, ...)").'
            ))
        );
    }

    /**
     * @param array<string, array<string, list<string>>> $skipped see
     *        \ILIAS\Language\Setup\LanguageInstallationManager::getSkippedDuplicateEntries()
     * @param list<string> $language_keys
     * @return list<string> the value of duplicateCustomizingOutputField()
     */
    protected function duplicateCustomizingEntries(array $skipped, array $language_keys): array
    {
        $entries = [];
        foreach ($language_keys as $language_key) {
            foreach ($skipped[$language_key] ?? [] as $entry => $existing) {
                $entries[] = $language_key . ': ' . DuplicateIdentifierMessage::entry((string) $entry, $existing);
            }
        }

        return $entries;
    }

    /**
     * @param array<string, array<string, list<string>>> $skipped see
     *        \ILIAS\Language\Setup\LanguageInstallationManager::getSkippedInvalidMarkupEntries()
     * @param list<string> $language_keys
     * @return list<string> the value of invalidMarkupCustomizingOutputField()
     */
    protected function invalidMarkupCustomizingEntries(array $skipped, array $language_keys): array
    {
        $policy = new TranslationMarkupPolicy();
        $entries = [];
        foreach ($language_keys as $language_key) {
            foreach ($skipped[$language_key] ?? [] as $entry => $violations) {
                $markup = $policy->markupOf(array_map('strval', $violations));
                $entries[] = $language_key . ': ' . $entry . ($markup === [] ? '' : ': ' . implode(', ', $markup));
            }
        }

        return $entries;
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        if ($this->rbac === null) {
            throw new \LogicException(
                'RBAC is not available during Setup; '
                . static::class . '::isAllowedToPerform() cannot be used here.'
            );
        }

        if (!$this->isKnownUser($usr_id)) {
            return false;
        }

        return $this->rbac->system()->checkAccessOfUser(
            $usr_id,
            'write',
            $this->language_folder->getRefId()
        );
    }

    /** Never the anonymous user, regardless of its RBAC roles. */
    private function isKnownUser(int $usr_id): bool
    {
        if ($usr_id <= 0) {
            return false;
        }

        return !defined('ANONYMOUS_USER_ID') || $usr_id !== ANONYMOUS_USER_ID;
    }

    public function maybePerformAs(InputFactory $input_factory, int $usr_id, array $raw_parameters): Result
    {
        try {
            // inside the try: getInputDescription() may throw too; value() throws grind's input error
            $parameters = $this->normalizeParameters(
                $this->grind($this->getInputDescription($input_factory->field()), $raw_parameters)->value()
            );
            if (!$this->isAllowedToPerform($usr_id, $parameters)) {
                return new Result\Error(
                    new \DomainException('You are not allowed to manage the languages of this installation.', 403)
                );
            }

            // usr_id last: the trusted value wins over a spoofed form key
            return new Result\Ok($this->perform([...$parameters, 'usr_id' => $usr_id]));
        } catch (\Throwable $e) {
            return new Result\Error(Failure::of($e));
        }
    }

    /** @return array<string, mixed> */
    abstract protected function normalizeParameters(array $grind_result): array;
}
