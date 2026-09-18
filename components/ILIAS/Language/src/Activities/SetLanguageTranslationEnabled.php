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

use ILIAS\Administration\Setting;
use ILIAS\Data\Description;
use ILIAS\Data\Text;
use ILIAS\Language\Language;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class SetLanguageTranslationEnabled extends LanguageActivity
{
    private readonly \Closure $settings;

    public function __construct(
        RefineryFactory $refinery,
        Language $language,
        \ilRbacSystem|\Closure $rbac_system,
        Setting|\Closure $settings,
        private readonly InstalledLanguageRepository $installed_language_repository,
        int|\Closure $language_folder_ref_id = 0,
    ) {
        parent::__construct($refinery, $language, $rbac_system, $language_folder_ref_id);
        $this->settings = $settings instanceof \Closure
            ? $settings
            : static fn(): Setting => $settings;
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->markdown(
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

        return $f->group([
            'language_key' => $language_key,
            'enabled' => $enabled,
        ]);
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $f->object(
            $this->markdown('Result of changing the page translation setting.'),
            [
                'language_key' => $f->string(
                    $this->markdown('Language key the page translation setting was changed for.')
                ),
                'enabled' => $f->bool(
                    $this->markdown('The page translation setting for this language after this change.')
                ),
                'changed' => $f->bool(
                    $this->markdown(
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

        if (!in_array($language_key, $this->installed_language_repository->getInstalledLanguages(), true)) {
            throw new InvalidInputException(
                'Unknown language key "' . $language_key . '" - not an installed language.'
            );
        }

        $translate_key = 'lang_translate_' . $language_key;

        $currently_enabled = (bool) ($this->settings)()->get($translate_key, '0');
        // Strict comparison of two proper booleans, not a loose `!=` on raw strings - see
        // SetLanguageTranslationEnabledTest for the edge case this pins down.
        $changed = $currently_enabled !== $enabled;

        if ($changed) {
            ($this->settings)()->set($translate_key, $enabled ? '1' : '0');
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
