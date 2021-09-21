<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * This logs the execution of database update steps.
 *
 * @author: Richard Klees
 */
interface ilDatabaseUpdateStepExecutionLog
{
    /**
     * @throws \LogicException	if the previously started step has not finished
     */
    public function started(string $class, int $step) : void;

    /**
     * @throws \LogicException	if the finished step does not match the previously started step
     */
    public function finished(string $class, int $step) : void;

    /**
     * Returns 0 as "first" step.
     */
    public function getLastStartedStep(string $class) : int;
    /**
     * Returns 0 as "first" step.
     */
    public function getLastFinishedStep(string $class) : int;
}
