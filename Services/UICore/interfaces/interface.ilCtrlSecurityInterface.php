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
     * This method must return a list of unsafe GET commands.
     *
     * Unsafe get commands returned by this method will now be CSRF
     * protected, which means an ilCtrlToken is appended each time
     * a link-target is generated to the class implementing this
     * interface with a command from that list.
     *
     * Tokens will be validated in @see ilCtrlInterface::getCmd(),
     * whereas the fallback command will be used if the CSRF
     * validation fails.
     *
     * @return string[]
     */
    public function getUnsafeGetCommands(): array;

    /**
     * This method must return a list of safe POST commands.
     *
     * Safe post commands returned by this method will no longer be
     * CSRF protected and will NOT be appended by an ilCtrlToken.
     *
     * @return string[]
     */
    public function getSafePostCommands(): array;
}
