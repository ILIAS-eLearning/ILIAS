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
use ILIAS\LearningSequence\Setup\InitLOMForLearningSequenceMigration;
use ILIAS\LearningSequence\Setup\ilLearningSequenceConditionsSyncedObjective;

class ilLearningSequenceSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;

    public function __construct(Refinery\Factory $refinery)
    {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new \ilFileSystemComponentDataDirectoryCreatedObjective(
            \ilLearningSequenceFilesystem::PATH_PRE,
            \ilFileSystemComponentDataDirectoryCreatedObjective::WEBDIR
        );
    }

    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Database is updated for Components/LearningSequence',
            true,
            new \ilFileSystemComponentDataDirectoryCreatedObjective(
                \ilLearningSequenceFilesystem::PATH_PRE,
                \ilFileSystemComponentDataDirectoryCreatedObjective::WEBDIR
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ilLearningSequenceRectifyPostConditionsTableDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ilLearningSequenceRegisterNotificationType()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \LSODropActivationDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \LSOAddModeDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ilLearningSequenceStreamlinePermissionsDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ilLearningSequenceItemBoundariesDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ilLearningSequenceItemPathDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ilLearningSequenceItemVisitsDBUpdateSteps()
            ),
            new ilLearningSequenceConditionsSyncedObjective()
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Component LearningSequence',
            false,
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLearningSequenceRectifyPostConditionsTableDBUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLearningSequenceRegisterNotificationType()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \LSOAddModeDBUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLearningSequenceStreamlinePermissionsDBUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLearningSequenceItemBoundariesDBUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLearningSequenceItemPathDBUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLearningSequenceItemVisitsDBUpdateSteps()),
            new ilLearningSequenceConditionsSyncedObjective()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [
            new InitLOMForLearningSequenceMigration(),
            new LSOMigrateCurriculumToLearningMap(),
        ];
    }
}
