<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * A password is used as part of credentials for authentication.
 * This is a quite specific kind of data - worth to be protected and
 * not to be confused by mistake.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Password
{

    /**
     * @var string
     */
    private $pass;

    public function __construct($pass)
    {
        if (!is_string($pass)) {
            throw new \InvalidArgumentException('Invalid value for $pass');
        }
        $this->pass = $pass;
    }

    /**
     * Get the password-string.
     *
     * @return  string
     */
    public function toString()
    {
        return $this->pass;
    }
}
