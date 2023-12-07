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

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\BuildTransformation;

class Group
{
    public function __construct(private readonly BuildTransformation $build_transformation)
    {
    }

    /**
     * @param Transformation[] $other
     */
    public function logicalOr(array $other): Transformation
    {
        return $this->build_transformation->fromConstraint(new LogicalOr($other));
    }

    public function not(Constraint $constraint): Transformation
    {
        return $this->build_transformation->fromConstraint(new Not($constraint, $this->dataFactory, $this->language));
    }

    /**
     * @param Transformation[] $constraints
     */
    public function parallel(array $constraints): Transformation
    {
        return $this->build_transformation->fromConstraint(new Parallel($constraints));
    }

    /**
     * @param Transformation[] $constraints
     */
    public function sequential(array $constraints): Transformation
    {
        return $this->build_transformation->fromConstraint(new Sequential($constraints));
    }
}
