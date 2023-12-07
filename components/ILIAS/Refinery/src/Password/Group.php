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

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\BuildTransformation;
use ILIAS\Refinery\Transformation;

class Group
{
    public function __construct(BuildTransformation $build_transformation)
    {
    }

    /**
     * Get the constraint that a password has a minimum length.
     */
    public function hasMinLength(int $min_length): Transformation
    {
        return $this->build_transformation->fromConstraint(new HasMinLength($min_length));
    }

    /**
     * Get the constraint that a password has upper case chars.
     */
    public function hasUpperChars(): Transformation
    {
        return $this->build_transformation->fromConstraint(new HasUpperChars());
    }

    /**
     * Get the constraint that a password has lower case chars.
     */
    public function hasLowerChars(): Transformation
    {
        return $this->build_transformation->fromConstraint(new HasLowerChars());
    }

    /**
     * Get the constraint that a password has numbers.
     */
    public function hasNumbers(): Transformation
    {
        return $this->build_transformation->fromConstraint(new HasNumbers());
    }

    /**
     * Get the constraint that a password has special chars.
     */
    public function hasSpecialChars(): Transformation
    {
        return $this->build_transformation->fromConstraint(new HasSpecialChars());
    }
}
