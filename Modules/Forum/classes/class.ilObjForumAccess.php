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
 * Class ilObjForumAccess
 * @author  Alex Killing <alex.killing@gmx.de>
 * @ingroup ModulesForum
 */
class ilObjForumAccess extends ilObjectAccess
{
    /** @var array<int, ilObjUser|null> */
    protected static array $userInstanceCache = [];

    public static function _getCommands(): array
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

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $t_arr = explode('_', $target);

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

    public static function _getThreadForPosting(int $a_pos_id): int
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

    public static function prepareMessageForLists(string $text): string
    {
        $text = str_replace('<br />', ' ', $text);
        $text = strip_tags($text);
        $text = preg_replace('/\[(\/)?quote\]/', '', $text);
        if (ilStr::strLen($text) > 40) {
            $text = ilStr::subStr($text, 0, 37) . '...';
        }

        return $text;
    }

    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
        /*
        We are only able to preload the top_pk values for the forum ref_ids.
        Other data like statistics and last posts require permission checks per reference, so there is no added value for using an SQL IN() function in the queries
        */
        ilObjForum::preloadForumIdsByRefIds($ref_ids);
    }

    public static function getLastPostByRefId(int $ref_id): ?array
    {
        return ilObjForum::lookupLastPostByRefId($ref_id);
    }

    /**
     * @param int $ref_id
     * @return array{num_posts: int, num_unread_posts: int, num_new_posts: int}
     */
    public static function getStatisticsByRefId(int $ref_id): array
    {
        return ilObjForum::lookupStatisticsByRefId($ref_id);
    }

    public static function getCachedUserInstance(int $usr_id): ?ilObjUser
    {
        if (!isset(self::$userInstanceCache[$usr_id]) && ilObjUser::userExists([$usr_id])) {
            $user = ilObjectFactory::getInstanceByObjId($usr_id, false);
            if ($user instanceof ilObjUser) {
                self::$userInstanceCache[$usr_id] = $user;
            }
        }

        return self::$userInstanceCache[$usr_id] ?? null;
    }
}
