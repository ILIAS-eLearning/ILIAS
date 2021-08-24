<?php

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

class TQP80Migration implements Migration
{
    /**
     * @return string - a meaningful and concise description for your migration.
     */
    public function getLabel() : string
    {
        return "Migrations for TestQuestionPool in ILIAS 8";
    }

    /**
     * Tell the default amount of steps to be executed for one run of the migration.
     * Return Migration::INFINITE if all units should be migrated at once.
     */
    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 10;
    }

    /**
     * Objectives the migration depends on.
     *
     * @throw UnachievableException if the objective is not achievable
     * @return Objective[]
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [];
    }

    /**
     * Prepare the migration by means of some environment.
     *
     * This is not supposed to modify the environment, but will be run to prime the
     * migration object to run `step` and `getRemainingAmountOfSteps` afterwards.
     */
    public function prepare(Environment $environment) : void
    {
        // Prepare the environment for the following steps here.
    }

    /**
     *  Run one step of the migration.
     */
    public function step(Environment $environment) : void
    {
        /** @var ilDBInterface $db */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $db->manipulate("DELETE FROM qpl_qst_type WHERE type_tag = 'assJavaApplet'");
        $db->manipulate("DELETE FROM qpl_qst_type WHERE type_tag = 'assFlashQuestion'");
    }

    /**
     * Count up how many "things" need to be migrated. This helps the admin to
     * decide how big he can create the steps and also how long a migration takes
     */
    public function getRemainingAmountOfSteps() : int
    {
        // Make some calculation to return the remaining amount of steps
        return 1;
    }
}