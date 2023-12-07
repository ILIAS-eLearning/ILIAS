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

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\BuildTransformation;

class Group
{
    public function __construct(private readonly BuildTransformation $build_transformation)
    {
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than the defined lower limit.
     */
    public function isGreaterThan(int $minimum): Transformation
    {
        return $this->build_transformation->fromConstraint(new GreaterThan($minimum));
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than the defined upper limit.
     */
    public function isLessThan(int $maximum): Transformation
    {
        return $this->build_transformation->fromConstraint(new LessThan($maximum));
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * greater than or equal the defined lower limit.
     */
    public function isGreaterThanOrEqual(int $minimum): Transformation
    {
        return $this->build_transformation->fromConstraint(new GreaterThanOrEqual($minimum));
    }

    /**
     * Creates a constraint that can be used to check if an integer value is
     * less than or equal the defined upper limit.
     */
    public function isLessThanOrEqual(int $maximum): Transformation
    {
        return $this->build_transformation->fromConstraint(new LessThanOrEqual($maximum));
    }
}
