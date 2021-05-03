<?php

namespace OrgUnit\User;

use ilOrgUnitPosition;
use ilOrgUnitUserAssignment;

/**
 * Class ilOrgUnitUserRepository
 *
 * @author: Martin Studer   <ms@studer-raimann.ch>
 */
class ilOrgUnitUserRepository
{

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;
    /**
     * @var self[]
     */
    protected static $instance;
    /**
     * @var ilOrgUnitUser[]
     */
    protected $orgu_users;
    /**
     * @var bool
     */
    protected $with_superiors = false;
    /**
     * @var bool
     */
    protected $with_positions = false;


    /**
     * ilOrgUnitUserRepository constructor.
     *
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }


    /**
     * @return ilOrgUnitUserRepository
     */
    public function withSuperiors() : ilOrgUnitUserRepository
    {
        $this->with_superiors = true;

        return $this;
    }


    /**
     * @return ilOrgUnitUserRepository
     */
    public function withPositions() : ilOrgUnitUserRepository
    {
        $this->with_positions = true;

        return $this;
    }


    /**
     * @param array $arr_user_id
     *
     * @return array
     */
    public function getOrgUnitUsers(array $arr_user_id) : array
    {
        $this->orgu_users = $this->loadUsersByUserIds($arr_user_id);

        if ($this->with_superiors === true) {
            $this->loadSuperiors($arr_user_id);
        }

        if ($this->with_positions === true) {
            $this->loadPositions($arr_user_id);
        }

        return $this->orgu_users;
    }


    /**
     * @param int $user_id
     *
     * @return ilOrgUnitUser|null
     */
    public function getOrgUnitUser(int $user_id) : ?ilOrgUnitUser
    {
        $this->orgu_users = $this->loadUsersByUserIds([$user_id]);

        if (count($this->orgu_users) == 0) {
            return null;
        }

        if ($this->with_superiors === true) {
            $this->loadSuperiors([$user_id]);
        }

        return $this->orgu_users[0];
    }


    /**
     * @param array $user_ids
     */
    public function loadSuperiors(array $user_ids) : void
    {
        global $DIC;

        $st = $DIC->database()->query($this->getSuperiorsSql($user_ids));

        $empl_id_sup_ids = [];
        while ($data = $DIC->database()->fetchAssoc($st)) {
            $org_unit_user = ilOrgUnitUser::getInstanceById($data['empl_usr_id']);
            $superior = ilOrgUnitUser::getInstance($data['sup_usr_id'], (string)$data['sup_login'], (string)$data['sup_email'], (string)$data['sup_second_email']);
            $org_unit_user->addSuperior($superior);
        }
    }


    /**
     * @param array $user_ids
     *
     * @return array
     */
    public function getEmailAdressesOfSuperiors(array $user_ids) : array
    {
        global $DIC;

        $st = $DIC->database()->query($this->getSuperiorsSql($user_ids));

        $arr_email_sup = [];
        while ($data = $DIC->database()->fetchAssoc($st)) {
            $arr_email_sup[] = $data['sup_email'];
        }

        return $arr_email_sup;
    }


    /**
     * @param array $user_ids
     *
     * @return string
     */
    protected function getSuperiorsSql(array $user_ids) : string
    {
        global $DIC;

        $sql = "SELECT 
				orgu_ua.orgu_id AS orgu_id,
				orgu_ua.user_id AS empl_usr_id,
				orgu_ua2.user_id as sup_usr_id,
				superior.email as sup_email,
				superior.second_email as sup_second_email,
				superior.login as sup_login
				FROM
				il_orgu_ua as orgu_ua,
				il_orgu_ua as orgu_ua2
				inner join usr_data as superior on superior.usr_id = orgu_ua2.user_id
				WHERE
				orgu_ua.orgu_id = orgu_ua2.orgu_id 
				and orgu_ua.user_id <> orgu_ua2.user_id 
				and orgu_ua.position_id = " . \ilOrgUnitPosition::CORE_POSITION_EMPLOYEE . "
				and orgu_ua2.position_id = " . \ilOrgUnitPosition::CORE_POSITION_SUPERIOR . " 
				AND " . $DIC->database()->in('orgu_ua.user_id', $user_ids, false, 'integer');

        return $sql;
    }


    /**
     * @param array $user_ids
     *
     * @return array
     */
    public function loadPositions(array $user_ids) : array
    {
        /**
         * @var ilOrgUnitUserAssignment $assignment
         */
        $positions = [];

        $assignments = ilOrgUnitUserAssignment::where(['user_id' => $user_ids])->get();
        if (count($assignments) > 0) {
            foreach ($assignments as $assignment) {
                $org_unit_user = ilOrgUnitUser::getInstanceById($assignment->getUserId());
                $org_unit_user->addPositions(ilOrgUnitPosition::find($assignment->getPositionId()));
            }
        }

        return $positions;
    }


    /**
     * @param $user_ids
     *
     * @return array
     */
    private function loadUsersByUserIds(array $user_ids) : array
    {
        $users = array();

        $q = "SELECT * FROM usr_data WHERE " . $this->dic->database()->in('usr_id', $user_ids, false, 'int');

        $set = $this->dic->database()->query($q);

        while ($row = $this->dic->database()->fetchAssoc($set)) {
            $users[] = ilOrgUnitUser::getInstance($row['usr_id'], (string)$row['login'], (string)$row['email'], (string)$row['second_email']);
        }

        return $users;
    }
}
