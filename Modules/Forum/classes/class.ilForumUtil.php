<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumUtil
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumUtil
{
    public static function getPublicUserAlias(string $user_alias, bool $is_anonymized) : string
    {
        global $DIC;

        $ilUser = $DIC->user();
        $lng = $DIC->language();

        if ($is_anonymized) {
            if ($user_alias === '') {
                $user_alias = $lng->txt('forums_anonymous');
            }

            return $user_alias;
        }

        return $ilUser->getLogin();
    }

    public static function moveMediaObjects(
        string $post_message,
        string $source_type,
        int $source_id,
        string $target_type,
        int $target_id,
        int $direction = 0
    ) : void {
        $mediaObjects = ilRTE::_getMediaObjects($post_message, $direction);
        $myMediaObjects = ilObjMediaObject::_getMobsOfObject($source_type, $source_id);
        foreach ($mediaObjects as $mob) {
            foreach ($myMediaObjects as $myMob) {
                if ($mob === $myMob) {
                    ilObjMediaObject::_removeUsage($mob, $source_type, $source_id);
                    break;
                }
            }
            ilObjMediaObject::_saveUsage($mob, $target_type, $target_id);
        }
    }

    public static function saveMediaObjects(
        string $post_message,
        string $target_type,
        int $target_id,
        int $direction = 0
    ) : void {
        $mediaObjects = ilRTE::_getMediaObjects($post_message, $direction);

        foreach ($mediaObjects as $mob) {
            ilObjMediaObject::_saveUsage($mob, $target_type, $target_id);
        }
    }
}
