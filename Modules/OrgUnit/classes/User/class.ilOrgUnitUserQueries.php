<?php
/**
 * Class ilOrgUnitUserQueries
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilOrgUnitUserQueries implements ilOrgUnitUserQueriesInterface
{

    /**
     * @var \ILIAS\DI\Container $dic;
     */
    protected $dic;

    /**
     * ilOrgUnitUserQueries constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }


    /**
     * @inheritdoc
     */
    public function findAllUsersByUserIds($user_ids)
    {
        $users = array();
        foreach ($user_ids as $user_id) {
            $q = "SELECT * FROM usr_data WHERE usr_id = " . $this->dic->database()->quote($user_id, "integer");
            $usr_set = $this->dic->database()->query($q);
            $users[] = $this->dic->database()->fetchAssoc($usr_set);
        }
        return $users;
    }


    /**
     * @inheritdoc
     */
    public function getAllUserNames($users)
    {
        $user_names = array();
        foreach ($users as $user) {
            $user_names[] = $user['login'];
        }
        return $user_names;
    }
}
