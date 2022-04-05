<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

/**
 * This class attempt to achieve a set of database update steps. Look into the
 * interface ilDatabaseUpdateSteps for further instructions.
 */
class ilDatabaseUpdateStepsExecutedObjective implements Objective
{
    const STEP_METHOD_PREFIX = "step_";

    protected ilDatabaseUpdateSteps $steps;
    protected string $steps_class;

    public function __construct(ilDatabaseUpdateSteps $steps)
    {
        $this->steps = $steps;
        $this->steps_class = get_class($this->steps);
    }

    /**
     * The hash for the objective is calculated over the classname and the steps
     * that are contained.
     */
    final public function getHash() : string
    {
        return hash(
            "sha256",
            self::class . $this->steps_class
        );
    }

    final public function getLabel() : string
    {
        return "Database update steps in $this->steps_class.";
    }

    /**
     * @inheritdocs
     */
    final public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilDBStepExecutionDBExistsObjective(),
            new ilDatabaseUpdatedObjective(),
            new ilDBStepReaderExistsObjective()
        ];
    }

    /**
     * @inheritdocs
     */
    public function achieve(Environment $environment) : Environment
    {
        $execution_log = $environment->getResource(ilDatabaseUpdateStepExecutionLog::class);
        $step_reader = $environment->getResource(ilDBStepReader::class);

        $last_started_step = $execution_log->getLastStartedStep($this->steps_class);
        $last_finished_step = $execution_log->getLastFinishedStep($this->steps_class);
        if ($last_started_step !== $last_finished_step) {
            $this->throwStepNotFinishedException($last_started_step, $last_finished_step);
            throw new LogicException(
                "ilDatabaseUpdateStepExecutionLog::throwStepNotFinishedException should throw an exception."
            );
        }

        if ($last_finished_step === $step_reader->getLatestStepNumber($this->steps_class, self::STEP_METHOD_PREFIX)) {
            return $environment;
        }

        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->steps->prepare($db);

        $steps = $step_reader->readStepNumbers($this->steps_class, self::STEP_METHOD_PREFIX);
        foreach ($steps as $step) {
            if ($step <= $last_finished_step) {
                continue;
            }
            $execution_log->started($this->steps_class, $step);
            $method = self::STEP_METHOD_PREFIX . $step;
            $this->steps->$method();
            $execution_log->finished($this->steps_class, $step);
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment) : bool
    {
        $execution_log = $environment->getResource(ilDatabaseUpdateStepExecutionLog::class);
        $step_reader = $environment->getResource(ilDBStepReader::class);

        return $execution_log->getLastFinishedStep($this->steps_class) !== $step_reader->getLatestStepNumber(
            $this->steps_class,
            self::STEP_METHOD_PREFIX
        );
    }

    protected function throwStepNotFinishedException(int $started, int $finished) : void
    {
        throw new RuntimeException(
            "For update steps in $this->steps_class: step $started was started " .
            "last, but step $finished was finished last. Aborting because of that " .
            "mismatch."
        );
    }
}
