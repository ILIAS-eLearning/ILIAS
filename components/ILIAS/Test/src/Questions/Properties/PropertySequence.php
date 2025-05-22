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

namespace ILIAS\Test\Questions\Properties;

class PropertySequence implements Property
{
    public function __construct(
        private readonly int $question_id,
        private readonly int $test_id,
        private readonly int $place_in_sequence
    ) {
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function getPlaceInSequence(): int
    {
        return $this->place_in_sequence;
    }


}
