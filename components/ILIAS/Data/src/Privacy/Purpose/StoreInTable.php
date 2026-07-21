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

namespace ILIAS\Data\Privacy\Purpose;

use ILIAS\Data\Privacy\Source\DbTarget;

/**
 * The value is persisted to a database table. The target is known and
 * therefore shows up in the generated privacy documentation.
 */
final readonly class StoreInTable implements Purpose
{
    public function __construct(
        private DbTarget $target,
    ) {
    }

    public function getTarget(): DbTarget
    {
        return $this->target;
    }

    public function describe(): string
    {
        return 'store_in:' . $this->target->describe();
    }
}
