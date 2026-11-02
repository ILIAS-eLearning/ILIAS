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
declare(strict_types=1);

namespace ILIAS\BackgroundTasks\Registry;

use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

/**
 * All tasks and values the components contribute via
 * `$contribute[\ILIAS\BackgroundTasks\Task::class]` and
 * `$contribute[\ILIAS\BackgroundTasks\Value::class]`.
 *
 * Buckets store their tasks and values by class name, so every class a
 * component puts into a bucket must be contributed, including deprecated
 * names that buckets created before an update may still reference. The
 * contributed instance serves as prototype, each task or value of a bucket is
 * a clone of it, so a contribution must not carry state of a single bucket.
 *
 * A component that renames or moves one of its tasks or values is itself
 * responsible for keeping stored buckets loadable. BackgroundTasks does not
 * migrate stored class names, and no other component must write them. Keep
 * the former name as an empty, deprecated subclass of the new class,
 * contribute both, and remove the former name with the next major release.
 */
interface Registry
{
    public function isRegistered(string $class_name): bool;

    /**
     * @throws \OutOfBoundsException if no task with this class name is registered
     */
    public function newTask(string $class_name): Task;

    /**
     * @throws \OutOfBoundsException if no value with this class name is registered
     */
    public function newValue(string $class_name): Value;
}
