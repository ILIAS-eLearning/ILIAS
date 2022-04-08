<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilWorkflowUtils is part of the petri net based workflow engine.
 *
 * This class collects methods that are used throughout the workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowUtils
{
    /**
     * Method that wraps PHPs time in order to allow simulations with the workflow.
     *
     * @return integer
     */
    public static function time() : int
    {
        if (defined('IL_PHPUNIT_TEST') && IL_PHPUNIT_TEST == true) {
            global $DIC;
            /** @var ilSetting $ilSetting */
            $ilSetting = $DIC['ilSetting'];

            return $ilSetting->get('IL_PHPUNIT_TEST_TIME', time());
        }

        return time();
    }

    public static function microtime()// TODO PHP8-REVIEW Return type declaration missing
    {
        if (defined('IL_PHPUNIT_TEST') && IL_PHPUNIT_TEST == true) {
            global $DIC;
            /** @var ilSetting $ilSetting */
            $ilSetting = $DIC['ilSetting'];

            return $ilSetting->get('IL_PHPUNIT_TEST_MICROTIME', time());
        }

        return microtime();
    }

    /**
     * Handles the generic time_passed event.
     * @param ilWorkflowEngine|null $workflow_engine
     */
    public static function handleTimePassedEvent(ilWorkflowEngine $workflow_engine = null) : void
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
