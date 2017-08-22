<?php
/**
 * Class ilListUser
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListUsers {
    

    public static function getData(array $arr_usr_ids = array(), array $options = array()) {
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

        $select = 'SELECT
				   usr_data.usr_id,
				   usr_data.time_limit_owner,
				   usr_data.login,
	               firstname,
	               lastname,
	               title,
	               institution,
	               department,
	               email,
	               phone_office,
	               phone_mobile,
	               active
	               FROM `usr_data`'.

            self::createWhereStatement($arr_usr_ids,$options['filters']);


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
        $user_data = array();

        while($user = $ilDB->fetchAssoc($result)){
            $list_user = new ilMStListUser();
            $list_user->setUsrId($user['usr_id']);
            $list_user->setActive($user['active']);
            $list_user->setTimeLimitOwner($user['time_limit_owner']);
            $list_user->setLogin($user['login']);
            $list_user->setFirstname($user['firstname']);
            $list_user->setLastname($user['lastname']);
            $list_user->setEmail($user['email']);
            $list_user->setPhone($user['phone_office']);
            $list_user->setMobilePhone($user['phone_mobile']);
            $list_user->setAssingedOrgus($user['department']);

            $user_data[] = $list_user;
        }

        return $user_data;
    }

    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids and local variable $filters
     *
     * @param array $arr_usr_ids
     * @param array $arr_filter
     * @return bool|string
     */
    private static function createWhereStatement($arr_usr_ids,$arr_filter){
        global $ilDB;

        $where = array();

        $where[] = $ilDB->in('usr_data.usr_id',$arr_usr_ids,false,'integer');

        if(!empty($arr_filter['user'])){

            $where[] = "(".$ilDB->like("usr_data.login", "text", "%".$arr_filter['user']."%")." ".
                "OR ".$ilDB->like("usr_data.firstname", "text", "%".$arr_filter['user']."%")." ".
                "OR ".$ilDB->like("usr_data.lastname", "text", "%".$arr_filter['user']."%")." ".
                "OR ".$ilDB->like("usr_data.email", "text", "%".$arr_filter['user']."%").") ";


        }


        if(!empty($arr_filter['org_unit'])) {
            ilObjOrgUnitTree::_getInstance()->buildTempTableWithUsrAssignements('orgu_usr_assignements');
            $where[] = 'usr_data.usr_id in (SELECT user_id from orgu_usr_assignements where ref_id = '.$ilDB->quote($arr_filter['org_unit'],'integer').')';
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