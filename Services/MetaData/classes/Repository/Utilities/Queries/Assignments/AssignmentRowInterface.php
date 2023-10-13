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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Assignments;

interface AssignmentRowInterface
{
    public function table(): string;

    public function id(): int;

    public function setId(int $id): void;

    public function idFromParentTable(): int;

    /**
     * @return ActionAssignmentInterface[]
     */
    public function actions(): \Generator;

    /**
     * Note that this does **not** clone!
     */
    public function addAction(
        ActionAssignmentInterface $assignment
    ): void;
}
