<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjForumAccess
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilObjForumAccess extends ilObjectAccess
{
    /** @var array<int, ilObjUser> */
    protected static array $userInstanceCache = [];

    public static function _getCommands() : array
    {
        return [
            [
                'permission' => 'read',
                'cmd' => 'showThreads',
                'lang_var' => 'show',
                'default' => true
            ],
            [
                'permission' => 'write',
                'cmd' => 'edit',
                'lang_var' => 'settings'
            ],
        ];
    }

    public static function _checkGoto($a_target) : bool
    {
        global $DIC;

        $t_arr = explode('_', $a_target);

        if ($t_arr[0] !== 'frm' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if (
            $DIC->access()->checkAccess('read', '', (int) $t_arr[1]) ||
            $DIC->access()->checkAccess('visible', '', (int) $t_arr[1])
        ) {
            return true;
        }

        return false;
    }

    public static function _getThreadForPosting(int $a_pos_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT pos_thr_fk FROM frm_posts WHERE pos_pk = %s',
            ['integer'],
            [$a_pos_id]
        );

        $row = $ilDB->fetchAssoc($res);

        return (int) $row['pos_thr_fk'];
    }

    public static function prepareMessageForLists(string $text) : string
    {
        $text = str_replace('<br />', ' ', $text);
        $text = strip_tags($text);
        $text = preg_replace('/\[(\/)?quote\]/', '', $text);
        if (ilStr::strLen($text) > 40) {
            $text = ilStr::subStr($text, 0, 37) . '...';
        }

        return $text;
    }

    public static function _preloadData($a_obj_ids, $a_ref_ids) : void
    {
        /*
        We are only able to preload the top_pk values for the forum ref_ids.
        Other data like statistics and last posts require permission checks per reference, so there is no added value for using an SQL IN() function in the queries
        */
        ilObjForum::preloadForumIdsByRefIds((array) $a_ref_ids);
    }

    public static function getLastPostByRefId(int $ref_id) : array
    {
        return ilObjForum::lookupLastPostByRefId($ref_id);
    }

    /**
     * @param int $ref_id
     * @return array{num_posts: int, num_unread_posts: int, num_new_posts: int}
     */
    public static function getStatisticsByRefId(int $ref_id) : array
    {
        return ilObjForum::lookupStatisticsByRefId($ref_id);
    }

    public static function getCachedUserInstance(int $usr_id) : ilObjUser
    {
        if (!isset(self::$userInstanceCache[$usr_id]) && ilObjUser::userExists([$usr_id])) {
            self::$userInstanceCache[$usr_id] = ilObjectFactory::getInstanceByObjId($usr_id, false);
        }

        return self::$userInstanceCache[$usr_id];
    }
}
