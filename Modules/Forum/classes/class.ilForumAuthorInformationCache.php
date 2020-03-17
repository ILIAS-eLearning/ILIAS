<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilForumAuthorInformationCache
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumAuthorInformationCache
{
    /**
     * @var array
     * @static
     */
    protected static $user_instances = array();

    /**
     * @var array
     * @static
     */
    protected static $requested_usr_ids = array();

    /**
     * @var array
     * @static
     */
    protected static $requested_usr_ids_key_map = array();

    /**
     * @static
     * @param array $usr_ids
     */
    public static function preloadUserObjects(array $usr_ids)
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
                array('text', 'text', 'text'),
                array('public_profile', 'public_gender', 'public_upload')
            );

            while ($row = $ilDB->fetchAssoc($res)) {
                $user = new ilObjUser;
                $user->setId($row['usr_id']);
                $user->setLogin($row['login']);
                $user->setGender($row['gender']);
                $user->setTitle($row['title']);
                $user->setFirstname($row['firstname']);
                $user->setLastname($row['lastname']);
                $user->create_date = $row['create_date']; // create_date is currently a public member, has to be changed in future evtl.
                $user->setPref('public_profile', $row['public_profile']);
                $user->setPref('public_gender', $row['public_gender']);
                $user->setPref('public_upload', $row['public_upload']);

                self::$user_instances[$row['usr_id']] = $user;
            }
        }
    }

    /**
     * @static
     * @param int $usr_id
     * @return ilObjUser|null
     */
    public static function getUserObjectById($usr_id)
    {
        if (!$usr_id) {
            return null;
        }

        if (!array_key_exists($usr_id, self::$requested_usr_ids_key_map)) {
            self::preloadUserObjects(array($usr_id));
        }

        return isset(self::$user_instances[$usr_id]) ? self::$user_instances[$usr_id] : null;
    }
}
