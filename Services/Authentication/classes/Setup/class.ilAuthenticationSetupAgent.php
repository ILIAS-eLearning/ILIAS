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

use ILIAS\Setup;
use ILIAS\Refinery;

class ilAuthenticationSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected const DEFAULT_SESSION_EXPIRE = 30;

    public function __construct(
        protected Refinery\Factory $refinery
    ) {
    }

    public function hasConfig(): bool
    {
        return true;
    }

    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data): \ilAuthenticationSetupConfig {
            return new ilAuthenticationSetupConfig($data["session_max_idle"] ?? self::DEFAULT_SESSION_EXPIRE);
        });
    }

    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        if ($config !== null) {
            return new ilSessionMaxIdleIsSetObjective($config);
        }

        return new ilSessionMaxIdleIsSetObjective(new ilAuthenticationSetupConfig(self::DEFAULT_SESSION_EXPIRE));
    }

    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        if ($config !== null) {
            return new Setup\ObjectiveCollection('Setup Authentication and Sessions', true, ...[
                new ilDatabaseUpdateStepsExecutedObjective(
                    new ilAuthenticationDatabaseUpdateSteps8()
                ),
                new ilSessionMaxIdleIsSetObjective($config)
            ]);
        }

        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilAuthenticationDatabaseUpdateSteps8()
        );
    }

    public function getBuildArtifactObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilAuthenticationDatabaseUpdateSteps8());
    }

    public function getMigrations(): array
    {
        return [];
    }
}
