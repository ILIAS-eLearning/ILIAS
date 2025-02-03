<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
