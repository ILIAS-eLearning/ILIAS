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

namespace ILIAS\Test\Scoring\Manual;

class ConsecutiveScoringMode
{
    public const ORIENTATION_USER = 'u';
    public const ORIENTATION_QUESTION = 'q';
    public const MODE_ALL_AT_ONCE = 'a';
    public const MODE_ONE_BY_ONE = 'o';

    public function __construct(
        protected readonly string $transposition,
        protected readonly string $cardinality
    ) {
    }

    public function isUserCentric(): bool
    {
        return $this->transposition === self::ORIENTATION_USER;
    }

    public function isSingle(): bool
    {
        return $this->cardinality === self::MODE_ONE_BY_ONE;
    }

}
