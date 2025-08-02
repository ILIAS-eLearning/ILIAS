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

namespace ILIAS\User\Settings\User;

use ILIAS\User\Context;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

class Settings
{
    public function __construct(
        private readonly Language $lng,
        private readonly \ilSetting $settings,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly Refinery $refinery,
        private readonly Repository $user_settings_repository
    ) {
    }

    public function addSectionsToForm(
        \ilPropertyFormGUI $form,
        Context $context,
        ?\ilObjUser $current_user
    ): \ilPropertyFormGUI {
        return array_reduce(
            $this->getSettingsForPageBySections(),
            fn(\ilPropertyFormGUI $c, array $v): \ilPropertyFormGUI => $this->addSectionToForm(
                $c,
                $current_user,
                $this->filterSettingsInSectionForAvailability($context, $v)
            ),
            $form
        );
    }

    public function performAdditionalChecks(
        \ilPropertyFormGUI $form
    ): bool {
        return $this->checkStartingPointValue($form);
    }

    /**
     * If it is possible to set the preference on the user, this is what will be
     * done, the user needs to be updated/stored after calling this function.
     */
    public function saveForm(
        \ilPropertyFormGUI $form,
        Context $context,
        \ilObjUser $current_user
    ): \ilObjUser {
        foreach ($this->getSettingsForPageBySections() as $section => $settings) {
            $available_settings = $this->filterSettingsInSectionForAvailability($context, $settings);
            $set_settings_to_default = false;
            if ($section !== 0
                && (($input = $form->getInput($section)) === '' || $input === '0')) {
                $set_settings_to_default = true;
            }
            foreach ($available_settings as $setting) {
                $setting->persistUserInput(
                    $current_user,
                    $set_settings_to_default ? null : $form->getInput($setting->getIdentifier()),
                    $form
                );
            }
        }

        $current_user->update();
        return $current_user;
    }

    private function getSettingsForPageBySections(): array
    {
        return $this->reorderSections(
            array_reduce(
                $this->user_settings_repository->get(),
                function (array $c, Setting $v): array {
                    if ($v->getSettingsPage() !== AvailablePages::MainSettings) {
                        return $c;
                    }

                    if (!array_key_exists($v->getSection()->value, $c)) {
                        $c[$v->getSection()->value] = [];
                    }

                    $c[$v->getSection()->value][] = $v;
                    return $c;
                },
                []
            )
        );
    }

    private function reorderSections(array $sections): array
    {
        $default_section = $sections[AvailableSections::Main->value];
        $additional_section = $sections[AvailableSections::Additional->value];
        unset($sections[AvailableSections::Main->value]);
        unset($sections[AvailableSections::Additional->value]);
        array_unshift($sections, $default_section);
        $sections[AvailableSections::Additional->value] = $additional_section;
        return $sections;
    }

    private function addSectionToForm(
        \ilPropertyFormGUI $form,
        ?\ilObjUser $current_user,
        array $section
    ): \ilPropertyFormGUI {
        if ($section === []) {
            return $form;
        }

        if ($section[0]->getSection() === AvailableSections::Main) {
            return $this->addDefaultInputsToForm($form, $current_user, $section);
        }

        return $this->addAdditionalInputsToForm($form, $current_user, $section);
    }

    private function addDefaultInputsToForm(
        \ilPropertyFormGUI $form,
        ?\ilObjUser $current_user,
        array $section
    ): \ilPropertyFormGUI {
        return array_reduce(
            $section,
            function (\ilPropertyFormGUI $c, Setting $v) use ($current_user): \ilPropertyFormGUI {
                $input = $v->getInput($this->lng, $current_user);
                $c->addItem($input);
                return $c;
            },
            $form
        );
    }

    private function addAdditionalInputsToForm(
        \ilPropertyFormGUI $form,
        ?\ilObjUser $current_user,
        array $section
    ): \ilPropertyFormGUI {
        $values = array_reduce(
            $section,
            function (array $c, Setting $v) use ($current_user): array {
                $input = $v->getInput($this->lng, $current_user);
                $input->setPostVar($v->getIdentifier());
                $c['checkbox']->addSubItem($input);
                $c['defaults'] .= "{$v->getLabel($this->lng)}: "
                    . "{$v->getDefaultValueForDisplay($this->lng, $this->refinery, $this->settings)}; ";
                if ($v->hasUserPersonalizedSetting($this->settings, $current_user)) {
                    $c['has_personalization'] = true;
                }
                return $c;
            },
            [
                'checkbox' => new \ilCheckboxInputGUI(
                    $this->lng->txt("personalise_{$section[0]->getSection()->value}"),
                    $section[0]->getSection()->value
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

    private function filterSettingsInSectionForAvailability(
        Context $context,
        array $settings
    ): array {
        return array_values(
            array_filter(
                $settings,
                static fn(Setting $v): bool => $context->isSettingAvailableInType($v)
            )
        );
    }

    private function checkStartingPointValue(\ilPropertyFormGUI $form): bool
    {
        return $form->getItemByPostVar('starting_point') === null
            || $this->user_settings_repository->getByIdentifier('starting_point')->validateUserChoice($this->tpl, $this->lng, $form);
    }
}
