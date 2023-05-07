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
namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;

/**
 * Class supportsTerminating
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface supportsTerminating
{
    /**
     * @param Closure $callback which is called, when a user explicitly
     *                          terminates  a Tool via the GUI. This callback
     *                          is called asynchronously.
     * @return supportsTerminating|Tool
     */
    public function withTerminatedCallback(Closure $callback) : supportsTerminating;

    /**
     * @return Closure|null
     */
    public function getTerminatedCallback() : ?Closure;

    /**
     * @return bool
     */
    public function hasTerminatedCallback() : bool;
}
