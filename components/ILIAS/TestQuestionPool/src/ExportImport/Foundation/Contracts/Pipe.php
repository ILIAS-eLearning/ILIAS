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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

/**
 * A pipe is responsible for processing the passable object. It can manipulate the passable object and pass it to the
 * next pipe.
 *
 * @template TPassable
 */
interface Pipe
{
    /**
     * Handle the passable object and return it
     *
     * @param TPassable $passable
     * @param \Closure $next
     * @return TPassable
     */
    public function handle(mixed $passable, \Closure $next): mixed;
}
