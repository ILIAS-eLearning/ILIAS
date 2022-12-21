<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\Services\Database\Integrity;

class ilDBIgnore
{
    /**
     * @var string[]
     */
    private array $values_to_ignore;

    public function __construct(?string ...$values_to_ignore)
    {
        $this->values_to_ignore = array_map(static function (?string $valueToIgnore): string {
            return null === $valueToIgnore ? 'IS NOT NULL' : '!= ' . $valueToIgnore;
        }, $values_to_ignore);
    }

    /**
     * @return string[]
     */
    public function values(): array
    {
        return $this->values_to_ignore;
    }
}
