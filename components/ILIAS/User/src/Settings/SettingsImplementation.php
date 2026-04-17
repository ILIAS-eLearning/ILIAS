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

namespace ILIAS\User\Settings;

use ILIAS\User\Context;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;

class SettingsImplementation implements Settings
{
    public function __construct(
        private readonly Language $lng,
        private readonly \ilSetting $settings,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly ConfigurationRepository $user_settings_configuration_repository,
        private readonly DataRepository $user_settings_data_repository
    ) {
    }

    /**
     * @param array<AvailablePages> $pages
     * @return array<string, \ILIAS\UI\Component\Input\Input>
     */
    public function buildFormInputs(
        array $pages,
        Context $context,
        ?\ilObjUser $user
    ): array {
        return array_reduce(
            $this->getSettingsForPagesBySections($pages),
            function (array $c, array $v) use ($pages, $context, $user): array {
                $settings = $this->filterSettingsInSectionForAvailability($context, $v);
                if ($settings === []) {
                    return $c;
                }

                $page = $settings[0]->getSettingsPage();
                $section = $settings[0]->getSection();
                $section_key = $this->buildSectionKey($settings[0]);

                ['inputs' => $inputs, 'byline' => $byline, 'personalized' => $personalized] = $this->buildInputsForSection(
                    $settings,
                    $user
                );

                if (!in_array(AvailablePages::MainSettings, $pages)
                    || $page === AvailablePages::MainSettings && $section === AvailableSections::Main) {
                    return $c + $inputs;
                }

                $c[$section_key] = $this->ui_factory->input()->field()->optionalGroup(
                    $inputs,
                    $this->lng->txt("personalise_{$section_key}"),
                    $byline
                );

                if (!$personalized) {
                    $c[$section_key] = $c[$section_key]->withValue(null);
                }

                return $c;
            },
            []
        );
    }

    /**
     * @param array<AvailablePages> $pages
     */
    public function addSectionsToLegacyForm(
        \ilPropertyFormGUI $form,
        array $pages,
        Context $context,
        ?\ilObjUser $user
    ): \ilPropertyFormGUI {
        return array_reduce(
            $this->getSettingsForPagesBySections($pages),
            fn(\ilPropertyFormGUI $c, array $v): \ilPropertyFormGUI => $this->addSectionToLegacyForm(
                $c,
                $user,
                $this->filterSettingsInSectionForAvailability($context, $v)
            ),
            $form
        );
    }

    public function performAdditionalChecks(
        \ilGlobalTemplateInterface $tpl,
        \ilPropertyFormGUI $form
    ): bool {
        return $this->checkStartingPointValue(
            $tpl,
            $form
        );
    }

    /**
     * If it is possible to set the preference on the user, this is what will be
     * done, the user needs to be updated/stored after calling this function.
     *
     * @param array<AvailablePages> $pages
     */
    public function saveForm(
        \ilPropertyFormGUI|array $form,
        array $pages,
        Context $context,
        \ilObjUser $user
    ): \ilObjUser {
        foreach ($this->getSettingsForPagesBySections($pages) as $section => $settings) {
            $available_settings = $this->filterSettingsInSectionForAvailability($context, $settings);
            $set_settings_to_default = false;
            if ($section !== 0
                && (
                    is_array($form) && $form[$section] === null
                    || $form instanceof \ilPropertyFormGUI
                        && (($input = $form->getInput($section)) === '' || $input === '0')
                )
            ) {
                $set_settings_to_default = true;
            }
            foreach ($available_settings as $setting) {
                $setting->persistUserInput(
                    $user,
                    $context,
                    $set_settings_to_default ? null : $this->retrieveValueFromInputs($form, $setting),
                    $form instanceof \ilPropertyFormGUI ? $form : null
                );
            }
        }

        $user->update();
        return $user;
    }

    public function getSettingByDefinitionClass(
        string $definition_class
    ): Setting {
        $setting = $this->user_settings_configuration_repository->getByDefinitionClass($definition_class);
        if ($setting === null) {
            throw new \UnexpectedValueException('No class by that name');
        }
        return $setting;
    }

    public function getValueFromLegacyFormByDefinitionClass(
        string $definition_class,
        \ilPropertyFormGUI $form
    ): mixed {
        return $form->getInput(
            $this->getSettingByDefinitionClass($definition_class)->getIdentifier()
        );
    }

    public function settingAvailableToUser(
        string $definition_class
    ): bool {
        return Context::User->isSettingAvailable(
            $this->getSettingByDefinitionClass($definition_class)
        );
    }

    public function getSettingValueFor(int $user_id, string $key): ?string
    {
        return $this->user_settings_data_repository->getFor($user_id)[$key] ?? null;
    }

    /**
     * @return list<Setting>
     */
    public function getExportableSettings(): array
    {
        $context = Context::Export;
        return array_filter(
            $this->user_settings_configuration_repository->get(),
            static fn(Setting $v): bool => $context->isSettingAvailable($v)
        );
    }

    /**
     * @param array<AvailablePages> $pages
     */
    private function getSettingsForPagesBySections(
        array $pages
    ): array {
        return $this->reorderSections(
            array_reduce(
                $this->user_settings_configuration_repository->get(),
                function (array $c, Setting $v) use ($pages): array {
                    if (!in_array($v->getSettingsPage(), $pages)) {
                        return $c;
                    }

                    $section_key = $this->buildSectionKey($v);
                    if (!array_key_exists($section_key, $c)) {
                        $c[$section_key] = [];
                    }

                    $c[$section_key][] = $v;
                    return $c;
                },
                []
            )
        );
    }

