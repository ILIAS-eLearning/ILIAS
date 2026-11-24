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

namespace ILIAS\Data\Privacy\TechnicalRequirement;

use ILIAS\Data\Privacy\Source\DbTarget;

/**
 * The value is persisted to a database table. Rendered as "<data> is stored
 * to <in_order_to>."; the table is listed in the notes for authors.
 */
readonly class StoreInTable implements TechnicalRequirement
{
    use RequiresPhrases;

    /**
     * @param string $in_order_to   what for, continuing "to", e.g. "print it on certificates"
     * @param string $condition when, continuing "If"
     */
    public function __construct(
        public DbTarget $target,
        public string $in_order_to,
        public string $condition = '',
    ) {
        self::requirePhrases(in_order_to: $in_order_to);
    }

    public function describe(): string
    {
        return 'store_in:' . $this->target->describe();
    }
}
