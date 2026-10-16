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

namespace ILIAS\Questions\Administration;

use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\User\Settings\Setting as UserSetting;

class ConfigurationRepository
{
    private const string SETTINGS_KEY_CREATE_MODE = 'default_create_mode';

    public function __construct(
        private readonly \ilSetting $common_settings,
        private readonly UserSetting $user_setting_create_mode,
        private readonly \ilSetting $questions_settings
    ) {
    }

    public function isCreateModeChangeableByUser(): bool
    {
        return $this->user_setting_create_mode->isChangeableByUser();
    }

    public function getGlobalCreateMode(): CreateModes
    {
        return CreateModes::tryFrom(
            $this->questions_settings->get(
                self::SETTINGS_KEY_CREATE_MODE,
                ''
            )
        ) ?? CreateModes::getDefaultMode();
    }

    public function isCreateModeSimple(
        DefaultEnvironment $environment
    ): bool {
        return $this->isCreateModeChangeableByUser() && $environment->isCreateModeSimple()
            || $this->getGlobalCreateMode() === CreateModes::Simple;
    }

    public function persistCreateMode(
        CreateModes $create_mode
    ): void {
        $this->questions_settings->set(
            self::SETTINGS_KEY_CREATE_MODE,
            $create_mode->value
        );
    }

    public function getInputForCreateMode(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery
    ): Input {
        return $this->user_setting_create_mode->getInput(
            $field_factory,
            $lng,
            $refinery,
            $this->common_settings
        );
    }
}
