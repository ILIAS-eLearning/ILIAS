<?php

declare(strict_types=1);

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
 * Synchronize member assignments
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseMemberCommandQueueHandler implements ilECSCommandQueueHandler
{
    protected ilLogger $log;

    private ilECSSetting $server;
    private int $mid = 0;

    private ?ilECSnodeMappingSettings $mapping = null;

    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server)
    {
        global $DIC;
        $this->log = $DIC->logger()->wsrv();
        $this->server = $server;
    }

    /**
     * Get server
     */
    public function getServer(): \ilECSSetting
    {
        return $this->server;
    }

    /**
     * get current mid
     */
    public function getMid(): int
    {
        return $this->mid;
    }

    /**
     * Check if course allocation is activated for one recipient of the
     */
    public function checkAllocationActivation(ilECSSetting $server, $a_content_id): bool
    {
        try {
            $crsm_reader = new ilECSCourseMemberConnector($server);
            $details = $crsm_reader->getCourseMember($a_content_id, true);
            $this->mid = $details->getMySender();

            // Check if import is enabled
            $part = ilECSParticipantSetting::getInstance($this->getServer()->getServerId(), $this->getMid());
            if (!$part->isImportEnabled()) {
                $this->log->warning('Import disabled for mid ' . $this->getMid());
                return false;
            }
            // Check course allocation setting
            $this->mapping = ilECSNodeMappingSettings::getInstanceByServerMid(
                $this->getServer()->getServerId(),
                $this->getMid()
            );
            return $this->mapping->isCourseAllocationEnabled();
        } catch (ilECSConnectorException $e) {
            $this->log->error('Reading course member details failed with message ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Handle create
     */
    public function handleCreate(ilECSSetting $server, $a_content_id): bool
    {
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
     */
    public function handleDelete(ilECSSetting $server, $a_content_id): bool
    {
        // nothing todo
        return true;
    }

    /**
     * Handle update
     */
    public function handleUpdate(ilECSSetting $server, $a_content_id): bool
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
     */
    protected function doUpdate($a_content_id, $course_member): bool
    {
        $this->log->debug('Starting ecs  member update');

        $course_id = (int) $course_member->lectureID;
        if (!$course_id) {
            $this->log->warning('Missing course id');
            return false;
        }
        $this->log->debug('sid: ' . $this->getServer()->getServerId() . ' course_id: ' . $course_id . ' mid: ' . $this->mid);
        //$crs_obj_id = ilECSImportManager::getInstance()->_lookupObjId($this->getServer()->getServerId(), $course_id, $this->mid);
        $crs_obj_id = ilECSImportManager::getInstance()->lookupObjIdByContentId($this->getServer()->getServerId(), $this->mid, $course_id);

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
        foreach ($assignments as $cms_id => $assigned) {
            $sub_id = ($cms_id === $course_id) ? null : $cms_id;

            $this->log->debug('sub id is ' . $sub_id . ' for ' . $cms_id);

            $obj_id = ilECSImportManager::getInstance()->lookupObjIdByContentId(
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
     */
    protected function readAssignments($course, $course_member): array
    {
        //TODO check if this switch is still needed
        switch ((int) $course->groupScenario) {
            case ilECSMappingUtils::PARALLEL_ONE_COURSE:
                $this->log->debug('Parallel group scenario one course.');
                break;

            case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
                $this->log->debug('Parallel group scenario groups in courses.');
                break;

            case ilECSMappingUtils::PARALLEL_ALL_COURSES:
                $this->log->debug('Parallel group scenario only courses.');
                break;

            default:
                $this->log->debug('Parallel group scenario undefined.');
                break;
        }

        $course_id = $course_member->lectureID;
        $assigned = array();
        foreach ((array) $course_member->members as $member) {
            $assigned[$course_id][$member->personID] = array(
                'id' => $member->personID,
                'role' => $member->role
            );
            if ((int) $course->groupScenario === ilECSMappingUtils::PARALLEL_ONE_COURSE) {
                $this->log->debug('Group scenarion "one course". Ignoring group assignments');
                continue;
            }

            foreach ((array) $member->groups as $pgroup) {
                // the sequence number in the course ressource
                $sequence_number = (int) $pgroup->num;
                // find parallel group with by sequence number
                $tmp_pgroup = $course->groups[$sequence_number];
                if (is_object($tmp_pgroup) && $tmp_pgroup->id !== '') {
                    $this->log->debug('Found parallel group with id: ' . $tmp_pgroup->id . ': for sequence number: ' . $sequence_number);

                    // @todo check hierarchy of roles
                    $assigned[$tmp_pgroup->id][$member->personID] = array(
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
     */
    protected function refreshAssignmentStatus(object $course_member, int $obj_id, $sub_id, $assigned): bool
    {
        $this->log->debug('Currrent sub_id = ' . $sub_id . ', obj_id = ' . $obj_id);

        $type = ilObject::_lookupType($obj_id);
        if ($type === 'crs') {
            $part = ilCourseParticipants::_getInstanceByObjId($obj_id);
        } elseif ($type === 'grp') {
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
        foreach ($usr_ids as $usr_id) {
            if (!isset($assigned[$usr_id])) {
                $ass = ilECSCourseMemberAssignment::lookupAssignment($course_id, $sub_id, $obj_id, $usr_id);
                if ($ass instanceof ilECSCourseMemberAssignment) {
                    $login = ilObjUser::_checkExternalAuthAccount(
                        $this->mapping->getAuthMode(),
                        (string) $usr_id
                    );

                    $this->log->debug('Local user assignment: ' . $usr_id . ' <-> ' . $login);

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
                $this->mapping->getAuthMode(),
                (string) $person_id
            );
            $this->log->info('Handling user ' . $person_id);

            if (in_array($person_id, $usr_ids, true)) {
                if ($il_usr_id = ilObjUser::_lookupId($login)) {
                    $part->updateRoleAssignments($il_usr_id, array($part->getAutoGeneratedRoleId($role)));
                } elseif ($role_info['create']) {
                    $this->createMember($person_id);
                    $this->log->info('Added new user ' . $person_id);
                    $login = ilObjUser::_checkExternalAuthAccount(
                        $this->mapping->getAuthMode(),
                        (string) $person_id
                    );
                    if ($role && $il_usr_id = ilObjUser::_lookupId($login)) {
                        $part->add($il_usr_id, $role);
                        $part->sendNotification(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER, $il_usr_id);
                    }
                }
            } else {
                if ($il_usr_id = ilObjUser::_lookupId($login)) {
                    // user exists => assign to course/group
                    if ($role) {
                        // Assign user
                        $this->log->info('Assigning new user ' . $person_id . ' ' . 'to ' . ilObject::_lookupTitle($obj_id) . ' using role: ' . $role);
                        $part->add($il_usr_id, $role);
                        $part->sendNotification(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER, $il_usr_id);
                    }
                } else {
                    // no local user exists
                    if ($role_info['create']) {
                        $this->createMember($person_id);
                        $this->log->info('Added new user ' . $person_id);
                        $login = ilObjUser::_checkExternalAuthAccount(
                            $this->mapping->getAuthMode(),
                            (string) $person_id
                        );
                    }
                    // Assign to role
                    if ($role && $il_usr_id = ilObjUser::_lookupId($login)) {
                        $part->add($il_usr_id, $role);
                        $part->sendNotification(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER, $il_usr_id);
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
     */
    protected function lookupRole(string $role_value, $a_obj_type): int
    {
        $role_mappings = $this->mapping->getRoleMappings();

        /* Zero is an allowed value */
        if (!$role_value) {
            $this->log->debug('No role assignment attribute: role');
        }
        foreach ($role_mappings as $name => $map) {
            $this->log->debug('Role "name" is ' . $name);

            // map is a string of ids seperated by ","
            $exploded_map = (array) explode(',', $map);
            if (in_array($role_value, $exploded_map, true)) {
                switch ($name) {
                    case ilParticipants::IL_CRS_ADMIN:
                    case ilParticipants::IL_CRS_TUTOR:
                    case ilParticipants::IL_CRS_MEMBER:
                        if ($a_obj_type === 'crs') {
                            $this->log->debug('Role: ' . $role_value . ' maps: ' . $map);
                            return $name;
                        }
                        break;

                    case ilParticipants::IL_GRP_ADMIN:
                    case ilParticipants::IL_GRP_MEMBER:
                        if ($a_obj_type === 'grp') {
                            $this->log->debug('Role: ' . $role_value . ' maps: ' . $map);
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
     */
    private function createMember($a_person_id): void
    {
        if (!$this->mapping instanceof ilECSNodeMappingSettings) {
            $this->log->warning('Node mapping settings not initialized.');
        }
        $auth_mode = $this->mapping->getAuthMode();

        if (
            $this->mapping->getAuthMode() ===
            ilAuthUtils::_getAuthModeName(ilAuthUtils::AUTH_SHIBBOLETH)
        ) {
            $this->log->info('Not handling direct user creation for auth mode: ' . $auth_mode);
            return;
        }
        if (strpos($auth_mode, 'ldap') !== 0) {
            $this->log->info('Not handling direct user creation for auth mode: ' . $auth_mode);
            return;
        }

        try {
            $server = ilLDAPServer::getInstanceByServerId(ilLDAPServer::_getFirstActiveServer());
            $server->doConnectionCheck();

            $query = new ilLDAPQuery($server);
            $query->bind(ilLDAPQuery::LDAP_BIND_DEFAULT);

            $users = $query->fetchUser($a_person_id);
            if ($users) {
                ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

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
     */
    private function readCourseMember(ilECSSetting $server, $a_content_id)
    {
        return (new ilECSCourseMemberConnector($server))->getCourseMember($a_content_id);
    }

    /**
     * Read course from ecs
     */
    private function readCourse($course_member)
    {
        $ecs_id = ilECSImportManager::getInstance()->lookupEContentIdByContentId(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $course_member->lectureID
        );

        return (new ilECSCourseConnector($this->getServer()))->getCourse($ecs_id);
    }
}
