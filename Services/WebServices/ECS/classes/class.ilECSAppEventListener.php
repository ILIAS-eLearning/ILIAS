<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * ECS Event Handler
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilECSAppEventListener implements ilAppEventListener
{
    private ilLogger $logger;
    private ilSetting $settings;
    private ilRbacAdmin $rbac_admin;

    public function __construct(
        \ilLogger $logger,
        ilSetting $settings,
        ilRbacAdmin $rbac_admin
    ) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->rbac_admin = $rbac_admin;
    }

    /**
    * Handle an event in a listener.
    * @param	string $a_component component, e.g. "Modules/Forum" or "Services/User"
    * @param	string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array  $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;

        $eventHandler = new static(
            $DIC->logger()->wsrv(),
            $DIC->settings(),
            $DIC->rbac()->admin(),
        );
        $eventHandler->handle($a_component, $a_event, $a_parameter);
    }

    private function handle($a_component, $a_event, $a_parameter): void
    {
        $this->logger->debug('Listening to event from: ' . $a_component . ' ' . $a_event);

        switch ($a_component) {
            case 'Services/Authentication':
                switch ($a_event) {
                    case 'afterLogin':
                        $this->handleNewAccountCreation((string) $a_parameter['username']);
                        break;
                }
                break;

            case 'Services/User':
                if ($a_event === 'afterCreate') {
                    $user = $a_parameter['user_obj'];
                    $this->handleMembership($user);
                    $this->handleNewAccountCreation((string) $user->getLogin());
                }
                break;

            case 'Modules/Group':

                $this->logger->debug('New event from group: ' . $a_event);
                switch ($a_event) {
                    case 'addSubscriber':
                    case 'addToWaitingList':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) === 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }

                            $settings = $this->initServer($a_parameter['usr_id']);
                            /** @var ilObjUser $user */
                            $this->extendAccount($settings, $user);

                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_PENDING);
                        }
                        break;

                    case 'deleteParticipant':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) === 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            /** @var ilObjUser $user */
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED);
                        }
                        break;

                    case 'addParticipant':
                        if ((ilObjUser::_lookupAuthMode($a_parameter['usr_id']) === 'ecs')) {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }

                            $settings = $this->initServer($user->getId());
                            /** @var ilObjUser $user */
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
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) === 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }

                            $settings = $this->initServer($a_parameter['usr_id']);
                            /** @var ilObjUser $user */
                            $this->extendAccount($settings, $user);

                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_PENDING);
                        }
                        break;


                    case 'deleteParticipant':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) === 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }
                            /** @var ilObjUser $user */
                            $this->updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED);
                        }
                        break;

                    case 'addParticipant':

                        if ((ilObjUser::_lookupAuthMode($a_parameter['usr_id']) === 'ecs')) {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $this->logger->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return;
                            }

                            $settings = $this->initServer($user->getId());
                            /** @var ilObjUser $user */
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
     */
    private function initServer(int $a_usr_id): ilECSSetting
    {
        $server_id = ilECSImportManager::getInstance()->lookupServerId($a_usr_id);

        return ilECSSetting::getInstanceByServerId($server_id);
    }

    /**
     * send notification about new user accounts
     */
    private function sendNotification(ilECSSetting $server, ilObjUser $user_obj): void
    {
        if (!count($server->getUserRecipients())) {
            return;
        }
        // If sub id is set => mail was send
        $import = new ilECSImport($server->getServerId(), $user_obj->getId());
        if ($import->getSubId()) {
            return;
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
    }

    protected function handleNewAccountCreation(string $username): void
    {
        $user_id = ilObjUser::_loginExists($username);
        if (!$user_id) {
            $this->logger->warning('Invalid username given: ' . $username);
        }

        $external_account = ilObjUser::_lookupExternalAccount($user_id);
        if ($external_account == '') {
            return;
        }
        $remote_user_repo = new ilECSRemoteUserRepository();
        $remote_user = $remote_user_repo->getECSRemoteUserByRemoteId($external_account);
        if ($remote_user === null) {
            return;
        }
        if ($remote_user->getServerId() === 0) {
            $this->logger->warning('Found remote user without server id: ' . $external_account);
            return;
        }
        if ($remote_user->getMid() === 0) {
            $this->logger->warning('Found remote user without mid id: ' . $external_account);
            return;
        }
        $part = ilECSParticipantSetting::getInstance(
            $remote_user->getServerId(),
            $remote_user->getMid()
        );
        $server = ilECSSetting::getInstanceByServerId($remote_user->getServerId());
        if (
            $part->getIncomingAuthType() === ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE ||
            $part->getIncomingAuthType() === ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH
        ) {
            $this->logger->info('Assigning ' . $username . ' to global ecs role');
            $this->rbac_admin->assignUser($server->getGlobalRole(), $user_id);
        }
    }

    /**
     * Assign missing course/groups to new user accounts
     */
    private function handleMembership(ilObjUser $user): void
    {
        $this->logger->debug('Handling ECS assignments ');

        $assignments = ilECSCourseMemberAssignment::lookupMissingAssignmentsOfUser($user->getExternalAccount());
        foreach ($assignments as $assignment) {
            $msettings = ilECSNodeMappingSettings::getInstanceByServerMid(
                $assignment->getServer(),
                $assignment->getMid()
            );
            if ($user->getAuthMode() === $msettings->getAuthMode()) {
                $this->logger->info('Adding user ' . $assignment->getUid() . ' to course/group: ' . $assignment->getObjId());
                $obj_type = ilObject::_lookupType($assignment->getObjId());
                if ($obj_type !== 'crs' && $obj_type !== 'grp') {
                    $this->logger->error('Invalid assignment type: ' . $obj_type);
                    $this->logger->logStack(ilLogLevel::ERROR);
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
                    $this->logger->error('Invalid ref_id given: ' . (int) $ref_id);
                    $this->logger->logStack(ilLogLevel::ERROR);
                    continue;
                }
            } else {
                $this->logger->notice('Auth mode of user: ' . $user->getAuthMode() . ' conflicts ' . $msettings->getAuthMode());
            }
        }
    }

    /**
     * Extend account
     */
    private function extendAccount(ilECSSetting $settings, ilObjUser $user): void
    {
        $end = new ilDateTime(time(), IL_CAL_UNIX);
        $end->increment(IL_CAL_MONTH, $settings->getDuration());

        $this->logger->info(__METHOD__ . ': account extension ' . $end);

        if ($user->getTimeLimitUntil() < $end->get(IL_CAL_UNIX)) {
            $user->setTimeLimitUntil($end->get(IL_CAL_UNIX));
            $user->update();
        }
    }

    /**
     * Update enrolment status
     */
    private function updateEnrolmentStatus(int $a_obj_id, ilObjUser $user, string $a_status): void
    {
        $remote = (new ilECSRemoteUserRepository())->getECSRemoteUserByUsrId($user->getId());
        if (!$remote instanceof ilECSRemoteUser) {
            return;
        }

        $enrol = new ilECSEnrolmentStatus();
        $enrol->setId('il_' . $this->settings->get('inst_id', "0") . '_' . ilObject::_lookupType($a_obj_id) . '_' . $a_obj_id);
        $enrol->setPersonId($remote->getRemoteUserId());
        $enrol->setPersonIdType(ilECSEnrolmentStatus::ID_UID);
        $enrol->setStatus($a_status);

        try {
            $con = new ilECSEnrolmentStatusConnector(ilECSSetting::getInstanceByServerId(1));
            $con->addEnrolmentStatus($enrol, $remote->getMid());
        } catch (ilECSConnectorException $e) {
            $this->logger->info(__METHOD__ . ': update enrolment status faild with message: ' . $e->getMessage());
        }
    }
}
