<?php
/**
 * Class ilOrgUnitUserQueries
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilOrgUnitUserQueries implements ilOrgUnitUserQueriesInterface
{
    protected \ILIAS\DI\Container $dic;

    /**
     * ilOrgUnitUserQueries constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }

    /**
     * @param int[] $user_ids
     */
    public function findAllUsersByUserIds(array $user_ids): array
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
     * @return string[]
     */
    public function getAllUserNames(array $users): array
    {
        $user_names = array();
        foreach ($users as $user) {
            $user_names[] = $user['login'];
        }

        return $user_names;
    }
}
