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

namespace ILIAS\Component\Dependencies;

class Name
{
    private const PROPER_NAME_REGEXP = "/\w+([\\\\]\w+){2,}/";

    public function __construct(
        protected string $name
    ) {
        if (!preg_match(self::PROPER_NAME_REGEXP, $this->name)) {
            throw new \InvalidArgumentException(
                "{$this->name} is not a proper name for a dependency."
            );
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
