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
use ILIAS\Setup\Metrics\Metric;
use ILIAS\Setup\Metrics\Storage;

class ilDatabaseUpdateStepsMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public const STEP_METHOD_PREFIX = "step_";

    protected string $step_class;

    public function __construct(Storage $storage, ilDatabaseUpdateSteps $steps)
    {
        parent::__construct($storage);
        $this->step_class = get_class($steps);
    }

    protected function collectFrom(Setup\Environment $environment, Storage $storage): void
    {
        $execution_log = $environment->getResource(ilDatabaseUpdateStepExecutionLog::class);
        $step_reader = $environment->getResource(ilDBStepReader::class);

        $version = new Metric(
            Metric::STABILITY_STABLE,
            Metric::TYPE_TEXT,
            (string) ($execution_log->getLastFinishedStep($this->step_class))
        );

        $available_version = new Metric(
            Metric::STABILITY_STABLE,
            Metric::TYPE_TEXT,
            (string) $step_reader->getLatestStepNumber($this->step_class, self::STEP_METHOD_PREFIX)
        );

        $update_required = new Metric(
            Metric::STABILITY_STABLE,
            Metric::TYPE_BOOL,
            $execution_log->getLastFinishedStep($this->step_class) !== $step_reader->getLatestStepNumber(
                $this->step_class,
                self::STEP_METHOD_PREFIX
            )
        );

        $collection = new Metric(
            Metric::STABILITY_STABLE,
            Metric::TYPE_COLLECTION,
            [
                "version" => $version,
                "available_version" => $available_version,
                "update_required" => $update_required
            ]
        );

        $storage->store($this->step_class, $collection);
    }

    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDBStepExecutionDBExistsObjective(),
            new ilDBStepReaderExistsObjective()
        ];
    }

    public function getHash(): string
    {
        return hash("sha256", static::class . $this->step_class);
    }
}
