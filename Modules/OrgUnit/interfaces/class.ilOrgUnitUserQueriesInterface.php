<?php
/**
 * Class ilOrgUnitUserQueriesInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilOrgUnitUserQueriesInterface
{

    /**
     * @param int[] $user_ids
     */
    public function findAllUsersByUserIds(array $user_ids): array;

    /**
     * @param string[] $users
     */
    public function getAllUserNames(array $users): array;
}
