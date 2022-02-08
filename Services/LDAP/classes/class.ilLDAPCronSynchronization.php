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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilLDAPCronSynchronization extends ilCronJob
{
    private ilLanguage $lng;
    private ilLogger $logger;
    private ilCronManager $cronManager;
    
    private $current_server = null;
    private $ldap_query = null;
    private $ldap_to_ilias = null;
    private $counter = 0;
    
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->auth();
        $this->cronManager = $DIC->cron()->manager();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ldap');
    }

    public function getId() : string
    {
        return "ldap_sync";
    }
    
    public function getTitle() : string
    {
        return $this->lng->txt('ldap_user_sync_cron');
    }
    
    public function getDescription() : string
    {
        return $this->lng->txt("ldap_user_sync_cron_info");
    }
    
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }
    
    public function hasAutoActivation() : bool
    {
        return false;
    }
    
    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    public function run() : ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;
    
        $messages = array();
        foreach (ilLDAPServer::_getCronServerIds() as $server_id) {
            try {
                $this->current_server = new ilLDAPServer($server_id);
                $this->current_server->doConnectionCheck();
                $this->logger->info("LDAP: starting user synchronization for " . $this->current_server->getName());
                
                $this->ldap_query = new ilLDAPQuery($this->current_server);
                $this->ldap_query->bind(ilLDAPQuery::LDAP_BIND_DEFAULT);
                
                if (is_array($users = $this->ldap_query->fetchUsers())) {
                    // Deactivate ldap users that are not in the list
                    $this->deactivateUsers($this->current_server, $users);
                }
            
                if (count($users)) {
                    ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

                    $offset = 0;
                    $limit = 500;
                    while ($user_sliced = array_slice($users, $offset, $limit, true)) {
                        $this->logger->info("LDAP: Starting update/creation of users ...");
                        $this->logger->info("LDAP: Offset: " . $offset);
                        $this->ldap_to_ilias = new ilLDAPAttributeToUser($this->current_server);
                        $this->ldap_to_ilias->setNewUserAuthMode($this->current_server->getAuthenticationMappingKey());
                        $this->ldap_to_ilias->setUserData($user_sliced);
                        $this->ldap_to_ilias->refresh();
                        $this->logger->info("LDAP: Finished update/creation");
                        
                        $offset += $limit;

                        $this->cronManager->ping($this->getId());
                    }
                    $this->counter++;
                } else {
                    $this->logger->info("LDAP: No users for update/create. Aborting.");
                }
            } catch (ilLDAPQueryException $exc) {
                $mess = $exc->getMessage();
                $this->logger->info($mess);
                
                $messages[] = $mess;
            }
        }
    
        if ($this->counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        if (sizeof($messages)) {
            $result->setMessage(implode("\n", $messages));
        }
        $result->setStatus($status);
        return $result;
    }
    
    /**
     * Deactivate users that are disabled in LDAP
     */
    private function deactivateUsers(ilLDAPServer $server, $a_ldap_users)
    {
        $inactive = [];

        foreach (ilObjUser::_getExternalAccountsByAuthMode($server->getAuthenticationMappingKey(), true) as $usr_id => $external_account) {
            if (!array_key_exists($external_account, $a_ldap_users)) {
                $inactive[] = $usr_id;
            }
        }
        if (count($inactive)) {
            ilObjUser::_toggleActiveStatusOfUsers($inactive, false);
            $this->logger->info('LDAP: Found ' . count($inactive) . ' inactive users.');
            
            $this->counter++;
        } else {
            $this->logger->info('LDAP: No inactive users found');
        }
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active) : void
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_LDAP:
                $a_fields["ldap_user_sync_cron"] = [ $a_is_active ?
                    $this->lng->txt("enabled") :
                    $this->lng->txt("disabled"),
                ilAdministrationSettingsFormHandler::VALUE_BOOL];
                break;
        }
    }
}
