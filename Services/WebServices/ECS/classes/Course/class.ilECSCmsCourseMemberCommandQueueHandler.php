<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';

/**
 * Synchronize member assignments
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseMemberCommandQueueHandler implements ilECSCommandQueueHandler
{
    /**
     * @var ilLogger
     */
    protected $log;
    
    private $server = null;
    private $mid = 0;
    
    private $mapping = null;
    
    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server)
    {
        $this->log = $GLOBALS['DIC']->logger()->wsrv();
        $this->server = $server;
    }
    
    /**
     * Get server
     * @return ilECSServerSetting
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * get current mid
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }
    
    
    /**
     * Get mapping settings
     * @return ilECSnodeMappingSettings
     */
    public function getMappingSettings()
    {
        return $this->mapping;
    }
    
    /**
     * Check if course allocation is activated for one recipient of the
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function checkAllocationActivation(ilECSSetting $server, $a_content_id)
    {
        try {
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberConnector.php';
            $crsm_reader = new ilECSCourseMemberConnector($server);
            $details = $crsm_reader->getCourseMember($a_content_id, true);
            $this->mid = $details->getMySender();
            
            // Check if import is enabled
            include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
            $part = ilECSParticipantSetting::getInstance($this->getServer()->getServerId(), $this->getMid());
            if (!$part->isImportEnabled()) {
                $this->log->warning('Import disabled for mid ' . $this->getMid());
                return false;
            }
            // Check course allocation setting
            include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
            $this->mapping = ilECSNodeMappingSettings::getInstanceByServerMid(
                $this->getServer()->getServerId(),
                $this->getMid()
            );
            return $this->getMappingSettings()->isCourseAllocationEnabled();
        } catch (ilECSConnectorException $e) {
            $this->log->error('Reading course member details failed with message ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Handle create
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleCreate(ilECSSetting $server, $a_content_id)
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';

        if (!$this->checkAllocationActivation($server, $a_content_id)) {
            return true;
        }
        try {
            //$course = $this->readCourse($server, $a_content_id);
            $course_member = $this->readCourseMember($server, $a_content_id);
            $this->doUpdate($a_content_id, $course_member);
            return true;
        } catch (ilECSConnectorException $e) {
            $this->log->error('Course member creation failed  with mesage ' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Handle delete
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleDelete(ilECSSetting $server, $a_content_id)
    {
        // nothing todo
        return true;
    }

    /**
     * Handle update
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleUpdate(ilECSSetting $server, $a_content_id)
    {
        if (!$this->checkAllocationActivation($server, $a_content_id)) {
            return true;
        }
        
        try {
            $course_member = $this->readCourseMember($server, $a_content_id);
            $this->doUpdate($a_content_id, $course_member);
            return true;
        } catch (ilECSConnectorException $e) {
            $this->log->error('Course member update failed  with mesage ' . $e->getMessage());
            return false;
        }
        return true;
    }
    
    
    /**
     * Perform update
     * @param type $a_content_id
     */
    protected function doUpdate($a_content_id, $course_member)
    {
        $this->log->debug('Starting ecs  member update');
        
        $course_id = (int) $course_member->lectureID;
        if (!$course_id) {
            $this->log->warning('Missing course id');
            return false;
        }
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $this->log->debug('sid: ' . $this->getServer()->getServerId() . ' course_id: ' . $course_id . ' mid: ' . $this->mid);
        //$crs_obj_id = ilECSImport::_lookupObjId($this->getServer()->getServerId(), $course_id, $this->mid);
        $crs_obj_id = ilECSImport::lookupObjIdByContentId($this->getServer()->getServerId(), $this->mid, $course_id);
        
        if (!$crs_obj_id) {
            $this->log->info('No main course created. Group scenario >= 3 ?');
        }
        
        $course = $this->readCourse($course_member);
        // Lookup already imported users and update their status
        $assignments = $this->readAssignments($course, $course_member);
        
        $this->log->debug('Parallel group assignments');
        $this->log->dump($assignments, ilLogLevel::DEBUG);
        $this->log->debug('------------------ End assignemtns');
        
        // iterate through all parallel groups
        foreach ((array) $assignments as $cms_id => $assigned) {
            $sub_id = ($cms_id == $course_id) ? null : $cms_id;
            
            $this->log->debug('sub id is ' . $sub_id . ' for ' . $cms_id);
            
            include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
            $obj_id = ilECSImport::lookupObjIdByContentId(
                $this->getServer()->getServerId(),
                $this->getMid(),
                $course_id,
                $sub_id
            );
                    
            $this->refreshAssignmentStatus($course_member, $obj_id, $sub_id, $assigned);
        }
        return true;
    }
    
    /**
     * Read assignments for all parallel groups
     * @param type $course
     * @param type $course_member
     */
    protected function readAssignments($course, $course_member)
    {
        $put_in_course = true;
        
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
        switch ((int) $course->groupScenario) {
            case ilECSMappingUtils::PARALLEL_ONE_COURSE:
                $this->log->debug('Parallel group scenario one course.');
                $put_in_course = true;
                break;
                
            case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
                $this->log->debug('Parallel group scenario groups in courses.');
                $put_in_course = false;
                break;
                
            case ilECSMappingUtils::PARALLEL_ALL_COURSES:
                $this->log->debug('Parallel group scenario only courses.');
                $put_in_course = false;
                break;
            
            default:
                $this->log->debug('Parallel group scenario undefined.');
                $put_in_course = true;
                break;
        }
        
        $course_id = $course_member->lectureID;
        $assigned = array();
        foreach ((array) $course_member->members as $member) {
            $assigned[$course_id][$member->personID] = array(
                'id' => $member->personID,
                'role' => $member->role
            );
            if ((int) $course->groupScenario == ilECSMappingUtils::PARALLEL_ONE_COURSE) {
                $this->log->debug('Group scenarion "one course". Ignoring group assignments');
                continue;
            }
            
            foreach ((array) $member->groups as $pgroup) {
                // the sequence number in the course ressource
                $sequence_number = (int) $pgroup->num;
                // find parallel group with by sequence number
                $tmp_pgroup = $course->groups[$sequence_number];
                if (is_object($tmp_pgroup)) {
                    $pgroup_id = $tmp_pgroup->id;
                }
                if (strlen($pgroup_id)) {
                    $this->log->debug('Found parallel group with id: ' . $pgroup_id . ': for sequence number: ' . $sequence_number);
                    
                    // @todo check hierarchy of roles
                    $assigned[$pgroup_id][$member->personID] = array(
                        'id' => $member->personID,
                        'role' => $pgroup->role
                    );
                } else {
                    $this->log->warning('Cannot find parallel group with sequence id: ' . $sequence_number);
                }
            }
        }
        $this->log->debug('ECS member assignments ' . print_r($assigned, true));
        return $assigned;
    }
    
    
    
    /**
     * Refresh status of course member assignments
     * @param object $course_member
     * @param int $obj_id
     */
    protected function refreshAssignmentStatus($course_member, $obj_id, $sub_id, $assigned)
    {
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberAssignment.php';
        
        $this->log->debug('Currrent sub_id = ' . $sub_id . ', obj_id = ' . $obj_id);
        
        $type = ilObject::_lookupType($obj_id);
        if ($type == 'crs') {
            include_once './Modules/Course/classes/class.ilCourseParticipants.php';
            $part = ilCourseParticipants::_getInstanceByObjId($obj_id);
        } elseif ($type == 'grp') {
            include_once './Modules/Group/classes/class.ilGroupParticipants.php';
            $part = ilGroupParticipants::_getInstanceByObjId($obj_id);
        } else {
            $this->log->warning('Invalid object type given for obj_id: ' . $obj_id);
            return false;
        }
        
        $course_id = (int) $course_member->lectureID;
        $usr_ids = ilECSCourseMemberAssignment::lookupUserIds(
            $course_id,
            $sub_id,
            $obj_id
        );
        
        // Delete remote deleted
        foreach ((array) $usr_ids as $usr_id) {
            if (!isset($assigned[$usr_id])) {
                $ass = ilECSCourseMemberAssignment::lookupAssignment($course_id, $sub_id, $obj_id, $usr_id);
                if ($ass instanceof ilECSCourseMemberAssignment) {
                    $login = ilObjUser::_checkExternalAuthAccount(
                        $this->getMappingSettings()->getAuthMode(),
                        (string) $usr_id
                    );
                    
                    $this->log->debug('Local user assignment: ' . (string) $usr_id . ' <-> ' . $login);
                    
                    if ($il_usr_id = ilObjUser::_lookupId($login)) {
                        // this removes also admin, tutor roles
                        $part->delete($il_usr_id);
                        $this->log->info('Deassigning user ' . $usr_id . ' ' . 'from course ' . ilObject::_lookupTitle($obj_id));
                    } else {
                        $this->log->notice('Deassigning unknown ILIAS user ' . $usr_id . ' ' . 'from course ' . ilObject::_lookupTitle($obj_id));
                    }

                    $ass->delete();
                }
            }
        }

        $this->log->debug('Handled assignmnent...');
        
        // Assign new participants
        foreach ((array) $assigned as $person_id => $person) {
            $role = $this->lookupRole($person['role'], $type);
            $role_info = ilECSMappingUtils::getRoleMappingInfo($role);
        
            $this->log->debug('Using role info...');
            $login = ilObjUser::_checkExternalAuthAccount(
                $this->getMappingSettings()->getAuthMode(),
                (string) $person_id
            );
            $this->log->info('Handling user ' . (string) $person_id);
            
            if (in_array($person_id, $usr_ids)) {
                if ($il_usr_id = ilObjUser::_lookupId($login)) {
                    $part->updateRoleAssignments($il_usr_id, array($part->getAutoGeneratedRoleId($role)));
                } elseif ($role_info['create']) {
                    $this->createMember($person_id);
                    $this->log->info('Added new user ' . $person_id);
                    $login = ilObjUser::_checkExternalAuthAccount(
                        $this->getMappingSettings()->getAuthMode(),
                        (string) $person_id
                    );
                    if ($role) {
                        if ($il_usr_id = ilObjUser::_lookupId($login)) {
                            $part->add($il_usr_id, $role);
                            $part->sendNotification($part->NOTIFY_ACCEPT_USER, $il_usr_id);
                        }
                    }
                }
            } else {
                if ($il_usr_id = ilObjUser::_lookupId($login)) {
                    // user exists => assign to course/group
                    if ($role) {
                        // Assign user
                        $this->log->info('Assigning new user ' . $person_id . ' ' . 'to ' . ilObject::_lookupTitle($obj_id) . ' using role: ' . $role);
                        $part->add($il_usr_id, $role);
                        $part->sendNotification($part->NOTIFY_ACCEPT_USER, $il_usr_id);
                    }
                } else {
                    // no local user exists
                    if ($role_info['create']) {
                        $this->createMember($person_id);
                        $this->log->info('Added new user ' . $person_id);
                        $login = ilObjUser::_checkExternalAuthAccount(
                            $this->getMappingSettings()->getAuthMode(),
                            (string) $person_id
                        );
                    }
                    // Assign to role
                    if ($role) {
                        if ($il_usr_id = ilObjUser::_lookupId($login)) {
                            $part->add($il_usr_id, $role);
                            $part->sendNotification($part->NOTIFY_ACCEPT_USER, $il_usr_id);
                        }
                    }
                }
                
                $assignment = new ilECSCourseMemberAssignment();
                $assignment->setServer($this->getServer()->getServerId());
                $assignment->setMid($this->mid);
                $assignment->setCmsId($course_id);
                $assignment->setCmsSubId($sub_id);
                $assignment->setObjId($obj_id);
                $assignment->setUid($person_id);
                $assignment->save();
            }
        }
        return true;
    }
    
    /**
     * Lookup local role by assignment
     * @param string $role_value
     * @return int
     */
    protected function lookupRole($role_value, $a_obj_type)
    {
        $role_mappings = $this->getMappingSettings()->getRoleMappings();
        
        /* Zero is an allowed value */
        if (!$role_value) {
            $this->log->debug('No role assignment attribute: role');
        }
        foreach ($role_mappings as $name => $map) {
            $this->log->debug('Role "name" is ' . $name);
            
            // map is a string of ids seperated by ","
            $exploded_map = (array) explode(',', $map);
            if (in_array($role_value, $exploded_map)) {
                switch ($name) {
                    case IL_CRS_ADMIN:
                    case IL_CRS_TUTOR:
                    case IL_CRS_MEMBER:
                        if ($a_obj_type == 'crs') {
                            $this->log->debug('Role: ' . $role_value . ' maps: ' . $exploded_map);
                            return $name;
                        }
                        break;
                        
                    case IL_GRP_ADMIN:
                    case IL_GRP_MEMBER:
                        if ($a_obj_type == 'grp') {
                            $this->log->debug('Role: ' . $role_value . ' maps: ' . $exploded_map);
                            return $name;
                        }
                        break;
                }
            }
        }
        $this->log->info('No role assignment mapping for role ' . $role_value);
        return 0;
    }
    
    /**
     * Create user account
     * @param type $a_person_id
     */
    private function createMember($a_person_id)
    {
        if (!$this->getMappingSettings() instanceof ilECSNodeMappingSettings) {
            $this->log->warning('Node mapping settings not initialized.');
        }
        $auth_mode = $this->getMappingSettings()->getAuthMode();

        if (
            $this->getMappingSettings()->getAuthMode() ==
            ilAuthUtils::_getAuthModeName(AUTH_SHIBBOLETH)
        ) {
            $this->log->info('Not handling direct user creation for auth mode: ' . $auth_mode);
            return false;
        }
        if (substr($auth_mode, 0, 4) !== 'ldap') {
            $this->log->info('Not handling direct user creation for auth mode: ' . $auth_mode);
            return false;
        }

        try {
            include_once './Services/LDAP/classes/class.ilLDAPServer.php';
            $server = ilLDAPServer::getInstanceByServerId(ilLDAPServer::_getFirstActiveServer());
            $server->doConnectionCheck();

            include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
            $query = new ilLDAPQuery($server);
            $query->bind(IL_LDAP_BIND_DEFAULT);
            
            $users = $query->fetchUser($a_person_id, true);
            if ($users) {
                include_once './Services/User/classes/class.ilUserCreationContext.php';
                ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

                include_once './Services/LDAP/classes/class.ilLDAPAttributeToUser.php';
                $xml = new ilLDAPAttributeToUser($server);
                $xml->setNewUserAuthMode($server->getAuthenticationMappingKey());
                $xml->setUserData($users);
                $xml->refresh();
            }
        } catch (ilLDAPQueryException $exc) {
            $this->log->error($exc->getMessage());
        }
    }
    

    /**
     * Read course from ecs
     * @return boolean
     */
    private function readCourseMember(ilECSSetting $server, $a_content_id)
    {
        try {
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMemberConnector.php';
            $crs_member_reader = new ilECSCourseMemberConnector($server);
            
            $member = $crs_member_reader->getCourseMember($a_content_id);
            return $member;
        } catch (ilECSConnectorException $e) {
            throw $e;
        }
    }
    
    /**
     * Read course from ecs
     * @return boolean
     */
    private function readCourse($course_member)
    {
        try {
            include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
            $ecs_id = ilECSImport::lookupEContentIdByContentId(
                $this->getServer()->getServerId(),
                $this->getMid(),
                $course_member->lectureID
            );
            
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';
            $crs_reader = new ilECSCourseConnector($this->getServer());
            return $crs_reader->getCourse($ecs_id);
        } catch (ilECSConnectorException $e) {
            throw $e;
        }
    }
}
