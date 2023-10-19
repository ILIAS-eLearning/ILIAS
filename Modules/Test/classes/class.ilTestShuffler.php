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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Random\Seed\GivenSeed;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;

/**
 * @package Modules/Test
 * provides Transformations to shuffle questions and answers
 */
class ilTestShuffler
{
    public const FIXED_SHUFFLER_SEED_MIN_LENGTH = 8;

    public function __construct(
        protected Refinery $refinery
    ) {
    }

    public function getAnswerShuffleFor(
        int $question_id,
        int $active_id,
        int $pass_id
    ): ShuffleTransformation {
        $fixedSeed = $this->buildFixedShufflerSeed($question_id, $pass_id, $active_id);
        return $this->refinery->random()->shuffleArray(new GivenSeed($fixedSeed));
    }

    protected function buildFixedShufflerSeed(int $question_id, int $pass_id, int $active_id): int
    {
        $seed = ($question_id + $pass_id) * $active_id;
        if (is_float($seed) && is_float($seed = $active_id + $pass_id)) {
            $seed = $active_id;
        }

        $div = ceil((10 ** (self::FIXED_SHUFFLER_SEED_MIN_LENGTH - 1)) / $seed);
        if ($div > 1) {
            $seed = $seed * ($div + $seed % 10);
        }
        return (int) $seed;
    }
}
