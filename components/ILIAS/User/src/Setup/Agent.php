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

namespace ILIAS\User\Setup;

use ILIAS\User\Settings\User\CollectSettingsObjective;
use ILIAS\Setup;
use ILIAS\Setup\Agent as SetupAgent;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Refinery\Transformation;

class Agent implements SetupAgent
{
    use HasNoNamedObjective;

    public function __construct(
        private readonly array $user_settings_contributions
    ) {
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException(self::class . ' has no Config.');
    }

    public function getInstallObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new CollectSettingsObjective($this->user_settings_contributions);
    }

    public function getBuildObjective(): Objective
    {
        return new CollectSettingsObjective($this->user_settings_contributions);
    }

    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new ObjectiveCollection(
            'Updates for User',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(
                new DBUpdateSteps11()
            ),
            new CollectSettingsObjective($this->user_settings_contributions)
        );
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective(
            $storage,
            new DBUpdateSteps11()
        );
    }

    public function getMigrations(): array
    {
        return [
            new MigrateNewAccountAttachments()
        ];
    }
}
