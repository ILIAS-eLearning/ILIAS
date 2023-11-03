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

declare(strict_types=1);

/**
 * Access class for chatroom objects.
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 * @ingroup components\ILIASChatroom
 */
class ilObjChatroomAccess extends ilObjectAccess implements ilWACCheckingClass
{
    private static ?bool $chat_enabled = null;

    public static function _getCommands(): array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'enter', 'default' => true];
        $commands[] = ['permission' => 'write', 'cmd' => 'settings-general', 'lang_var' => 'settings'];

        return $commands;
    }

    public static function _checkGoto(string $target): bool
    {
        $t_arr = explode('_', $target);

        if (count($t_arr) < 2 || $t_arr[0] !== 'chtr' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if (ilChatroom::checkUserPermissions('visible', (int) $t_arr[1], false) ||
            ilChatroom::checkUserPermissions('read', (int) $t_arr[1], false)) {
            return true;
        }

        return false;
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        if (!$user_id) {
            $user_id = $GLOBALS['DIC']->user()->getId();
        }

        return self::checkRoomAccess($permission, $ref_id, $obj_id, (int) $user_id);
    }

    private static function checkRoomAccess(string $a_permission, int $a_ref_id, int $a_obj_id, int $a_user_id): bool
    {
        global $DIC;

        if (self::$chat_enabled === null) {
            $chatSetting = new ilSetting('chatroom');
            self::$chat_enabled = (bool) $chatSetting->get('chat_enabled', '0');
        }

        $hasWriteAccess = $DIC->rbac()->system()->checkAccessOfUser($a_user_id, 'write', $a_ref_id);
        if ($hasWriteAccess) {
            return true;
        }

        switch ($a_permission) {
            case 'read':
            case 'visible':
                $is_online = self::lookupOnline($a_obj_id);
                if (!$is_online) {
                    $DIC->access()->addInfoItem(
                        ilAccessInfo::IL_NO_OBJECT_ACCESS,
                        $DIC->language()->txt('offline')
                    );
                    return false;
                }
                break;
        }

        return self::$chat_enabled;
    }

    public static function lookupOnline(int $a_obj_id): bool
    {
        return !ilObject::lookupOfflineStatus($a_obj_id);
    }

    public function canBeDelivered(ilWACPath $ilWACPath): bool
    {
        return false;
    }
}
