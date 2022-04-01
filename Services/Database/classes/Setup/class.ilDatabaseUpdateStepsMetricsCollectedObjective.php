<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Metrics\Metric;
use ILIAS\Setup\Metrics\Storage;

class ilDatabaseUpdateStepsMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    const STEP_METHOD_PREFIX = "step_";

    protected string $step_class;

    public function __construct(Storage $storage, ilDatabaseUpdateSteps $steps)
    {
        parent::__construct($storage);
        $this->step_class = get_class($steps);
    }

    protected function collectFrom(Setup\Environment $environment, Storage $storage) : void
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

    protected function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDBStepExecutionDBExistsObjective(),
            new ilDBStepReaderExistsObjective()
        ];
    }

    public function getHash() : string
    {
        return hash("sha256", static::class . $this->step_class);
    }
}
