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
    protected ?array $step_numbers = null;

    public function __construct(
        ilDatabaseUpdateSteps $steps
    ) {
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
            $this->steps_class
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
            new \ilDBStepExecutionDBExistsObjective(),
            new \ilDatabaseUpdatedObjective()
        ];
    }

    /**
     * @inheritdocs
     */
    public function achieve(Environment $environment) : Environment
    {
        $execution_log = $environment->getResource(\ilDatabaseUpdateStepExecutionLog::class);

        $last_started_step = $execution_log->getLastStartedStep($this->steps_class);
        $last_finished_step = $execution_log->getLastFinishedStep($this->steps_class);
        if ($last_started_step !== $last_finished_step) {
            $this->throwStepNotFinishedException($last_started_step, $last_finished_step);
            throw new LogicException(
                "ilDatabaseUpdateStepExecutionLog::throwStepNotFinishedException should throw an exception."
            );
        }

        if ($last_finished_step === $this->getLatestStepNumber()) {
            return $environment;
        }

        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->steps->prepare($db);

        $this->readSteps();
        foreach ($this->step_numbers as $step) {
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
        $execution_log = $environment->getResource(\ilDatabaseUpdateStepExecutionLog::class);
        return $execution_log->getLastFinishedStep($this->steps_class) !== $this->getLatestStepNumber();
    }

    protected function throwStepNotFinishedException(int $started, int $finished) : void
    {
        throw new \RuntimeException(
            "For update steps in $this->steps_class: step $started was started " .
            "last, but step $finished was finished last. Aborting because of that " .
            "mismatch."
        );
    }

    /**
     * Get the number of latest database step in this class.
     */
    final public function getLatestStepNumber() : int
    {
        $this->readSteps();
        return $this->step_numbers[count($this->step_numbers) - 1];
    }

    /**
     * Get a list of all steps in this class.
     */
    protected function readSteps() : void
    {
        if (!is_null($this->step_numbers)) {
            return;
        }

        $this->step_numbers = [];

        foreach (get_class_methods($this->steps_class) as $method) {
            if (stripos($method, self::STEP_METHOD_PREFIX) !== 0) {
                continue;
            }

            $number = substr($method, strlen(self::STEP_METHOD_PREFIX));

            if (!preg_match("/^[1-9]\d*$/", $number)) {
                throw new \LogicException("Method $method seems to be a step but has an odd looking number");
            }

            $this->step_numbers[] = (int) $number;
        }

        sort($this->step_numbers);
    }
}
