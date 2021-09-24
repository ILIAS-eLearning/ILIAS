<?php

/**
 * Interface ilCtrlSecurityInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface can be implemented by GUI classes in order
 * to provide ilCtrl with security information about it.
 */
interface ilCtrlSecurityInterface
{
    /**
     * This method should return a list of commands (strings)
     * which are considered "safe" commands.
     *
     * Safe in this scenario means, that no CSRF validation is
     * needed for the command to be executed.
     *
     * Any other commands calling the GUI class implementing this
     * interface are considered unsafe and need to check-pass a
     * CSRF validation.
     *
     * @return string[]
     */
    public function getSafeCommands() : array;
}