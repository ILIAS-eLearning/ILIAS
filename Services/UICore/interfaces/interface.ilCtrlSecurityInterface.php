<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Interface ilCtrlSecurityInterface provides ilCtrl security
 * information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * Information gathered by this interface is stored in an artifact
 * as well. Currently, the only purpose is to gather a list of safe
 * commands which determines whether a CSRF-protection is necessary.
 */
interface ilCtrlSecurityInterface
{
    /**
     * This method must return a list of commands (strings) which
     * are considered unsafe.
     *
     * When building link-targets with ilCtrl to a class implementing
     * this interface, an ilCtrlToken will be automatically appended
     * when the action or command is one of these.
     *
     * When retrieving them with @see ilCtrl::getCmd() the current
     * request needs to pass a CSRF validation.
     *
     * @return string[]
     */
    public function getUnsafeCommands() : array;
}