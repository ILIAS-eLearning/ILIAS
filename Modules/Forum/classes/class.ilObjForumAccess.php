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
    protected static array $userInstanceCache = [];

    /**
     * get commands
     * this method returns an array of all possible commands/permission combinations
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
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

    /**
     * Check whether goto script will succeed
     */
    public static function _checkGoto($a_target) : bool
    {
        global $DIC;

        $t_arr = explode('_', $a_target);

        if ($t_arr[0] != 'frm' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if (
            $DIC->access()->checkAccess('read', '', $t_arr[1]) ||
            $DIC->access()->checkAccess('visible', '', $t_arr[1])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get thread id for posting
     */
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

        return $row['pos_thr_fk'];
    }

    /**
     * Returns the number of bytes used on the harddisk by the specified forum
     */
    public static function _lookupDiskUsage(int $a_obj_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryf(
            'SELECT top_frm_fk, pos_pk FROM frm_posts p
			JOIN frm_data d ON d.top_pk = p.pos_top_fk
			WHERE top_frm_fk = %s',
            ['integer'],
            [$a_obj_id]
        );

        $size = 0;
        while ($row = $ilDB->fetchAssoc($res)) {
            $fileDataForum = new ilFileDataForum($row['top_frm_fk'], $row['pos_pk']);
            $filesOfPost = $fileDataForum->getFilesOfPost();
            foreach ($filesOfPost as $attachment) {
                $size += $attachment['size'];
            }
            unset($fileDataForum);
            unset($filesOfPost);
        }

        return $size;
    }

    /**
     * Prepare message for container view
     */
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

    public static function _preloadData($obj_ids, $ref_ids)
    {
        /*
        We are only able to preload the top_pk values for the forum ref_ids.
        Other data like statistics and last posts require permission checks per reference, so there is no added value for using an SQL IN() function in the queries
        */
        ilObjForum::preloadForumIdsByRefIds((array) $ref_ids);
    }

    public static function getLastPostByRefId(int $ref_id) : array
    {
        return ilObjForum::lookupLastPostByRefId($ref_id);
    }

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
