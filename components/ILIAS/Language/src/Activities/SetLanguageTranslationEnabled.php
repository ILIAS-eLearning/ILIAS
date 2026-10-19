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
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class SetLanguageTranslationEnabled extends LanguageActivity
{
    public function __construct(
        Language $language,
        RBAC $rbac,
        LanguageFolder $language_folder,
        private readonly RepositoryProvider $repository,
    ) {
        parent::__construct($language, $rbac, $language_folder);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            <<<'MARKDOWN'
Enables or disables the "page translation" feature for one specific
language. This is a per-language on/off flag, not a system-wide setting -
use SetLanguageDetectionEnabled for the system-wide automatic language
detection flag instead.
MARKDOWN
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        $language_key = $f->text(
            'Language key',
            'Language key the page translation setting applies to, e.g. de, fr, it.'
        )->withRequired(true)->withDedicatedName('language_key');

        $enabled = $f->checkbox(
            'Enabled',
            'Whether page translation should be enabled for this language.'
        )->withDedicatedName('enabled');

        return $f->section([
            'language_key' => $language_key,
            'enabled' => $enabled,
        ], 'Enable or disable page translation');
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('Result of changing the page translation setting.'),
            [
                'language_key' => $f->string(
                    $text->simpleDocument('Language key the page translation setting was changed for.')
                ),
                'enabled' => $f->bool(
                    $text->simpleDocument('The page translation setting for this language after this change.')
                ),
                'changed' => $f->bool(
                    $text->simpleDocument(
                        'Whether the setting actually differed from its previously stored value - ' .
                        'false if the requested value already matched the stored one, in which case ' .
                        'nothing was written.'
                    )
                ),
            ]
        );
    }

    public function perform(mixed $parameters): array
    {
        if (!is_array($parameters)
            || !array_key_exists('language_key', $parameters)
            || !is_string($parameters['language_key'])
            || trim($parameters['language_key']) === ''
            || !array_key_exists('enabled', $parameters)
            || !is_bool($parameters['enabled'])
        ) {
            throw new InvalidInputException(
                'The language_key (non-empty string) and enabled (bool) parameters are required.'
            );
        }

        $language_key = trim($parameters['language_key']);
        $enabled = $parameters['enabled'];

        if (!in_array($language_key, $this->repository->getInstalledLanguages(), true)) {
            throw new InvalidInputException(
                'Unknown language key "' . $language_key . '" - not an installed language.'
            );
        }

        $currently_enabled = $this->repository->isPageTranslationEnabled($language_key);
        // strict comparison of booleans, a loose `!=` on raw strings misjudges some values (see the test)
        $changed = $currently_enabled !== $enabled;

        if ($changed) {
            $this->repository->setPageTranslationEnabled($language_key, $enabled);
        }

        return [
            'language_key' => $language_key,
            'enabled' => $enabled,
            'changed' => $changed,
        ];
    }

    /**
     * @param array{language_key: string, enabled: bool} $grind_result
     * @return array{language_key: string, enabled: bool}
     */
    protected function normalizeParameters(array $grind_result): array
    {
        return [
            'language_key' => trim($grind_result['language_key']),
            'enabled' => $grind_result['enabled'],
        ];
    }
}
