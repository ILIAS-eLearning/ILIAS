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
 * ECS Event Handler
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ilCtrl_Calls
 * @ingroup ServicesWebServicesECS
 */
class ilECSAppEventListener implements ilAppEventListener
{
    private ilSetting $setting;
    private ilLogger $logger;

    public function __construct(
        \ilSetting $setting,
        \ilLogger $logger
    ) {
        $this->setting = $setting;
        $this->logger = $logger;
    }

    /**
    * Handle an event in a listener.
    * @param	string $a_component component, e.g. "Modules/Forum" or "Services/User"
    * @param	string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array  $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        global $DIC;
        
        $eventHandler = new static(
            $DIC->settings(),
            $DIC->logger()->wsrv()
            );
        $eventHandler->handle($a_component, $a_event, $a_parameter);
    }

    private function handle($a_component, $a_event, $a_parameter) : void
    {
        $this->logger->debug('Listening to event from: ' . $a_component . ' ' . $a_event);
        
        switch ($a_component) {
            case 'Services/User':
                switch ($a_event) {
                    case 'afterCreate':
                        $user = $a_parameter['user_obj'];
                        $this->handleMembership($user);
                        break;
                }
                break;
            
            case 'Modules/Group':

                $this->logger->debug('New event from group: ' . $a_event);
                switch ($a_event) {
                    case 'addSubscriber':
                    case 'addToWaitingList':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            
                            $settings = $this->initServer($a_parameter['usr_id']);
                            $this->extendAccount($settings, $user);
                            
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_PENDING);
                        }
                        break;
                        
                    case 'deleteParticipant':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED);
                        }
                        break;
                        
                    case 'addParticipant':
                        if ((ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs')) {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            
                            $settings = $this->initServer($user->getId());
                            
                            $this->extendAccount($settings, $user);
                            #$this->_sendNotification($settings,$user);
                            
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_ACTIVE);
                            unset($user);
                        }
                        break;
                        
                        
                
                }
                break;
                
            case 'Modules/Course':
                
                $this->logger->info(__METHOD__ . ': New event from course: ' . $a_event);
                switch ($a_event) {

                    case 'addSubscriber':
                    case 'addToWaitingList':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            
                            $settings = $this->initServer($a_parameter['usr_id']);
                            $this->extendAccount($settings, $user);
                            
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_PENDING);
                        }
                        break;
                        
                
                    case 'deleteParticipant':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED);
                        }
                        break;
                        
                    case 'addParticipant':
                        
                        if ((ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs')) {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            
                            $settings = $this->initServer($user->getId());
                            
                            $this->extendAccount($settings, $user);
                            $this->sendNotification($settings, $user);
                            
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_ACTIVE);
                            unset($user);
                        }
                        break;
                }
                break;
        }
    }
    
    /**
     * Init server settings
     * @param type $a_usr_id
     */
    private function initServer($a_usr_id)
    {
        $server_id = ilECSImportManager::getInstance()->lookupServerId($a_usr_id);

        $settings = ilECSSetting::getInstanceByServerId($server_id);
        
        return $settings;
    }
    
    /**
     * send notification about new user accounts
     *
     * @access protected
     */
    private function sendNotification(ilECSSetting $server, ilObjUser $user_obj)
    {
        if (!count($server->getUserRecipients())) {
            return true;
        }
        // If sub id is set => mail was send
        $import = new ilECSImport($server->getServerId(), $user_obj->getId());
        if ($import->getSubId()) {
            return false;
        }

        $lang = ilLanguageFactory::_getLanguage();
        $lang->loadLanguageModule('ecs');

        $mail = new ilMail(ANONYMOUS_USER_ID);
        $subject = $lang->txt('ecs_new_user_subject');

        // build body
        $body = $lang->txt('ecs_new_user_body') . "\n\n";
        $body .= $lang->txt('ecs_new_user_profile') . "\n\n";
        $body .= $user_obj->getProfileAsString($lang) . "\n\n";
        $body .= ilMail::_getAutoGeneratedMessageString($lang);
        
        $mail->enqueue($server->getUserRecipientsAsString(), "", "", $subject, $body, array());
        
        // Store sub_id = 1 in ecs import which means mail is send
        $import->setSubId(1);
        $import->save();
        
        return true;
    }
    
