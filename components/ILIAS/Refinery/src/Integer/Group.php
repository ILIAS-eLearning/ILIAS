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

namespace ILIAS\Refinery\Integer;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\In\Group as In;
use ILIAS\Language\Language;

class Group
{
    public function __construct(
        private readonly Factory $dataFactory,
        private readonly Language $language,
        private readonly In $in
    ) {
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than the defined lower limit.
     */
    public function isGreaterThan(int $minimum): Constraint
    {
        return new GreaterThan($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than the defined upper limit.
     */
    public function isLessThan(int $maximum): Constraint
    {
        return new LessThan($maximum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than or equal the defined lower limit.
     */
    public function isGreaterThanOrEqual(int $minimum): Constraint
    {
        return new GreaterThanOrEqual($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than or equal the defined upper limit.
     */
    public function isLessThanOrEqual(int $maximum): Constraint
    {
        return new LessThanOrEqual($maximum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if an integer value is between the given lower and upper bounds.
     * The ranges are inclusive [$lower_bound, $upper_bound].
     */
    public function isBetween(int $lower_bound, int $upper_bound): Constraint
    {
        return $this->in->series([
            $this->isGreaterThanOrEqual($lower_bound),
            $this->isLessThanOrEqual($upper_bound),
        ]);
    }
}
