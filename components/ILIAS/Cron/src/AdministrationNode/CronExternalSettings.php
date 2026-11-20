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

namespace ILIAS\Cron\AdministrationNode;

use ILIAS\Administration\Settings\ExternalSettings;
use ILIAS\Administration\Settings\SettingsForm;

/**
 * Settings of the cron jobs, shown on every settings form after the settings of the components.
 */
class CronExternalSettings implements ExternalSettings
{
    public function getObjectType(): string
    {
        return 'cron';
    }

    public function getForms(): array
    {
        return SettingsForm::cases();
    }

    public function getPosition(SettingsForm $form): int
    {
        return 1000;
    }

    public function getGUI(): \ilObjectGUI
    {
        return \ilObjCronGUI::create();
    }
}