    /**
     * Assign missing course/groups to new user accounts
     * @param ilObjUser $user
     */
    private function handleMembership(ilObjUser $user)
    {
        $this->log->debug('Handling ECS assignments ');
        
        $assignments = ilECSCourseMemberAssignment::lookupMissingAssignmentsOfUser($user->getExternalAccount());
        foreach ($assignments as $assignment) {
            $msettings = ilECSNodeMappingSettings::getInstanceByServerMid(
                $assignment->getServer(),
                $assignment->getMid()
            );
            if ($user->getAuthMode() == $msettings->getAuthMode()) {
                $this->log->info('Adding user ' . $assignment->getUid() . ' to course/group: ' . $assignment->getObjId());
                $obj_type = ilObject::_lookupType($assignment->getObjId());
                if ($obj_type !== 'crs' && $obj_type !== 'grp') {
                    $this->log->error('Invalid assignment type: ' . $obj_type);
                    $this->log->logStack(ilLogLevel::ERROR);
                    continue;
                }
                $refs = ilObject::_getAllReferences($assignment->getObjId());
                $ref_id = end($refs);

                try {
                    $part = ilParticipants::getInstance((int) $ref_id);
                    if ($obj_type === 'crs') {
                        $part->add($user->getId(), ilCourseConstants::CRS_MEMBER);
                    } elseif ($obj_type === 'grp') {
                        $part->add($user->getId(), ilParticipants::IL_GRP_MEMBER);
                    }
                } catch (InvalidArgumentException $e) {
                    $this->log->error('Invalid ref_id given: ' . (int) $ref_id);
                    $this->log->logStack(ilLogLevel::ERROR);
                    continue;
                }
            } else {
                $this->log->notice('Auth mode of user: ' . $user->getAuthMode() . ' conflicts ' . $msettings->getAuthMode());
            }
        }
    }
    
    /**
     * Extend account
     * @param ilECSSetting $server
     * @param ilObjUser $user
     */
    private function extendAccount(ilECSSetting $settings, ilObjUser $user)
    {
        $end = new ilDateTime(time(), IL_CAL_UNIX);
        $end->increment(IL_CAL_MONTH, $settings->getDuration());
        
        $this->logger->info(__METHOD__ . ': account extension ' . (string) $end);
        
        if ($user->getTimeLimitUntil() < $end->get(IL_CAL_UNIX)) {
            $user->setTimeLimitUntil($end->get(IL_CAL_UNIX));
            $user->update();
        }
    }
    
    /**
     * Update enrolment status
     * @param type $a_obj_id
     * @param ilObjUser $user
     * @param type $a_status
     * @return boolean
     */
    private function updateEnrolmentStatus($a_obj_id, ilObjUser $user, $a_status)
    {
        $remote = (new ilECSRemoteUserRepository())->getECSRemoteUserByUsrId($user->getId());
        if (!$remote instanceof ilECSRemoteUser) {
            return false;
        }
        
        $enrol = new ilECSEnrolmentStatus();
        $enrol->setId('il_' . $this->settings->get('inst_id', 0) . '_' . ilObject::_lookupType($a_obj_id) . '_' . $a_obj_id);
        $enrol->setPersonId($remote->getRemoteUserId());
        $enrol->setPersonIdType(ilECSEnrolmentStatus::ID_UID);
        $enrol->setStatus($a_status);
        
        try {
            $con = new ilECSEnrolmentStatusConnector(ilECSSetting::getInstanceByServerId(1));
            $con->addEnrolmentStatus($enrol, $remote->getMid());
        } catch (ilECSConnectorException $e) {
            $this->logger->info(__METHOD__ . ': update enrolment status faild with message: ' . $e->getMessage());
            return false;
        }
    }
}
