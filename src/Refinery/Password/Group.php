<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password;

use ILIAS\Data\Factory;
use ilLanguage;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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
    public function hasMinLength(int $min_length) : HasMinLength
    {
        return new HasMinLength($min_length, $this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has upper case chars.
     */
    public function hasUpperChars() : HasUpperChars
    {
        return new HasUpperChars($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has lower case chars.
     */
    public function hasLowerChars() : HasLowerChars
    {
        return new HasLowerChars($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has numbers.
     */
    public function hasNumbers() : HasNumbers
    {
        return new HasNumbers($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has special chars.
     */
    public function hasSpecialChars() : HasSpecialChars
    {
        return new HasSpecialChars($this->data_factory, $this->lng);
    }
}