    private function reorderSections(
        array $sections
    ): array {
        if (isset($sections[AvailableSections::Main->value])) {
            $default_section = $sections[AvailableSections::Main->value];
            unset($sections[AvailableSections::Main->value]);
            array_unshift($sections, $default_section);
        }

        if (isset($sections[AvailableSections::Additional->value])) {
            $additional_section = $sections[AvailableSections::Additional->value];
            unset($sections[AvailableSections::Additional->value]);
            $sections[AvailableSections::Additional->value] = $additional_section;
        }
        return $sections;
    }

    private function buildInputsForSection(
        array $settings,
        ?\ilObjUser $user
    ): array {
        return array_reduce(
            $settings,
            function (array $c, Setting $v) use ($user): array {
                $c['inputs'][$v->getIdentifier()] = $v->getInput(
                    $this->ui_factory->input()->field(),
                    $this->lng,
                    $this->refinery,
                    $this->settings,
                    $user
                );
                $c['byline'] .= "{$v->getLabel($this->lng)}: "
                    . "{$v->getDefaultValueForDisplay($this->lng, $this->settings)}; ";
                if ($v->hasUserPersonalizedSetting($this->settings, $user)) {
                    $c['personalized'] = true;
                }
                return $c;
            },
            [
                'inputs' => [],
                'byline' => "{$this->lng->txt('default')}<br>",
                'personalized' => false
            ]
        );
    }

    private function addSectionToLegacyForm(
        \ilPropertyFormGUI $form,
        ?\ilObjUser $user,
        array $section
    ): \ilPropertyFormGUI {
        if ($section === []) {
            return $form;
        }

        if ($section[0]->getSettingsPage() === AvailablePages::MainSettings
            && $section[0]->getSection() === AvailableSections::Main) {
            return $this->addDefaultInputsToLegacyForm($form, $user, $section);
        }

        return $this->addAdditionalInputsToLegacyForm($form, $user, $section);
    }

    private function addDefaultInputsToLegacyForm(
        \ilPropertyFormGUI $form,
        ?\ilObjUser $user,
        array $section
    ): \ilPropertyFormGUI {
        return array_reduce(
            $section,
            function (\ilPropertyFormGUI $c, Setting $v) use ($user): \ilPropertyFormGUI {
                $input = $v->getLegacyInput($this->lng, $this->settings, $user);
                $c->addItem($input);
                return $c;
            },
            $form
        );
    }

    private function addAdditionalInputsToLegacyForm(
        \ilPropertyFormGUI $form,
        ?\ilObjUser $user,
        array $section
    ): \ilPropertyFormGUI {
        $section_key = $this->buildSectionKey($section[0]);
        $values = array_reduce(
            $section,
            function (array $c, Setting $v) use ($user): array {
                $input = $v->getLegacyInput($this->lng, $this->settings, $user);
                $input->setPostVar($v->getIdentifier());
                $c['checkbox']->addSubItem($input);
                $c['defaults'] .= "{$v->getLabel($this->lng)}: "
                    . "{$v->getDefaultValueForDisplay($this->lng, $this->settings)}; ";
                if ($v->hasUserPersonalizedSetting($this->settings, $user)) {
                    $c['has_personalization'] = true;
                }
                return $c;
            },
            [
                'checkbox' => new \ilCheckboxInputGUI(
                    $this->lng->txt("personalise_{$section_key}"),
                    $section_key
                ),
                'defaults' => "{$this->lng->txt('default')}<br>",
                'has_personalization' => false
            ]
        );
        $values['checkbox']->setInfo(trim($values['defaults']));
        $values['checkbox']->setChecked($values['has_personalization']);

        $form->addItem($values['checkbox']);

        return $form;
    }

    private function buildSectionKey(Setting $setting): string
    {
        return $setting->getSettingsPage() === AvailablePages::MainSettings
            ? $setting->getSection()->value
            : $setting->getSettingsPage()->value;
    }

    private function filterSettingsInSectionForAvailability(
        Context $context,
        array $settings
    ): array {
        return array_values(
            array_filter(
                $settings,
                static fn(Setting $v): bool => $context->isSettingAvailable($v)
            )
        );
    }

    private function retrieveValueFromInputs(
        \ilPropertyFormGUI|array $form,
        Setting $setting
    ): mixed {
        if ($form instanceof \ilPropertyFormGUI) {
            return $form->getInput($setting->getIdentifier());
        }

        $section_key = $this->buildSectionKey($setting);

        if ($section_key === AvailableSections::Main->value) {
            return $form[$setting->getIdentifier()];
        }
        return $form[$section_key][$setting->getIdentifier()];
    }

    private function checkStartingPointValue(
        \ilGlobalTemplateInterface $tpl,
        \ilPropertyFormGUI $form
    ): bool {
        return $form->getInput('additional') === ''
            || $this->user_settings_configuration_repository
                ->getByIdentifier('starting_point')
                ->validateUserChoice(
                    $tpl,
                    $this->lng,
                    $form
                );
    }
}
