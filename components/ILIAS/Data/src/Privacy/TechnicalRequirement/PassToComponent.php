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
 * The value is handed to another ILIAS component, which documents what it
 * does with it. Rendered under "Integrated components".
 */
readonly class PassToComponent implements TechnicalRequirement
{
    use RequiresPhrases;

    /**
     * @param string $component the directory name of the component, e.g. "Mail"
     * @param string $in_order_to   what for, continuing "to", e.g. "send the profile of a new account by mail"
     */
    public function __construct(
        public string $component,
        public string $in_order_to,
    ) {
        self::requirePhrases(component: $component, in_order_to: $in_order_to);
    }

    public function describe(): string
    {
        return "pass_to:{$this->component}";
    }
}
