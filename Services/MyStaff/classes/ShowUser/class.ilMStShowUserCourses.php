<?php
/**
 * Class ilMStShowUserCourses
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilMStShowUserCourses extends ilMStListCourses {
    

    public static function getData(array $options = array()) {

        return parent::getData($options);
    }

    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids and local variable $filters
     *
     * @param array $arr_filter
     * @return bool|string
     */
    public static function createWhereStatement($arr_filter) {
        global $ilDB;

        if(!$arr_filter['usr_id']) {
            //TODO
           exit;
        }

        $where = parent::createWhereStatement($arr_filter);

        $usr_filter = "usr.usr_id = ".$ilDB->quote($arr_filter['usr_id'],'integer');

        if(!empty($where)){
            return 'WHERE '.$usr_filter;
        }else{
            return ' AND '.$usr_filter;
        }

    }
}