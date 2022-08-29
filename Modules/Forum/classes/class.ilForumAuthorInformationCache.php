<?php

declare(strict_types=1);

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
 * ilForumAuthorInformationCache
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumAuthorInformationCache
{
    /** @var ilObjUser[] */
    protected static array $user_instances = [];
    /** @var int[]  */
    protected static array $requested_usr_ids = [];
    /** @var array<int, int>  */
    protected static array $requested_usr_ids_key_map = [];

    /**
     * @param int[] $usr_ids
     */
    public static function preloadUserObjects(array $usr_ids): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $usr_ids_to_request = array_diff($usr_ids, self::$requested_usr_ids);
        self::$requested_usr_ids = array_merge(self::$requested_usr_ids, $usr_ids_to_request);
        self::$requested_usr_ids_key_map = array_flip(self::$requested_usr_ids);

        if ($usr_ids_to_request) {
            $in = $ilDB->in('ud.usr_id', $usr_ids_to_request, false, 'integer');
            $query = "
				SELECT ud.usr_id, od.create_date, login, firstname, lastname, ud.title, gender, pprof.value public_profile, pgen.value public_gender, pup.value public_upload
				FROM usr_data ud
				INNER JOIN object_data od ON od.obj_id = ud.usr_id
				LEFT JOIN usr_pref pprof ON pprof.usr_id = ud.usr_id AND pprof.keyword = %s
				LEFT JOIN usr_pref pgen ON pgen.usr_id = ud.usr_id AND pgen.keyword = %s
				LEFT JOIN usr_pref pup ON pup.usr_id = ud.usr_id AND pup.keyword = %s
				WHERE $in
			";

            $res = $ilDB->queryF(
                $query,
                ['text', 'text', 'text'],
                ['public_profile', 'public_gender', 'public_upload']
            );

            while ($row = $ilDB->fetchAssoc($res)) {
                $user = new ilObjUser();
                $user->setId((int) $row['usr_id']);
                $user->setLogin($row['login']);
                $user->setGender($row['gender']);
                $user->setTitle($row['title']);
                $user->setFirstname($row['firstname']);
                $user->setLastname($row['lastname']);
                $user->setPref('public_profile', $row['public_profile']);
                $user->setPref('public_gender', $row['public_gender']);
                $user->setPref('public_upload', $row['public_upload']);

                self::$user_instances[(int) $row['usr_id']] = $user;
            }
        }
    }

    public static function getUserObjectById(int $usr_id): ?ilObjUser
    {
        if (!$usr_id) {
            return null;
        }

        if (!isset(self::$requested_usr_ids_key_map[$usr_id])) {
            self::preloadUserObjects([$usr_id]);
        }

        return self::$user_instances[$usr_id] ?? null;
    }
}
