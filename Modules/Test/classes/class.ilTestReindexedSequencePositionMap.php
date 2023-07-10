<?php

declare(strict_types=1);

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

/**
 * Class ilTestReindexedSequencePositionMap
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @package     Modules/Test
 */
class ilTestReindexedSequencePositionMap
{
    /** @var array<int, int> */
    private array $sequencePositionMap = [];

    public function addPositionMapping(int $oldSequencePosition, int $newSequencePosition): void
    {
        $this->sequencePositionMap[$oldSequencePosition] = $newSequencePosition;
    }

    public function getNewSequencePosition(int $oldSequencePosition): ?int
    {
        return $this->sequencePositionMap[$oldSequencePosition] ?? null;
    }
}
