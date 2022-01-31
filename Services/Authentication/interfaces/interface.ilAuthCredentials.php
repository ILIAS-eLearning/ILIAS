<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Interface of auth credentials
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
interface ilAuthCredentials
{
    /**
     * Set username
     */
    public function setUsername($a_name);
    
    /**
     * Get username
     */
    public function getUsername();
    
    /**
     * Set password
     */
    public function setPassword($a_password);
    
    /**
     * Get password
     */
    public function getPassword();

    /**
     * Set auth mode.
     * Used - for instance - for manual selection on login screen.
     * @param string $a_auth_mode
     */
    public function setAuthMode($a_auth_mode);
    
    /**
     * Get auth mode
     */
    public function getAuthMode();
}
