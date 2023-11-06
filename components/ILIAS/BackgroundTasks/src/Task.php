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
use ILIAS\BackgroundTasks\Types\Type;

/**
 * Interface Task
 * @package ILIAS\BackgroundTasks
 *          A Task is the basic interface of an "thing" which can be put into a Bucket and will be
 *          run or triggered by the BackgroundTask-Worker. Currently there are two types of Tasks:
 *          - Job: A Task, which can be run without any interaction with the user such as zipping
 *          files or just collecting some data
 *          - UserInteraction: A Task in the Bucket, which will need some User-Interaction before
 *          running the task. A User-Interaction is provided as Button in the UserInterface such as
 *          [ Cancel ] or [ Download ]
 */
interface Task
{
    public function getType(): string;

    /**
     * @return Type[] A list of types that are taken as input.
     */
    public function getInputTypes(): array;

    public function getOutputType(): Type;

    public function getOutput(): Value;

    /**
     * @param $values (Value|Task)[]
     */
    public function setInput(array $values): void;

    /**
     * @return Value[]
     */
    public function getInput(): array;

    /**
     * @return Task[] A list of tasks that is chained with this task. The first element will be
     *                this tasks, the following his dependencies.
     */
    public function unfoldTask(): array;

    /**
     * @return Option   An Option to remove the current task and do some cleanup if possible. This
     *                  Option is displayed if the Bucket is completed. You do not have to provide
     *                  an additional Option to remove in your UserInteraction, the remove-Option
     *                  is added to the list of Options (last position)
     * @see self::getAbortOption();
     */
    public function getRemoveOption(): Option;

    /**
     * @return Option   In case a Job is failed or did not respond for some time, an Abort-Option
     *                  is displayed. There is already a Standard-Abort-Option registered, you can
     *                  override with your own and do some cleanup if possible.
     */
    public function getAbortOption(): Option;
}
