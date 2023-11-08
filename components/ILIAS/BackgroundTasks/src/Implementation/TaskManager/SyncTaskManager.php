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

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionSkippedException;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\UserInteraction;

/**
 * Class BasicTaskManager
 * @package ILIAS\BackgroundTasks\Implementation
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * Basic Task manager. Will execute tasks immediately.
 * Some important infos:
 *         - The bucket and its tasks are not saved into the db upon execution
 *         - The percentage and current task are not updated during execution.
 *         - The bucket and its tasks inkl. percentage and current task are only saved into the DB
 *         when a user interaction occurs.
 */
class SyncTaskManager extends BasicTaskManager
{
    protected Persistence $persistence;

    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * This will add an Observer of the Task and start running the task.
     * @throws \Exception
     */
    public function run(Bucket $bucket): void
    {
        $task = $bucket->getTask();
        $bucket->setCurrentTask($task);
        $observer = new NonPersistingObserver($bucket);

        try {
            $task = $this->executeTask($task, $observer);
            if ($task instanceof UserInteraction && $task->canBeSkipped($task->getInput())) {
                throw new UserInteractionSkippedException('can be skipped');
            }
            $bucket->setState(State::FINISHED);
        } catch (UserInteractionSkippedException $e) {
            $bucket->setState(State::FINISHED);
        } catch (UserInteractionRequiredException $e) {
            // We're okay!
            $this->persistence->saveBucketAndItsTasks($bucket);
        }
    }
}
