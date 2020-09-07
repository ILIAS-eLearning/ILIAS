<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints\Password;

use ILIAS\Data;

/**
 * Factory for creating password constraints.
 */
class Factory
{
    /**
     * @var ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    /**
     * Get the constraint that a password has a minimum length.
     *
     * @param	int	$min_length
     * @return	Constraint
     */
    public function hasMinLength($min_length)
    {
        return new HasMinLength($min_length, $this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has upper case chars.
     *
     * @return	Constraint
     */
    public function hasUpperChars()
    {
        return new HasUpperChars($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has lower case chars.
     *
     * @return	Constraint
     */
    public function hasLowerChars()
    {
        return new HasLowerChars($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has numbers.
     *
     * @return	Constraint
     */
    public function hasNumbers()
    {
        return new HasNumbers($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that a password has special chars.
     *
     * @return  Password
     */
    public function hasSpecialChars()
    {
        return new HasSpecialChars($this->data_factory, $this->lng);
    }
}
