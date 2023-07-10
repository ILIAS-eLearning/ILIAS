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
 * ilWorkflowUtils is part of the petri net based workflow engine.
 *
 * This class collects methods that are used throughout the workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowUtils
{
    /**
     * Method that wraps PHPs time in order to allow simulations with the workflow.
     *
     * @return int
     */
    public static function time(): int
    {
        if (defined('IL_PHPUNIT_TEST') && IL_PHPUNIT_TEST) {
            global $DIC;
            /** @var ilSetting $ilSetting */
            $ilSetting = $DIC['ilSetting'];

            return (int) $ilSetting->get('IL_PHPUNIT_TEST_TIME', (string) time());
        }

        return time();
    }

    public static function microtime(): string
    {
        if (defined('IL_PHPUNIT_TEST') && IL_PHPUNIT_TEST) {
            global $DIC;
            /** @var ilSetting $ilSetting */
            $ilSetting = $DIC['ilSetting'];

            return $ilSetting->get('IL_PHPUNIT_TEST_MICROTIME', (string) time());
        }

        return (string) microtime();
    }

    /**
     * Handles the generic time_passed event.
     * @param ilWorkflowEngine|null $workflow_engine
     */
    public static function handleTimePassedEvent(ilWorkflowEngine $workflow_engine = null): void
    {
        if (!$workflow_engine) {
            $workflow_engine = new ilWorkflowEngine();
        }

        $workflow_engine->processEvent(
            'time_passed',
            'time_passed',
            'none',
            0,
            'none',
            0
        );
    }
}
