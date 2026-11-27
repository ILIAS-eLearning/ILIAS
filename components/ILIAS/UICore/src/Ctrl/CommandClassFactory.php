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

namespace ILIAS\UICore\Ctrl;

/**
 * Creates the command classes of a component with the dependencies the
 * component bootstrap wired for it. Components contribute an implementation,
 * ilCtrl then creates the listed classes through it instead of calling their
 * constructor directly. Classes which are not listed by any factory are still
 * created with `new`, so command classes can be moved over one by one.
 *
 * Implementations are created at build time: their constructor MUST NOT do
 * more than storing the given dependencies.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface CommandClassFactory
{
    /**
     * @return class-string[] the command classes this factory creates.
     */
    public function getCommandClasses(): array;

    /**
     * @param Ctrl  $ctrl         the instance which requests the command class, so it can
     *                            be passed on without the bootstrap knowing about ilCtrl.
     * @param mixed ...$arguments the runtime arguments a caller would have passed
     *                            to the constructor, e.g. the ref_id of an object.
     */
    public function create(Ctrl $ctrl, string $class_name, mixed ...$arguments): object;
}
