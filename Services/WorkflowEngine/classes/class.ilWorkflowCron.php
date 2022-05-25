<?php declare(strict_types=1);

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
    public static function executeCronjob() : void
    {
        self::raiseTimePassedEvent();
    }

    /**
     * Raises the generic "time passed" event.
     */
    public static function raiseTimePassedEvent() : void
    {
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
