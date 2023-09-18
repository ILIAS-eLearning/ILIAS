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

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

/**
 * Interface TaskManager
 * @package ILIAS\BackgroundTasks
 * We add, remove or observe buckets with the background task manager.
 */
interface TaskManager
{
    /**
     * Actually executes a task.
     * @return mixed
     */
    public function executeTask(Task $task, Observer $observer);

    /**
     * Depending on your background task settings, executes or puts the task into the queue.
     * @internal param int $userId
     * @internal param Task $task
     */
    public function run(Bucket $bucket): void;

    /**
     * Continue a task that is the state UserInteraction with a given option.
     */
    public function continueTask(Bucket $bucket, Option $option): void;

    /**
     * Quits and deletes a Bucket with all it's Jobs
     */
    public function quitBucket(Bucket $bucket): void;
}
