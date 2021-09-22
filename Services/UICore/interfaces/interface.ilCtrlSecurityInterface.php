<?php

/**
 * Interface ilCtrlSecurityInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * Classes implementing this interface are used to determine
 * whether commands for this class can be executed safely
 * without CSRF validation.
 */
interface ilCtrlSecurityInterface
{
    /**
     * Returns all commands of a class that can be executed
     * without CSRF validation.
     *
     * @return string[]
     */
    public function getSafeCommands() : array;
}