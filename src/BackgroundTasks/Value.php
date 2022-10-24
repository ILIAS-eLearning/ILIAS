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

use ILIAS\BackgroundTasks\Types\Type;

/**
 * Interface Value
 * @package ILIAS\BackgroundTasks
 * The Value as a defined format of data passed between two tasks. IO MUST be serialisable
 * since it will bes stored in the database or somewhere else
 */
interface Value extends \Serializable
{
    /**
     * @return string Gets a hash for this Value. If two objects are the same the hash must be the
     *                same! if two objects are different you need to have as view collisions as
     *                possible.
     */
    public function getHash(): string;

    public function equals(Value $other): bool;

    public function getType(): Type;

    public function setParentTask(Task $parentTask): void;

    public function getParentTask(): Task;

    public function hasParentTask(): bool;

    public function setValue($value): void;
}
