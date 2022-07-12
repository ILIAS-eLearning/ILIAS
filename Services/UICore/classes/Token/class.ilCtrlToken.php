<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlToken is responsible for generating and storing
 * unique CSRF tokens.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlToken implements ilCtrlTokenInterface
{
    /**
     * Holds a temporarily generated token.
     *
     * @var string
     */
    private string $token;

    /**
     * ilCtrlToken Constructor
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function verifyWith(string $token) : bool
    {
        return ($this->token === $token);
    }

    /**
     * @inheritDoc
     */
    public function getToken() : string
    {
        return $this->token;
    }
}
