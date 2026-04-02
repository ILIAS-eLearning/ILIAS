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
 */

declare(strict_types=1);

namespace ILIAS\Data;

/**
 * Data transfer object that carries the raw SVG data as a string,
 * created from trusted sources. This object is merely a type as
 * a mean to talk about SVG's and pass them between different layers
 * of the system, it does not validate whether the SVG is valid or
 * not.
 */
readonly class SVG implements \Stringable
{
    public function __construct(
        protected string $raw_svg_string,
    ) {
    }

    public function __toString(): string
    {
        return $this->raw_svg_string;
    }
}
