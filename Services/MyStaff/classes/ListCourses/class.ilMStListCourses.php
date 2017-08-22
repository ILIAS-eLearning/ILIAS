<?php
/**
 * Class ilMStListCourses
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCourses {
    

    public static function getData(array $arr_usr_ids = array(),array $options = array()) {
        global $ilDB;

        //Permissions
        if(count($arr_usr_ids) == 0) {
            return false;
        }

        $_options = array(
            'filters' => array(),
            'sort' => array(),
            'limit' => array(),
            'count' => false,
        );
        $options = array_merge($_options, $options);

        $udf = ilUserDefinedFields::_getInstance();

        $select = 'SELECT crs.title as crs_title, reg_status, lp_status, usr_data.login as usr_login, usr_data.lastname as usr_lastname, usr_data.firstname as usr_firstname, usr_data.email as usr_email  from (
	                    select reg.obj_id, reg.usr_id, 2 as reg_status, lp.status as lp_status from obj_members as reg
                        left join ut_lp_marks as lp on lp.obj_id = reg.obj_id and lp.usr_id = reg.usr_id
		                UNION
	                    select obj_id, usr_id, 1 as reg_status, 0 as lp_status from crs_waiting_list as waiting) as memb
                    inner join object_data as crs on crs.obj_id = memb.obj_id and crs.type = "crs"
	                inner join usr_data on usr_data.usr_id = memb.usr_id and usr_data.active = 1';

        $select .= static::createWhereStatement($arr_usr_ids,$options['filters']);

        if ($options['count']) {
            $result = $ilDB->query($select);
            return $ilDB->numRows($result);
        }

        if($options['sort']) {
            $select .= " ORDER BY ".$options['sort']['field'] ." ".$options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $select .= " LIMIT ".$options['limit']['start'].",".$options['limit']['end'];
        }

        $result = $ilDB->query($select);
        $crs_data = array();

        while($crs = $ilDB->fetchAssoc($result)){
            $list_course = new ilMStListCourse();
            $list_course->setCrsTitle($crs['crs_title']);
            $list_course->setUsrRegStatus($crs['reg_status']);
            $list_course->setUsrLpStatus($crs['lp_status']);
            $list_course->setUsrLogin($crs['usr_login']);
            $list_course->setUsrLastname($crs['usr_lastname']);
            $list_course->setUsrFirstname($crs['usr_firstname']);
            $list_course->setUsrEmail($crs['usr_email']);
            $list_course->setUsrAssingedOrgus('');

            $crs_data[] = $list_course;
        }

        return $crs_data;
    }

    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids and local variable $filters
     *
     * @param array $arr_usr_ids
     * @param array $arr_filter
     * @return bool|string
     */
    public static function createWhereStatement($arr_usr_ids,$arr_filter){
        global $ilDB;

        $where = array();

        $where[] = $ilDB->in('usr_data.usr_id',$arr_usr_ids,false,'integer');

        if((!empty($arr_filter['time_limit_owner']) && $arr_filter['time_limit_owner'] != 0)) {
            $where[] = '(usr_data.time_limit_owner = ' . $ilDB->quote($arr_filter['time_limit_owner'],'integer') . ')';
        }

        if(!empty($arr_filter['org_unit'])) {

            $user_ids_orgs = array_merge(ilObjOrgUnitTree::_getInstance()->getEmployees($arr_filter['org_unit'], $arr_filter['org_unit_recursive']), ilObjOrgUnitTree::_getInstance()->getSuperiors($arr_filter['org_unit'], $arr_filter['org_unit_recursive']));
            if(empty($user_ids_orgs)) {
                $user_ids_orgs = array(0 => -1);
            }
            $where[] = '(usr_data.usr_id = ' . implode(' OR usr_data.usr_id = ', $user_ids_orgs) . ')';
        }

        if(!empty($arr_filter['lastname'])){
            $where[] = '(lastname LIKE ' . $ilDB->quote('%'
                    . str_replace('*', '%', $arr_filter['lastname']) . '%', 'text').')';
        }

        if(!empty($arr_filter['firstname'])){
            $where[] = '(firstname LIKE ' . $ilDB->quote('%'
                    . str_replace('*', '%', $arr_filter['firstname']) . '%', 'text').')';
        }

        if(!empty($arr_filter['email'])){
            $where[] = '(email LIKE ' . $ilDB->quote('%'
                    . str_replace('*', '%', $arr_filter['email']) . '%', 'text').')';
        }

        if(!empty($arr_filter['title'])){
            $where[] = '(title LIKE ' . $ilDB->quote('%'
                    . str_replace('*', '%', $arr_filter['title']) . '%', 'text').')';
        }

        if($arr_filter['activation']) {
            if($arr_filter['activation'] == 'active') {
                $where[] = '(active = "1")';
            }
            if($arr_filter['activation'] == 'inactive') {
                $where[] = '(active = "0")';
            }

        }

        if(!empty($where)){
            return ' WHERE ' . implode(' AND ', $where) . ' ';
        }else{
            return '';
        }
    }
}