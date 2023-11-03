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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Paths\Steps\StepInterface;

interface PathInterface
{
    /**
     * Get all steps in the path.
     * @return StepInterface[]
     */
    public function steps(): \Generator;

    /**
     * Relative paths start at some otherwise determined element,
     * absolute paths start at root.
     */
    public function isRelative(): bool;

    /**
     * Specifies whether the path should point to exactly one
     * element, or whether it can also lead to no or many elements.
     */
    public function leadsToExactlyOneElement(): bool;

    public function toString(): string;
}
