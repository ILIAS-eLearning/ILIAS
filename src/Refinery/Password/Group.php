<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery\Password;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
use ilLanguage;

class Group
{
    protected Factory $data_factory;
    protected ilLanguage $lng;

    public function __construct(Factory $data_factory, ilLanguage $lng)
    {
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    /**
     * Get the constraint that a password has a minimum length.
     */
    public function hasMinLength(int $min_length): Constraint
    {
        return new HasMinLength($min_length, $this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has upper case chars.
     */
    public function hasUpperChars(): Constraint
    {
        return new HasUpperChars($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has lower case chars.
     */
    public function hasLowerChars(): Constraint
    {
        return new HasLowerChars($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has numbers.
     */
    public function hasNumbers(): Constraint
    {
        return new HasNumbers($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has special chars.
     */
    public function hasSpecialChars(): Constraint
    {
        return new HasSpecialChars($this->data_factory, $this->lng);
    }
}
