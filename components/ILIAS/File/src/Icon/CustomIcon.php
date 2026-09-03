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

namespace ILIAS\File\Icon;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class CustomIcon implements Icon
{
    public function __construct(
        private string $rid,
        private bool $active,
        private bool $is_default_icon,
        private array $suffixes
    ) {
    }

    public function getRid(): string
    {
        return $this->rid;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isDefaultIcon(): bool
    {
        return $this->is_default_icon;
    }

    /**
     * @return mixed[]
     */
    public function getSuffixes(): array
    {
        return $this->suffixes;
    }
}
