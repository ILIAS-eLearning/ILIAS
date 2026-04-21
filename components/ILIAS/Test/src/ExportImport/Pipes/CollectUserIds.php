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

namespace ILIAS\Test\ExportImport\Pipes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;

/**
 * Pipe stores all user IDs during normalization. This is used to export user information the test object has references
 * to (e.g. participants, feedback authors).
 */
class CollectUserIds implements Pipe
{
    /**
     * @var array<int, true> $ids
     */
    private array $ids = [];

    /**
     * @inheritDoc
     */
    public function handle(mixed $passable, \Closure $next): mixed
    {
        if ($passable instanceof NormalizeCarry && $passable->value instanceof Id) {
            if ($passable->value->getObject() === 'user') {
                $this->ids[$passable->value->getId()] = true;
            }
        }

        return $next($passable);
    }

    /**
     * Get all user IDs collected during normalization.
     *
     * @return list<int>
     */
    public function getIds(): array
    {
        return array_keys($this->ids);
    }
}
