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

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;

class Parallel extends Constraint
{
    /**
     * There's a test to show this state will never be visible
     * ParallelTest::testCorrectErrorMessagesAfterMultiAccept
     *
     * @var Constraint[]
     */
    protected array $failed_constraints;

    /**
     * @param Constraint[] $constraints
     * @param Data\Factory $data_factory
     * @param \ILIAS\Language\Language $lng
     */
    public function __construct(array $constraints, Data\Factory $data_factory, \ILIAS\Language\Language $lng)
    {
        parent::__construct(
            function ($value) use ($constraints): bool {
                $ret = true;
                $this->failed_constraints = [];
                foreach ($constraints as $constraint) {
                    if (!$constraint->accepts($value)) {
                        $this->failed_constraints[] = $constraint;
                        $ret = false;
                    }
                }

                return $ret;
            },
            function ($txt, $value): string {
                $messages = [];
                foreach ($this->failed_constraints as $constraint) {
                    $messages[] = $constraint->getErrorMessage($value);
                }

                return implode(" ", $messages);
            },
            $data_factory,
            $lng
        );
    }
}
