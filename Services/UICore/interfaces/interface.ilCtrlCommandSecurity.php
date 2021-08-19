<?php declare(strict_types=1);

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilCtrlCommandSecurity
{
    /**
     * Returns a list of command strings provided in the HTTP POST body, where a CSRF token is not verified by ilCtrl
     * @return string[]
     */
    public function getSafePostCommands() : array;

    /**
     * Returns a list of command strings provided in the HTTP query string, where a CSRF token MUST be verified by ilCtrl
     * @return string[]
     */
    public function getUnsafeGetCommands() : array;
}
