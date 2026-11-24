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
 * The value is presented in the user interface. The phrases are rendered in
 * the generated privacy documentation as "<data> is presented <where> to
 * <to>, to <in_order_to>."
 */
readonly class DisplayToUser implements TechnicalRequirement
{
    use RequiresPhrases;

    /**
     * @param string $where     where it is presented, with its preposition, e.g. "in the marker of a person on the map"
     * @param string $to        who sees it, e.g. "every person who can see the map", "persons with the \"Read\" permission"
     * @param string $in_order_to   what for, continuing "to", e.g. "show who is where"
     * @param string $condition when, continuing "If", e.g. "the person has published it in their profile"
     */
    public function __construct(
        public string $where,
        public string $to,
        public string $in_order_to,
        public string $condition = '',
    ) {
        self::requirePhrases(where: $where, to: $to, in_order_to: $in_order_to);
    }

    public function describe(): string
    {
        return "display_to_user:{$this->where}";
    }
}
