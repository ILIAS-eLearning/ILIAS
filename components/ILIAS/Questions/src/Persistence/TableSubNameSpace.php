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

namespace ILIAS\Questions\Persistence;

class TableSubNameSpace
{
    /**
     * @param string $vendor Maximum four characters used to create the tables
     * @param string $sub_name_space Maximum eight characters used to create the tables
     */
    public function __construct(
        private readonly string $vendor,
        private readonly string $sub_name_space
    ) {
        if ($vendor === ''
            || $sub_name_space === ''
            || mb_strlen($vendor) > 6
            || mb_strlen($sub_name_space) > 8) {
            throw new \InvalidArgumentException(
                '$vendor cannot be empty or longer than 6, '
                . '$sub_name_space cannot be empty or longer than 8 characters.'
            );
        }
    }

    public function get(): string
    {
        if ($this->vendor === 'ILIAS') {
            return $this->sub_name_space;
        }
        return "{$this->vendor}_{$this->sub_name_space}";
    }
}
