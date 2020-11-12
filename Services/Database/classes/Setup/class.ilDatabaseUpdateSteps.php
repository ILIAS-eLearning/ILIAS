<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

/**
 * This base-class simplifies the creation of (consecutive) database updates.
 *
 * Implement update steps on one or more tables by creating methods that follow
 * this schema:
 *
 * public function step_1(\ilDBInterface $db) { ... }
 *
 * The class will figure out which of them haven't been performed yet and need
 * to be executed.
 *
 * If the class takes care of only one table or a set of related tables it will
 * be easier to maintain.
 *
 * If for some reason you rely on other objectives, e.g. steps from other db-update
 * classes, implement `getAdditionalPreconditionsForStep`.
 */
abstract class ilDatabaseUpdateSteps implements Objective
{
    const STEP_METHOD_PREFIX = "step_";

    /**
     * @var	string[]|null
     */
    protected $steps = null;

    /**
     * @var	Objective
     */
    protected $base;

    /**
     * @param Objective $base for the update steps, i.e. the objective that should
     *                           have been reached before the steps of this class can
     *                           even begin. Most probably this should be
     *                           \ilDatabasePopulatedObjective.
     */
    public function __construct(
        Objective $base
    ) {
        $this->base = $base;
    }

    /**
     * Get preconditions for steps.
     *
     * The previous step will automatically be a precondition of every step but
     * will not be returned from this method.
     *
     * @return Objective[]
     */
    public function getAdditionalPreconditionsForStep(int $num) : array
    {
        return [];
    }

    /**
     * The hash for the objective is calculated over the classname and the steps
     * that are contained.
     */
    final public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    final public function getLabel() : string
    {
        return "Database update steps in " . get_class($this);
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
    final public function getPreconditions(Environment $environment) : array
    {
        $log = $environment->getResource(\ilDatabaseUpdateStepExecutionLog::class);

        if ($log) {
            $finished = $log->getLastFinishedStep(get_class($this));
        } else {
            $finished = 0;
        }

        return [$this->getStep($this->getLatestStepNum(), $finished)];
    }

    /**
     * @inheritdocs
     */
    final public function achieve(Environment $environment) : Environment
    {
        return $environment;
    }

    /**
     * @inheritDoc
     */
    final public function isApplicable(Environment $environment) : bool
    {
        return true;
    }

    /**
     * Get a database update step. Optionally tell which step is known to have
     * been finished to exclude it from the preconditions of the newer steps.
     *
     * @throws \LogicException if step is unknown
     */
    final public function getStep(int $num, int $finished = 0) : ilDatabaseUpdateStep
    {
        $cur = $this->base;
        foreach ($this->getSteps() as $s) {
            if ($s <= $finished) {
                continue;
            } elseif ($s <= $num) {
                $cur = new ilDatabaseUpdateStep($this, $s, $cur, ...$this->getAdditionalPreconditionsForStep($s));
            } else {
                break;
            }
        }

        return $cur;
    }

    /**
     * Get the number of latest database step in this class.
     */
    final public function getLatestStepNum() : int
    {
        $this->getSteps();
        return end($this->steps);
    }

    /**
     * Get the numbers of the steps in this class.
     *
     * @return int[]
     */
    final protected function getSteps() : array
    {
        if (!is_null($this->steps)) {
            return $this->steps;
        }

        $this->steps = [];

        foreach (get_class_methods(static::class) as $method) {
            if (stripos($method, self::STEP_METHOD_PREFIX) !== 0) {
                continue;
            }

            $number = substr($method, strlen(self::STEP_METHOD_PREFIX));

            if (!preg_match("/^[1-9]\d*$/", $number)) {
                throw new \LogicException("Method $method seems to be a step but has an odd looking number");
            }

            $this->steps[(int) $number] = (int) $number;
        }

        asort($this->steps);

        return $this->steps;
    }
}
