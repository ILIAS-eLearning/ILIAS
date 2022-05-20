<?php
declare(strict_types=1);

namespace ILIAS\MyStaff\ListUsers;

use ILIAS\DI\Container;
use ILIAS\Services\MyStaff\Utils\ListFetcherResult;

/**
 * Class ilListUser
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListUsers
{
    private Container $dic;

    /**
     * ilMStListUsers constructor.
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    final public function getData(array $arr_usr_ids = array(), array $options = array()) : ListFetcherResult
    {
        //Permissions
        if (count($arr_usr_ids) == 0) {
            return new ListFetcherResult([], 0);
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
	               FROM ' . $this->dic->database()->quoteIdentifier('usr_data') .

            self::createWhereStatement($arr_usr_ids, $options['filters']);

        $result = $this->dic->database()->query($select);
        $numRows = $this->dic->database()->numRows($result);

        if ($options['sort']) {
            $select .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }

        $result = $this->dic->database()->query($select);
        $user_data = array();

        while ($user = $this->dic->database()->fetchAssoc($result)) {
            $list_user = new ilMStListUser();
            $list_user->setUsrId(intval($user['usr_id']));
            $list_user->setGender(intval($user['gender']));
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
            $list_user->setActive(intval($user['active']));
            $list_user->setTimeLimitOwner(intval($user['time_limit_owner']));
            $list_user->setLogin($user['login']);
            $list_user->setFirstname($user['firstname']);
            $list_user->setLastname($user['lastname']);
            $list_user->setEmail($user['email']);
            $list_user->setPhone($user['phone_office']);
            $list_user->setMobilePhone($user['phone_mobile']);

            $user_data[] = $list_user;
        }

        return new ListFetcherResult($user_data, $numRows);
    }

    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids AND local variable $filters
     */
    private function createWhereStatement(array $arr_usr_ids, array $arr_filter) : string
    {
        $where = array();

        $where[] = $this->dic->database()->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');

        if (!empty($arr_filter['user'])) {
            $where[] = "(" . $this->dic->database()->like(
                "usr_data.login",
                "text",
                "%" . $arr_filter['user'] . "%"
            ) . " " . "OR " . $this->dic->database()
                                                                               ->like(
                                                                                   "usr_data.firstname",
                                                                                   "text",
                                                                                   "%" . $arr_filter['user'] . "%"
                                                                               ) . " " . "OR " . $this->dic->database()
                                                                                                                                              ->like(
                                                                                                                                                  "usr_data.lastname",
                                                                                                                                                  "text",
                                                                                                                                                  "%" . $arr_filter['user'] . "%"
                                                                                                                                              ) . " " . "OR " . $this->dic->database()
                                                                                                                                                                                                             ->like(
                                                                                                                                                                                                                 "usr_data.email",
                                                                                                                                                                                                                 "text",
                                                                                                                                                                                                                 "%" . $arr_filter['user'] . "%"
                                                                                                                                                                                                             ) . ") ";
        }

        if (!empty($arr_filter['org_unit'])) {
            $where[] = 'usr_data.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' . $this->dic->database()
                                                                                                         ->quote(
                                                                                                             $arr_filter['org_unit'],
                                                                                                             'integer'
                                                                                                         ) . ')';
        }

        if (!empty($arr_filter['lastname'])) {
            $where[] = '(lastname LIKE ' . $this->dic->database()->quote('%' . str_replace(
                '*',
                '%',
                $arr_filter['lastname']
            ) . '%', 'text') . ')';
        }

        if (!empty($arr_filter['firstname'])) {
            $where[] = '(firstname LIKE ' . $this->dic->database()->quote('%' . str_replace(
                '*',
                '%',
                $arr_filter['firstname']
            ) . '%', 'text') . ')';
        }

        if (!empty($arr_filter['email'])) {
            $where[] = '(email LIKE ' . $this->dic->database()->quote('%' . str_replace(
                '*',
                '%',
                $arr_filter['email']
            ) . '%', 'text') . ')';
        }

        if (!empty($arr_filter['title'])) {
            $where[] = '(title LIKE ' . $this->dic->database()->quote('%' . str_replace(
                '*',
                '%',
                $arr_filter['title']
            ) . '%', 'text') . ')';
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
