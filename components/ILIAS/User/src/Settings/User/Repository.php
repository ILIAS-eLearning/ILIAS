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

class Repository
{
    private const PREFIX_HIDE_FIELD = 'usr_settings_hide_';
    private const PREFIX_VISIBLE_LOCAL_USER_ADMINISTRATION = 'usr_settings_visib_lua_';
    private const PREFIX_CHANGEABLE_PROFILE = 'usr_settings_disable_';
    private const PREFIX_CHANGEABLE_LOCAL_USER_ADMINISTRATION = 'usr_settings_changeable_lua';
    private const PREFIX_EXPORTABLE = 'usr_settings_export_';

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
                $configuration = new $v();
                if (!$configuration->isAvailable()) {
                    return $c;
                }
                $c[] = $this->buildSettingFromConfiguration($configuration);
                return $c;
            },
            []
        );
    }

    public function getByIdentifier(string $identifier): Setting
    {
        foreach ($this->available_user_settings as $setting) {
            $configuration = new $setting();
            if ($configuration->getIdentifier() === $identifier) {
                return $this->buildSettingFromConfiguration($configuration);
            }
        }
    }

    public function storeConfiguration(Setting $setting): void
    {
        $identifier = $setting->getIdentifier();
        $this->settings->set(
            self::PREFIX_HIDE_FIELD . $identifier,
            $setting->isVisibleInPersonalData() ? '0' : '1'
        );
        $this->settings->set(
            self::PREFIX_VISIBLE_LOCAL_USER_ADMINISTRATION . $identifier,
            $setting->isVisibleInLocalUserAdministration() ? '1' : '0'
        );
        $this->settings->set(
            self::PREFIX_CHANGEABLE_PROFILE . $identifier,
            $setting->isChangeableInProfile() ? '0' : '1'
        );
        $this->settings->set(
            self::PREFIX_CHANGEABLE_LOCAL_USER_ADMINISTRATION . $identifier,
            $setting->isChangeableInLocalUserAdministration() ? '1' : '0'
        );
        $this->settings->set(
            self::PREFIX_EXPORTABLE . $identifier,
            $setting->export() ? '1' : '0'
        );
    }

    private function buildSettingFromConfiguration(
        SettingConfiguration $configuration
    ): Setting {
        $identifier = $configuration->getIdentifier();
        return new Setting(
            $configuration,
            $this->settings->get(self::PREFIX_HIDE_FIELD . $identifier) !== '1',
            $this->settings->get(self::PREFIX_VISIBLE_LOCAL_USER_ADMINISTRATION . $identifier) === '1',
            $this->settings->get(self::PREFIX_CHANGEABLE_PROFILE . $identifier) !== '1',
            $this->settings->get(self::PREFIX_CHANGEABLE_LOCAL_USER_ADMINISTRATION . $identifier) === '1',
            $this->settings->get(self::PREFIX_EXPORTABLE . $identifier) === '1'
        );
    }
}
