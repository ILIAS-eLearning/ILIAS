<?php

/**
 * Interface ilCtrlTokenInterface describes an ilCtrl token.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTokenInterface
{
    /**
     * Returns a unique token for the user of this instance.
     *
     * If the token isn't already stored in the database it will be
     * generated and done so.
     *
     * @return string
     */
    public function getToken() : string;

    /**
     * Compares the given token to the one of generated or stored for
     * the user of this instance.
     *
     * @param string $token
     * @return bool
     */
    public function verifyWith(string $token) : bool;
}