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
 * Class ilForumUtil
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumUtil
{
    public static function getPublicUserAlias(string $user_alias, bool $is_anonymized): string
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
    ): void {
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
    ): void {
        $mediaObjects = ilRTE::_getMediaObjects($post_message, $direction);

        foreach ($mediaObjects as $mob) {
            ilObjMediaObject::_saveUsage($mob, $target_type, $target_id);
        }
    }
}
