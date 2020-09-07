<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once('./Services/EventHandling/interfaces/interface.ilAppEventListener.php');

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
    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $log = $GLOBALS['DIC']->logger()->wsrv();
        
        $log->debug('Listening to event from: ' . $a_component . ' ' . $a_event);
        
        switch ($a_component) {
            case 'Services/User':
                switch ($a_event) {
                    case 'afterCreate':
                        $user = $a_parameter['user_obj'];
                        self::handleMembership($user);
                        break;
                }
                break;
            
            case 'Modules/Group':

                $log->debug('New event from group: ' . $a_event);
                switch ($a_event) {
                    case 'addSubscriber':
                    case 'addToWaitingList':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $log->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return true;
                            }
                            
                            $settings = self::initServer($a_parameter['usr_id']);
                            self::extendAccount($settings, $user);
                            
                            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                            self::updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_PENDING);
                        }
                        break;
                        
                    case 'deleteParticipant':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $log->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return true;
                            }
                            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                            self::updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED);
                        }
                        break;
                        
                    case 'addParticipant':
                        if ((ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs')) {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $log->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return true;
                            }
                            
                            $settings = self::initServer($user->getId());
                            
                            self::extendAccount($settings, $user);
                            #self::_sendNotification($settings,$user);
                            
                            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                            self::updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_ACTIVE);
                            unset($user);
                        }
                        break;
                        
                        
                
                }
                break;
                
            case 'Modules/Course':
                
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': New event from course: ' . $a_event);
                switch ($a_event) {

                    case 'addSubscriber':
                    case 'addToWaitingList':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $log->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return true;
                            }
                            
                            $settings = self::initServer($a_parameter['usr_id']);
                            self::extendAccount($settings, $user);
                            
                            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                            self::updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_PENDING);
                        }
                        break;
                        
                
                    case 'deleteParticipant':
                        if (ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs') {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $log->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return true;
                            }
                            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                            self::updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED);
                        }
                        break;
                        
                    case 'addParticipant':
                        
                        if ((ilObjUser::_lookupAuthMode($a_parameter['usr_id']) == 'ecs')) {
                            if (!$user = ilObjectFactory::getInstanceByObjId($a_parameter['usr_id'])) {
                                $log->info('No valid user found for usr_id ' . $a_parameter['usr_id']);
                                return true;
                            }
                            
                            $settings = self::initServer($user->getId());
                            
                            self::extendAccount($settings, $user);
                            self::_sendNotification($settings, $user);
                            
                            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                            self::updateEnrolmentStatus($a_parameter['obj_id'], $user, ilECSEnrolmentStatus::STATUS_ACTIVE);
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
    protected static function initServer($a_usr_id)
    {
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $server_id = ilECSImport::lookupServerId($a_usr_id);

        include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
        $settings = ilECSSetting::getInstanceByServerId($server_id);
        
        return $settings;
    }
    
    /**
     * send notification about new user accounts
     *
     * @access protected
     */
    protected static function _sendNotification(ilECSSetting $server, ilObjUser $user_obj)
    {
        if (!count($server->getUserRecipients())) {
            return true;
        }
        // If sub id is set => mail was send
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $import = new ilECSImport($server->getServerId(), $user_obj->getId());
        if ($import->getSubId()) {
            return false;
        }

        include_once('./Services/Language/classes/class.ilLanguageFactory.php');
        $lang = ilLanguageFactory::_getLanguage();
        $lang->loadLanguageModule('ecs');

        include_once('./Services/Mail/classes/class.ilMail.php');
        $mail = new ilMail(6);
        $mail->enableSoap(false);
        $subject = $lang->txt('ecs_new_user_subject');

        // build body
        $body = $lang->txt('ecs_new_user_body') . "\n\n";
        $body .= $lang->txt('ecs_new_user_profile') . "\n\n";
        $body .= $user_obj->getProfileAsString($lang) . "\n\n";
        $body .= ilMail::_getAutoGeneratedMessageString($lang);
        
        $mail->sendMail($server->getUserRecipientsAsString(), "", "", $subject, $body, array(), array("normal"));
        
        // Store sub_id = 1 in ecs import which means mail is send
        $import->setSubId(1);
        $import->save();
        
        return true;
    }
    
    /**
     * Assign missing course/groups to new user accounts
     * @param ilObjUser $user
     */
    protected static function handleMembership(ilObjUser $user)
    {
        $log = $GLOBALS['DIC']->logger()->wsrv();
        $log->debug('Handling ECS assignments ');
        
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberAssignment.php';
        $assignments = ilECSCourseMemberAssignment::lookupMissingAssignmentsOfUser($user->getExternalAccount());
        foreach ($assignments as $assignment) {
            include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
            $msettings = ilECSNodeMappingSettings::getInstanceByServerMid(
                $assignment->getServer(),
                $assignment->getMid()
            );
            if ($user->getAuthMode() == $msettings->getAuthMode()) {
                $log->info('Adding user ' . $assignment->getUid() . ' to course/group: ' . $assignment->getObjId());
                include_once './Services/Membership/classes/class.ilParticipants.php';
                
                if (
                    ilObject::_lookupType($assignment->getObjId()) == 'crs' ||
                    ilObject::_lookupType($assignment->getObjId()) == 'grp'
                ) {
                    include_once './Modules/Course/classes/class.ilCourseConstants.php';
                    $part = ilParticipants::getInstanceByObjId($assignment->getObjId());
                    $part->add($user->getId(), ilCourseConstants::CRS_MEMBER);
                }
            } else {
                $log->notice('Auth mode of user: ' . $user->getAuthMode() . ' conflicts ' . $msettings->getAuthMode());
            }
        }
    }
    
    /**
     * Extend account
     * @param ilECSSetting $server
     * @param ilObjUser $user
     */
    protected static function extendAccount(ilECSSetting $settings, ilObjUser $user)
    {
        $end = new ilDateTime(time(), IL_CAL_UNIX);
        $end->increment(IL_CAL_MONTH, $settings->getDuration());
        
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': account extension ' . (string) $end);
        
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
    protected static function updateEnrolmentStatus($a_obj_id, ilObjUser $user, $a_status)
    {
        include_once './Services/WebServices/ECS/classes/class.ilECSRemoteUser.php';
        $remote = ilECSRemoteUser::factory($user->getId());
        if (!$remote instanceof ilECSRemoteUser) {
            return false;
        }
        
        include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
        $enrol = new ilECSEnrolmentStatus();
        $enrol->setId('il_' . $GLOBALS['DIC']['ilSetting']->get('inst_id', 0) . '_' . ilObject::_lookupType($a_obj_id) . '_' . $a_obj_id);
        $enrol->setPersonId($remote->getRemoteUserId());
        $enrol->setPersonIdType(ilECSEnrolmentStatus::ID_UID);
        $enrol->setStatus($a_status);
        
        try {
            include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatusConnector.php';
            $con = new ilECSEnrolmentStatusConnector(ilECSSetting::getInstanceByServerId(1));
            $con->addEnrolmentStatus($enrol, $remote->getMid());
        } catch (ilECSConnectorException $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': update enrolment status faild with message: ' . $e->getMessage());
            return false;
        }
    }
}
