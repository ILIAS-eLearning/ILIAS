<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * A migration is a potentially long lasting operation that can be broken into
 * discrete steps. Other than database updates, it is supposed to run in the
 * background, even when the installation is online again.
 */
interface Migration
{
    public const INFINITE = -1;

    /**
     * @return string a meaningful name for your migration.
     */
    public function getLabel() : string;

    /**
     * @return string
     */
    public function getKey() : string;

    /**
     * Tell the default amount of steps to be executed for one run of the migration.
     * Return Migration::INFINITE if all units should be migrated at once.
     *
     * @return int
     */
    public function getDefaultAmountOfStepsPerRun() : int;

    /**
     * Objectives the migration depend on.
     *
     * @throw UnachievableException if the objective is not achievable
     * @return Objective[]
     */
    public function getPreconditions(Environment $environment) : array;

    /**
     * Prepare the migration by means of some environment.
     *
     * This is not supposed to modify the environment, but will be run to prime the
     * migration object to run `step` and `getRemainingAmountOfSteps` afterwards.
     */
    public function prepare(Environment $environment) : void;

    /**
     *  Run one step of the migration.
     */
    public function step(Environment $environment) : void;

    /**
     * Count up how many "things" need to be migrated. This helps the admin to
     * decide how big he can create the steps and also how long a migration takes
     *
     * @return int
     */
    public function getRemainingAmountOfSteps() : int;
}
