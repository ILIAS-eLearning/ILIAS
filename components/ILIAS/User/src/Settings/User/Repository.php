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

use ILIAS\User\PropertyAttributes;

class Repository
{
    public function __construct(
        private readonly \ilSetting $settings,
        private readonly array $available_user_settings
    ) {
    }

    public function get(): array
    {
        return array_reduce(
            $this->available_user_settings,
            function (array $c, string $v): array {
                $definition = new $v();
                if (!$definition->isAvailable()) {
                    return $c;
                }
                $c[] = $this->buildSettingFromDefinition($definition);
                return $c;
            },
            []
        );
    }

    public function getByIdentifier(string $identifier): Setting
    {
        foreach ($this->available_user_settings as $setting) {
            $definition = new $setting();
            if ($definition->getIdentifier() === $identifier) {
                return $this->buildSettingFromDefinition($definition);
            }
        }
    }

    public function storeConfiguration(Setting $setting): void
    {
        PropertyAttributes::HiddenFromUser->store(
            $this->settings,
            $setting,
            !$setting->isVisibleInPersonalData()
        );
        PropertyAttributes::VisibleInLocalUserAdministration->store(
            $this->settings,
            $setting,
            $setting->isVisibleInLocalUserAdministration()
        );
        PropertyAttributes::UnchangeableByUser->store(
            $this->settings,
            $setting,
            !$setting->isChangeableByUser()
        );
        PropertyAttributes::ChangeableInLocalUserAdministration->store(
            $this->settings,
            $setting,
            $setting->isChangeableInLocalUserAdministration()
        );
        PropertyAttributes::Export->store(
            $this->settings,
            $setting,
            $setting->export()
        );
    }

    private function buildSettingFromDefinition(
        SettingDefinition $definition
    ): Setting {
        $identifier = $definition->getIdentifier();
        return new Setting(
            $definition,
            !PropertyAttributes::HiddenFromUser->retrieve($this->settings, $definition),
            PropertyAttributes::VisibleInLocalUserAdministration->retrieve($this->settings, $definition),
            !PropertyAttributes::UnchangeableByUser->retrieve($this->settings, $definition),
            PropertyAttributes::ChangeableInLocalUserAdministration->retrieve($this->settings, $definition),
            PropertyAttributes::Export->retrieve($this->settings, $definition)
        );
    }
}
