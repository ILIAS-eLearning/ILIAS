<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Interface ilCtrlTokenInterface describes an ilCtrl token.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTokenInterface
{
    /**
     * Compares the given token to the stored one of the given user.
     *
     * @param string $token
     * @return bool
     */
    public function verifyWith(string $token) : bool;

    /**
     * Returns the token string of this instance.
     *
     * @return string
     */
    public function getToken() : string;
}
