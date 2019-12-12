<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class interface
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
interface ilAuthProviderAccountMigrationInterface
{
    
    /**
     * Get auth mode which triggered the account migration
     * 2_1 for ldap account migration with server id 1
     * 11 for apache auth
     *
     * @see ilAuthUtils
     * @return string
     */
    public function getTriggerAuthMode();
    
    /**
     * Get user auth mode name
     * ldap_1 for ldap account migration with server id 1
     * apache for apache auth
     */
    public function getUserAuthModeName();
    
    /**
     * Get external account name
     * @return string
     */
    public function getExternalAccountName();
    
    
    
    /**
     * Create new account
     * @param ilAuthStatus
     */
    public function migrateAccount(ilAuthStatus $status);
    
    
    /**
     * Create new ILIAS account for external_account
     * @param ilAuthStatus
     */
    public function createNewAccount(ilAuthStatus $status);
}
