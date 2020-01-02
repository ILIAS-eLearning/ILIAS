<?php
/**
 * Class ilOrgUnitUserQueriesInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilOrgUnitUserQueriesInterface
{

    /**
     * @param array $user_ids
     *
     * @return array $users
     */
    public function findAllUsersByUserIds($user_ids);


    /**
     * @param array $users
     *
     * @return array $user_names
     */
    public function getAllUserNames($users);
}
