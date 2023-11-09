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

namespace ILIAS\Badge;

use ilBadge;

class ModalContent
{
    /**
     * @param string<string, string> $properties
     */
    public function __construct(
        private readonly ilBadge $badge,
        private readonly array $properties
    ) {
    }

    public function badge(): ilBadge
    {
        return $this->badge;
    }

    /**
     * @return array<string, string>
     */
    public function properties(): array
    {
        return $this->properties;
    }

    /**
     * @param string<string, string> $properties
     */
    public function withAdditionalProperties(array $properties): self
    {
        return new self($this->badge(), $this->properties() + $properties);
    }
}
