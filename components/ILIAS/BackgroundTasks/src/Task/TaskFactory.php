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

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

interface TaskFactory
{
    /**
     * @param $class_name   string  The fully quallified classname to create
     * @param $input        Value[]|null The values you want as inputs to the task.
     */
    public function createTask(string $class_name, ?array $input = null): Task;
}
