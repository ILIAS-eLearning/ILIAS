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
     * @return string - a meaningful and concise description for your migration.
     */
    public function getLabel(): string;

    /**
     * Tell the default amount of steps to be executed for one run of the migration.
     * Return Migration::INFINITE if all units should be migrated at once.
     */
    public function getDefaultAmountOfStepsPerRun(): int;

    /**
     * Objectives the migration depend on.
     *
     * @throw UnachievableException if the objective is not achievable
     * @return Objective[]
     */
    public function getPreconditions(Environment $environment): array;

    /**
     * Prepare the migration by means of some environment.
     *
     * This is not supposed to modify the environment, but will be run to prime the
     * migration object to run `step` and `getRemainingAmountOfSteps` afterwards.
     */
    public function prepare(Environment $environment): void;

    /**
     *  Run one step of the migration.
     */
    public function step(Environment $environment): void;

    /**
     * Count up how many "things" need to be migrated. This helps the admin to
     * decide how big he can create the steps and also how long a migration takes
     */
    public function getRemainingAmountOfSteps(): int;
}
