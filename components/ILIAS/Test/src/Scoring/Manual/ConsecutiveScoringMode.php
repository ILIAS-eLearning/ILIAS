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
    public const MODE_USER = 'u';
    public const MODE_QUESTION = 'q';
    public const MODE_ALL = 'a';
    public const MODE_ONE = 'o';

    public function __construct(
        protected readonly string $transposition,
        protected readonly string $cardinality
    ) {
    }

    public function isUserCentric(): bool
    {
        return $this->transposition === self::MODE_USER;
    }

    public function isSingle(): bool
    {
        return $this->cardinality === self::MODE_ONE;
    }

}
