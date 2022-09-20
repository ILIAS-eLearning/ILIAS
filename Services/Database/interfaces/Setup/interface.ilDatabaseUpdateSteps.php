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

/**
 * This interface tags classes that contain database update steps. It has no
 * requirements for any methods (currently), but instead is just used to mark
 * all classes containing update steps. The steps are then executed via
 * ilDatabaseUpdateStepsExecutedObjective.
 *
 * A soft requirement (which can not be expressed via this interface) is, that
 * the methods containing db-updates fit a certain naming scheme.
 *
 * Implement update steps on one or more tables by creating methods that follow
 * this schema:
 *
 * public function step_1() { ... }
 *
 * The ilDatabaseUpdateStepsExecutedObjective will figure out which of them
 * haven't been performed yet and need to be executed.
 *
 * If one class takes care of only one table or a set of related tables it will
 * be easier to maintain.
 *
 * If for some reason you rely on other objectives, e.g. steps from other db-update
 * classes, split the according update steps into two classes and use the precondition
 * mechanism of the objectives to express the dependency.
 */
interface ilDatabaseUpdateSteps
{
    /**
     * Prepare the execution of the steps.
     *
     * Do not use anything from the globals or the DIC inside your steps, only use
     * the instance of the database provided here.
     */
    public function prepare(\ilDBInterface $db): void;
}
