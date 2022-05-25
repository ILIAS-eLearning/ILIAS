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

    private int $counter = 0;
    
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
                $current_server = new ilLDAPServer($server_id);
                $current_server->doConnectionCheck();
                $this->logger->info("LDAP: starting user synchronization for " . $current_server->getName());

                $ldap_query = new ilLDAPQuery($current_server);
                $ldap_query->bind();
                
                if (is_array($users = $ldap_query->fetchUsers())) {
                    // Deactivate ldap users that are not in the list
                    $this->deactivateUsers($current_server, $users);
                }
            
                if (count($users)) {
                    ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

                    $offset = 0;
                    $limit = 500;
                    while ($user_sliced = array_slice($users, $offset, $limit, true)) {
                        $this->logger->info("LDAP: Starting update/creation of users ...");
                        $this->logger->info("LDAP: Offset: " . $offset);
                        $ldap_to_ilias = new ilLDAPAttributeToUser($current_server);
                        $ldap_to_ilias->setNewUserAuthMode($current_server->getAuthenticationMappingKey());
                        $ldap_to_ilias->setUserData($user_sliced);
                        $ldap_to_ilias->refresh();
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
        if (count($messages)) {
            $result->setMessage(implode("\n", $messages));
        }
        $result->setStatus($status);
        return $result;
    }
    
    /**
     * Deactivate users that are disabled in LDAP
     */
    private function deactivateUsers(ilLDAPServer $server, array $a_ldap_users) : void
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
        if ($a_form_id === ilAdministrationSettingsFormHandler::FORM_LDAP) {
            $a_fields["ldap_user_sync_cron"] = [$a_is_active ?
                $this->lng->txt("enabled") :
                $this->lng->txt("disabled"),
                ilAdministrationSettingsFormHandler::VALUE_BOOL];
        }
    }
}
