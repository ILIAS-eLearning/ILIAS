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

class LogicalOr extends Constraint
{
    /**
     * LogicalOr constructor.
     * @param Constraint[] $other
     * @param Data\Factory $data_factory
     * @param \ILIAS\Language\Language $lng
     */
    public function __construct(array $other, Data\Factory $data_factory, \ILIAS\Language\Language $lng)
    {
        parent::__construct(
            static function ($value) use ($other): bool {
                foreach ($other as $constraint) {
                    if ($constraint->accepts($value)) {
                        return true;
                    }
                }

                return false;
            },
            static function ($value) use ($other): string {
                $problems = [];

                foreach ($other as $constraint) {
                    $problems[] = $constraint->getErrorMessage($value);
                }

                return 'Please fix one of these: ' . implode(', ', array_filter($problems));
            },
            $data_factory,
            $lng
        );
    }
}
