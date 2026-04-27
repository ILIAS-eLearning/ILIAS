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

namespace ILIAS\Style\System\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class Style implements SettingDefinition
{
    private readonly \ilStyleDefinition $style_definition;

    public function __construct()
    {
        global $DIC;
        $this->style_definition = $DIC['styleDefinition'];
    }

    public function getIdentifier(): string
    {
        return 'style';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Additional;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('style');

        return $field_factory->select(
            $lng->txt('usr_skin_style'),
            $this->buildStyleOptions(),
        )->withRequired(true)
        ->withValue(
            $this->buildStyleIdFromUserOrDefault($user)
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('style');

        $input = new \ilSelectInputGUI($lng->txt('usr_skin_style'));
        $input->setOptions($this->buildStyleOptions());
        $input->setValue($this->buildStyleIdFromUserOrDefault($user));
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        $default_skin = $this->style_definition->getSkin();
        return "{$default_skin->getName()} / {$default_skin->getDefaultStyle()->getName()}";
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        $system_style_config = $this->style_definition->getSystemStylesConf();
        ['style' => $user_style, 'skin' => $user_skin] = $this->retrieveValueFromUser($user);
        return $user_style !== $system_style_config->getDefaultStyleId()
            || $user_skin !== $system_style_config->getDefaultSkinId();
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        if ($input === null) {
            $system_style_config = $this->style_definition->getSystemStylesConf();
            $user->setPref('style', $system_style_config->getDefaultStyleId());
            $user->setPref('skin', $system_style_config->getDefaultSkinId());
            return $user;
        }

        $sknst = explode(':', $input);
        $user->setPref('skin', $sknst[0]);
        $user->setPref('style', $sknst[1]);
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): array
    {
        return [
            'style' => $user->getPref('style'),
            'skin' => $user->getSkin()
        ];
    }

    private function buildStyleOptions(): array
    {
        $options = [];
        foreach ($this->style_definition::getAllSkins() as $skin) {
            foreach ($skin->getStyles() as $style) {
                if (
                    !\ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId()) ||
                    $style->isSubstyle()
                ) {
                    continue;
                }

                $options[$skin->getId() . ':' . $style->getId()] = $skin->getName() . ' / ' . $style->getName();
            }
        }
        return $options;
    }

    private function buildStyleIdFromUserOrDefault(
        ?\ilObjUser $user
    ): string {
        if ($user !== null) {
            ['style' => $style, 'skin' => $skin] = $this->retrieveValueFromUser($user);
        } else {
            $style = $this->style_definition->getSystemStylesConf()->getDefaultStyleId();
            $skin = $this->style_definition->getSystemStylesConf()->getDefaultSkinId();
        }
        return "{$skin}:{$style}";
    }
}
