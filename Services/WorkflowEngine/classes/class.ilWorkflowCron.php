<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilWorkflowCron is part of the petri net based workflow engine.
 *
 * This helper class is called from the cron job. Here, all automatic events
 * are wired up for the workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowCron
{
    /**
     * This method is the main entry point for all chron tasks that are to
     * be done by/for/with the workflow engine.
     * Please keep this method clean and shiny. Call out to other methods from
     * here and leave the ordering here clear enough to make it a useful tool
     * to control the facile order of events.
     */
    public static function executeCronjob()
    {
        self::raiseTimePassedEvent();
    }

    /**
     * Raises the generic "time passed" event.
     */
    public static function raiseTimePassedEvent()
    {
        /** @noinspection PhpIncludeInspection */
        require_once './Services/WorkflowEngine/classes/class.ilWorkflowEngine.php';
        $workflow_engine = new ilWorkflowEngine();

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
