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
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailUserCache
{
    /** @var array<int, ilObjUser|null> */
    protected static array $user_instances = [];
    /** @var int[] */
    protected static array $requested_usr_ids = [];
    /** @var array<int, int> */
    protected static array $requested_usr_ids_key_map = [];

    /**
     * @param int[] $usr_ids
     */
    public static function preloadUserObjects(array $usr_ids): void
    {
        global $DIC;

        $usr_ids_to_request = array_diff($usr_ids, self::$requested_usr_ids);
        self::$requested_usr_ids = array_merge(self::$requested_usr_ids, $usr_ids_to_request);
        self::$requested_usr_ids_key_map = array_flip(self::$requested_usr_ids);

        if ($usr_ids_to_request) {
            $in = $DIC->database()->in('ud.usr_id', $usr_ids_to_request, false, 'integer');
            $query = "
				SELECT ud.usr_id, login, firstname, lastname, title, gender, 
				       pprof.value public_profile,pup.value public_upload, pupgen.value public_gender
				FROM usr_data ud
				LEFT JOIN usr_pref pprof ON pprof.usr_id = ud.usr_id AND pprof.keyword = %s
				LEFT JOIN usr_pref pupgen ON pupgen.usr_id = ud.usr_id AND pupgen.keyword = %s
				LEFT JOIN usr_pref pup ON pup.usr_id = ud.usr_id AND pup.keyword = %s
				WHERE $in
			";

            $res = $DIC->database()->queryF(
                $query,
                ['text', 'text', 'text'],
                ['public_profile', 'public_gender', 'public_upload']
            );

            while ($row = $DIC->database()->fetchAssoc($res)) {
                $user = new ilObjUser();
                $user->setId((int) $row['usr_id']);
                $user->setLogin((string) $row['login']);
                $user->setGender((string) $row['gender']);
                $user->setTitle((string) $row['title']);
                $user->setFirstname((string) $row['firstname']);
                $user->setLastname((string) $row['lastname']);
                $user->setPref('public_profile', $row['public_profile']);
                $user->setPref('public_upload', $row['public_upload']);
                $user->setPref('public_gender', $row['public_gender']);

                self::$user_instances[(int) $row['usr_id']] = $user;
            }
        }
    }

    public static function getUserObjectById(int $usr_id): ?ilObjUser
    {
        if ($usr_id < 1) {
            return null;
        }

        if (!array_key_exists($usr_id, self::$requested_usr_ids_key_map)) {
            self::preloadUserObjects([$usr_id]);
        }

        return self::$user_instances[$usr_id] ?? null;
    }
}
