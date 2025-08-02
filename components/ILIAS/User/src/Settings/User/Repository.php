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
        $this->storeConfigurationValue(
            PropertyAttributes::ChangeableByUser->getSettingsAccessPrefix(),
            $setting->getIdentifier(),
            !$setting->isChangeableByUser()
        );
        $this->storeConfigurationValue(
            PropertyAttributes::ChangeableInLocalUserAdministration->getSettingsAccessPrefix(),
            $setting->getIdentifier(),
            $setting->isChangeableInLocalUserAdministration()
        );
        $this->storeConfigurationValue(
            PropertyAttributes::Export->getSettingsAccessPrefix(),
            $setting->getIdentifier(),
            $setting->export()
        );
    }

    private function buildSettingFromDefinition(
        SettingDefinition $definition
    ): Setting {
        $this->updateLegacyValues($definition);
        return new Setting(
            $definition,
            $this->retrieveConfigurationValue(
                PropertyAttributes::ChangeableByUser->getSettingsAccessPrefix(),
                $definition->getIdentifier()
            ),
            $this->retrieveConfigurationValue(
                PropertyAttributes::ChangeableInLocalUserAdministration->getSettingsAccessPrefix(),
                $definition->getIdentifier()
            ),
            $this->retrieveConfigurationValue(
                PropertyAttributes::Export->getSettingsAccessPrefix(),
                $definition->getIdentifier()
            ),
        );
    }

    /**
     * @todo: Remove with ILIAS 12
     */
    private function updateLegacyValues(SettingDefinition $definition): void
    {
        if ($this->settings->get(
            PropertyAttributes::ChangeableByUser->getSettingsAccessPrefix() . "_{$definition->getIdentifier()}"
        ) !== null) {
            return;
        }

        $legacy_disabled_value = $this->settings->get("usr_settings_disable_{$definition->getIdentifier()}", '0');
        $this->settings->delete("usr_settings_disable_{$definition->getIdentifier()}");
        $this->storeConfigurationValue(
            PropertyAttributes::ChangeableByUser->getSettingsAccessPrefix(),
            $definition->getIdentifier(),
            $legacy_disabled_value !== '1'
        );
    }

    private function retrieveConfigurationValue(
        string $prefix,
        string $identifier
    ): bool {
        return $this->settings->get("{$prefix}_{$identifier}", '0') === '1';
    }

    private function storeConfigurationValue(
        string $prefix,
        string $identifier,
        bool $value
    ): void {
        $this->settings->set("{$prefix}_{$identifier}", $value ? '1' : '0');
    }
}
