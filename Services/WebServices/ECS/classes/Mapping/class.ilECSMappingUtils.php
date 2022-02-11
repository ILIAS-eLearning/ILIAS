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
 * Mapping utils
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSMappingUtils
{
    const MAPPED_WHOLE_TREE = 1;
    const MAPPED_MANUAL = 2;
    const MAPPED_UNMAPPED = 3;
    
    const PARALLEL_ONE_COURSE = 0;
    const PARALLEL_GROUPS_IN_COURSE = 1;
    const PARALLEL_ALL_COURSES = 2;
    const PARALLEL_COURSES_FOR_LECTURERS = 3;

    /**
     * Lookup mapping status
     * @param int $a_server_id
     * @param int $a_tree_id
     * @return int
     */
    public static function lookupMappingStatus($a_server_id, $a_mid, $a_tree_id)
    {
        if (ilECSNodeMappingAssignments::hasAssignments($a_server_id, $a_mid, $a_tree_id)) {
            if (ilECSNodeMappingAssignments::isWholeTreeMapped($a_server_id, $a_mid, $a_tree_id)) {
                return self::MAPPED_WHOLE_TREE;
            }
            return self::MAPPED_MANUAL;
        }
        return self::MAPPED_UNMAPPED;
    }
    
    /**
     * Get mapping status as string
     * @param int $a_status
     */
    public static function mappingStatusToString($a_status)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt('ecs_node_mapping_status_' . $a_status);
    }
    
    
    public static function getCourseMappingFieldInfo()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $field_info = array();
        $counter = 0;
        foreach (
            array(
                'organisation',
                'orgunit',
                'term',
                'title',
                'lecturer',
                'courseType',
                'degreeProgramme',
                'module',
                'venue'
                ) as $field) {
            $field_info[$counter]['name'] = $field;
            $field_info[$counter]['translation'] = $lng->txt('ecs_cmap_att_' . $field);
            $counter++;
        }
        return $field_info;
    }
    
    public static function getCourseMappingFieldSelectOptions()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $options[''] = $lng->txt('select_one');
        foreach (self::getCourseMappingFieldInfo() as $info) {
            $options[$info['name']] = $info['translation'];
        }
        return $options;
    }
    
    /**
     * Get course value by mapping
     * @param type $course
     * @param type $a_field
     * @return array
     */
    public static function getCourseValueByMappingAttribute($course, $a_field)
    {
        switch ($a_field) {
            case 'organisation':
                return array((string) $course->organisation);
                
            case 'term':
                return array((string) $course->term);
                
            case 'title':
                return array((string) $course->title);
                
            case 'orgunit':
                $units = array();
                foreach ((array) $course->organisationalUnits as $unit) {
                    $units[] = (string) $unit->title;
                }
                return $units;
                
            case 'lecturer':
                $lecturers = array();
                foreach ((array) $course->groups as $group) {
                    foreach ((array) $group->lecturers as $lecturer) {
                        $lecturers[] = (string) ($lecturer->lastName . ', ' . $lecturer->firstName);
                    }
                }
                return $lecturers;
                
            case 'courseType':
                return array((string) $course->lectureType);
                
            case 'degreeProgramme':
                $degree_programmes = array();
                foreach ((array) $course->degreeProgrammes as $prog) {
                    $degree_programmes[] = (string) $prog->title;
                }
                return $degree_programmes;
                
            case 'module':
                $modules = array();
                foreach ((array) $course->modules as $mod) {
                    $modules[] = (string) $mod->title;
                }
                return $modules;
                
            case 'venue':
                $venues[] = array();
                foreach ((array) $course->groups as $group) {
                    foreach ((array) $group->datesAndVenues as $venue) {
                        $venues[] = (string) $venue->venue;
                    }
                }
                return $venues;
        }
        return array();
    }
    
    
    /**
     * Get role mapping info
     */
    public static function getRoleMappingInfo($a_role_type_info = 0)
    {
        //KEEP!!! until the defines are turned into proper constants
        include_once './Services/Membership/classes/class.ilParticipants.php';
        $roles = array(
            ilParticipants::IL_CRS_ADMIN => array(
                'role' => ilParticipants::IL_CRS_ADMIN,
                'lang' => 'il_crs_admin',
                'create' => true,
                'required' => true,
                'type' => 'crs'),
            ilParticipants::IL_CRS_TUTOR => array(
                'role' => ilParticipants::IL_CRS_TUTOR,
                'lang' => 'il_crs_tutor',
                'create' => true,
                'required' => false,
                'type' => 'crs'),
            ilParticipants::IL_CRS_MEMBER => array(
                'role' => ilParticipants::IL_CRS_MEMBER,
                'lang' => 'il_crs_member',
                'create' => false,
                'required' => true,
                'type' => 'crs'),
            ilParticipants::IL_GRP_ADMIN => array(
                'role' => ilParticipants::IL_GRP_ADMIN,
                'lang' => 'il_grp_admin',
                'create' => true,
                'required' => false,
                'type' => 'grp'),
            ilParticipants::IL_GRP_MEMBER => array(
                'role' => ilParticipants::IL_GRP_MEMBER,
                'lang' => 'il_grp_member',
                'create' => false,
                'required' => false,
                'type' => 'grp')
        );
        if (!$a_role_type_info) {
            return $roles;
        } else {
            return $roles[$a_role_type_info];
        }
    }
    
    /**
     * Get auth mode selection
     * @return array
     */
    public static function getAuthModeSelection()
    {
        global $DIC;

        $lng = $DIC->language();
        $ilSetting = $DIC->settings();


        $options[0] = $lng->txt('select_one');
        $options['local'] = $lng->txt('auth_local');

        foreach (ilLDAPServer::getServerIds() as $sid) {
            $server = ilLDAPServer::getInstanceByServerId($sid);
            $options['ldap_' . $server->getServerId()] = 'LDAP (' . $server->getName() . ')';
        }

        if ($ilSetting->get('shib_active', "0")) {
            $options[ilAuthUtils::_getAuthModeName(ilAuthUtils::AUTH_SHIBBOLETH)] =
                $lng->txt('auth_shibboleth');
        }

        return $options;
    }
}
