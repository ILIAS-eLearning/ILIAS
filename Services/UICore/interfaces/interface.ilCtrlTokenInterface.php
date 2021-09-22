<?php

/**
 * ilCtrlTokenInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTokenInterface
{
    /**
     * @param string $token
     * @return bool
     */
    public function verify(string $token) : bool;

    /**
     * Returns a token for the current user.
     *
     * @return string
     */
    public function get() : string;
}