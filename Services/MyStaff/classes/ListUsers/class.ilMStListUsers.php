<?php

/**
 * Class ilListUser
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListUsers
{

    /**
     * @param array $arr_usr_ids
     * @param array $options
     *
     * @return array|int
     */
    public static function getData(array $arr_usr_ids = array(), array $options = array())
    {
        global $DIC;

        //Permissions
        if (count($arr_usr_ids) == 0) {
            if ($options['count']) {
                return 0;
            } else {
                return array();
            }
        }

        $_options = array(
            'filters' => array(),
            'sort' => array(),
            'limit' => array(),
            'count' => false,
        );
        $options = array_merge($_options, $options);

        $select = 'SELECT
				   usr_id,
				   time_limit_owner,
				   login,
				   gender,
	               firstname,
	               lastname,
	               title,
	               institution,
	               department,
	               street,
	               zipcode,
	               city,
	               country,
	               sel_country,
	               hobby,
	               email,
	               matriculation,
	               phone_office,
	               phone_mobile,
	               active
	               FROM ' . $DIC->database()->quoteIdentifier('usr_data') .

            self::createWhereStatement($arr_usr_ids, $options['filters']);

        if ($options['count']) {
            $result = $DIC->database()->query($select);

            return $DIC->database()->numRows($result);
        }

        if ($options['sort']) {
            $select .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }

        $result = $DIC->database()->query($select);
        $user_data = array();

        while ($user = $DIC->database()->fetchAssoc($result)) {
            $list_user = new ilMStListUser();
            $list_user->setUsrId($user['usr_id']);
            $list_user->setGender($user['gender']);
            $list_user->setTitle($user['title']);
            $list_user->setInstitution($user['institution']);
            $list_user->setDepartment($user['department']);
            $list_user->setStreet($user['street']);
            $list_user->setZipcode($user['zipcode']);
            $list_user->setCity($user['city']);
            $list_user->setCountry($user['country']);
            $list_user->setSelCountry($user['sel_country']);
            $list_user->setHobby($user['hobby']);
            $list_user->setMatriculation($user['matriculation']);
            $list_user->setActive($user['active']);
            $list_user->setTimeLimitOwner($user['time_limit_owner']);
            $list_user->setLogin($user['login']);
            $list_user->setFirstname($user['firstname']);
            $list_user->setLastname($user['lastname']);
            $list_user->setEmail($user['email']);
            $list_user->setPhone($user['phone_office']);
            $list_user->setMobilePhone($user['phone_mobile']);

            $user_data[] = $list_user;
        }

        return $user_data;
    }


    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids AND local variable $filters
     *
     * @param array $arr_usr_ids
     * @param array $arr_filter
     *
     * @return string
     */
    private static function createWhereStatement(array $arr_usr_ids, array $arr_filter)
    {
        global $DIC;

        $where = array();

        $where[] = $DIC->database()->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');

        if (!empty($arr_filter['user'])) {
            $where[] = "(" . $DIC->database()->like("usr_data.login", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
                    ->like("usr_data.firstname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
                    ->like("usr_data.lastname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
                    ->like("usr_data.email", "text", "%" . $arr_filter['user'] . "%") . ") ";
        }

        if (!empty($arr_filter['org_unit'])) {
            $where[] = 'usr_data.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' . $DIC->database()
                    ->quote($arr_filter['org_unit'], 'integer') . ')';
        }

        if (!empty($arr_filter['lastname'])) {
            $where[] = '(lastname LIKE ' . $DIC->database()->quote('%' . str_replace('*', '%', $arr_filter['lastname']) . '%', 'text') . ')';
        }

        if (!empty($arr_filter['firstname'])) {
            $where[] = '(firstname LIKE ' . $DIC->database()->quote('%' . str_replace('*', '%', $arr_filter['firstname']) . '%', 'text') . ')';
        }

        if (!empty($arr_filter['email'])) {
            $where[] = '(email LIKE ' . $DIC->database()->quote('%' . str_replace('*', '%', $arr_filter['email']) . '%', 'text') . ')';
        }

        if (!empty($arr_filter['title'])) {
            $where[] = '(title LIKE ' . $DIC->database()->quote('%' . str_replace('*', '%', $arr_filter['title']) . '%', 'text') . ')';
        }

        if ($arr_filter['activation']) {
            if ($arr_filter['activation'] == 'active') {
                $where[] = '(active = "1")';
            }
            if ($arr_filter['activation'] == 'inactive') {
                $where[] = '(active = "0")';
            }
        }

        if (!empty($where)) {
            return ' WHERE ' . implode(' AND ', $where) . ' ';
        } else {
            return '';
        }
    }
}
