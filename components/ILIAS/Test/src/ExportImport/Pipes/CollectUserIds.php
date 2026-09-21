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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;

/**
 * Collects user IDs during normalization. This is used to export user information the test object has references
 * to (e.g. participants, feedback authors).
 */
class CollectUserIds implements Processor
{
    /**
     * @var array<int, true> $ids
     */
    private array $ids = [];

    /**
     * @inheritDoc
     */
    public function process(object $carry): void
    {
        if (
            $carry instanceof NormalizeCarry
            && $carry->value() instanceof Id
            && $carry->value()->getObject() === 'user'
        ) {
            $this->ids[$carry->value()->getId()] = true;
        }
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
