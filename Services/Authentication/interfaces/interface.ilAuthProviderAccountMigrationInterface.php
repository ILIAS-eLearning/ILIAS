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
     */
    public function getTriggerAuthMode() : string;
    
    /**
     * Get user auth mode name
     * ldap_1 for ldap account migration with server id 1
     * apache for apache auth
     */
    public function getUserAuthModeName() : string;
    
    /**
     * Get external account name
     */
    public function getExternalAccountName() : string;
    
    
    
    /**
     * Create new account
     */
    public function migrateAccount(ilAuthStatus $status) : void;
    
    
    /**
     * Create new ILIAS account for external_account
     */
    public function createNewAccount(ilAuthStatus $status) : void;
}
