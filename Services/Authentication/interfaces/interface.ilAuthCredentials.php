<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * Set captcha code
     * @param type $a_code
     */
    public function setCaptchaCode($a_code);
    
    /**
     * Get captcha code
     */
    public function getCaptchaCode();
    
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
