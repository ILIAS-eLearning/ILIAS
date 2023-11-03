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
    public function started(string $class, int $step): void;

    /**
     * @throws \LogicException	if the finished step does not match the previously started step
     */
    public function finished(string $class, int $step): void;

    /**
     * Returns 0 as "first" step.
     */
    public function getLastStartedStep(string $class): int;
    /**
     * Returns 0 as "first" step.
     */
    public function getLastFinishedStep(string $class): int;
}
