<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailUserCache
{
    protected static array $user_instances = array();
    protected static array $requested_usr_ids = array();
    protected static array $requested_usr_ids_key_map = array();

    /**
     * @static
     * @param array $usr_ids
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
				SELECT ud.usr_id, login, firstname, lastname, title, gender, pprof.value public_profile,pup.value public_upload, pupgen.value public_gender
				FROM usr_data ud
				LEFT JOIN usr_pref pprof ON pprof.usr_id = ud.usr_id AND pprof.keyword = %s
				LEFT JOIN usr_pref pupgen ON pupgen.usr_id = ud.usr_id AND pupgen.keyword = %s
				LEFT JOIN usr_pref pup ON pup.usr_id = ud.usr_id AND pup.keyword = %s
				WHERE $in
			";

            $res = $DIC->database()->queryF(
                $query,
                array('text', 'text', 'text'),
                array('public_profile', 'public_gender', 'public_upload')
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

                self::$user_instances[$row['usr_id']] = $user;
            }
        }
    }

    /**
     * @static
     * @param int $usr_id
     * @return ilObjUser|null
     */
    public static function getUserObjectById($usr_id): ?\ilObjUser
    {
        if (!$usr_id) {
            return null;
        }
        
        if (!array_key_exists($usr_id, self::$requested_usr_ids_key_map)) {
            self::preloadUserObjects(array($usr_id));
        }

        return self::$user_instances[$usr_id] ?? null;
    }
}
