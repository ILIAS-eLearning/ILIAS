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

/**
 * Persons can export the value, e.g. in a CSV or vCard. The phrases are
 * rendered as "<by> can export <data> <where>, to <in_order_to>."
 */
readonly class Export implements TechnicalRequirement
{
    use RequiresPhrases;

    /**
     * @param string $where     where, with its preposition, e.g. "in the list of the staff as CSV"
     * @param string $by        who can export it, e.g. "persons with the \"Read\" permission"
     * @param string $in_order_to   what for, continuing "to"
     * @param string $condition when, continuing "If"
     */
    public function __construct(
        public string $where,
        public string $by,
        public string $in_order_to,
        public string $condition = '',
    ) {
        self::requirePhrases(where: $where, by: $by, in_order_to: $in_order_to);
    }

    public function describe(): string
    {
        return "export:{$this->where}";
    }
}
