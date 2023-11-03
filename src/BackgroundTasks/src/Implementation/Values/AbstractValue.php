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

namespace ILIAS\BackgroundTasks\Implementation\Values;

use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class AbstractValue
 * @package ILIAS\BackgroundTasks\Implementation\Values
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class AbstractValue implements Value
{
    protected Task $parentTask;

    public function getType(): Type
    {
        return new SingleType(static::class);
    }

    public function getParentTask(): Task
    {
        return $this->parentTask;
    }

    public function setParentTask(Task $parentTask): void
    {
        $this->parentTask = $parentTask;
    }

    public function hasParentTask(): bool
    {
        return isset($this->parentTask);
    }

    public function __serialize(): array
    {
        return ['data' => $this->serialize()];
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize($data['data']);
    }
}
