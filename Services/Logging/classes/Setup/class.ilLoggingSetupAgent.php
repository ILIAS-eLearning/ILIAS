<?php

declare(strict_types=1);

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


use ILIAS\Setup;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;
use ILIAS\UI;

class ilLoggingSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Factory $refinery;

    public function __construct(Factory $refinery)
    {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilLoggingSetupConfig(
                $data["enable"] ?? false,
                $data["path_to_logfile"] ?? null,
                $data["errorlog_dir"] ?? null
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Config $config = null): Objective
    {
        return new ilLoggingConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Config $config = null): Objective
    {
        $objective = new Setup\Objective\NullObjective();
        if ($config !== null) {
            $objective = new ilLoggingConfigStoredObjective($config);
        }
        return new ILIAS\Setup\ObjectiveCollection(
            'Update of Services/Logging',
            false,
            $objective,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilLoggingUpdateSteps8()
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective(): Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Storage $storage): Objective
    {
        return new ilLoggingMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
