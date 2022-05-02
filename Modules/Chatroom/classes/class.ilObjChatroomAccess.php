<?php declare(strict_types=1);

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
 * Access class for chatroom objects.
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAccess extends ilObjectAccess implements ilWACCheckingClass
{
    private static ?bool $chat_enabled = null;

    public static function _getCommands() : array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'enter', 'default' => true];
        $commands[] = ['permission' => 'write', 'cmd' => 'settings-general', 'lang_var' => 'settings'];

        return $commands;
    }

    public static function _checkGoto(string $target) : bool
    {
        $t_arr = explode('_', $target);

        if (count($t_arr) < 2 || $t_arr[0] !== 'chtr' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if (
            ilChatroom::checkUserPermissions('visible', (int) $t_arr[1], false) ||
            ilChatroom::checkUserPermissions('read', (int) $t_arr[1], false)
        ) {
            return true;
        }

        return false;
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
    {
        if (!$user_id) {
            $user_id = $GLOBALS['DIC']->user()->getId();
        }

        return self::checkRoomAccess($permission, $ref_id, $obj_id, (int) $user_id);
    }

    private static function checkRoomAccess(string $a_permission, int $a_ref_id, int $a_obj_id, int $a_user_id) : bool
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
            case 'visible':
                $visible = null;

                $active = self::isActivated($a_ref_id, $a_obj_id, $visible);

                if (!$active) {
                    $DIC->access()->addInfoItem(
                        ilAccessInfo::IL_NO_OBJECT_ACCESS,
                        $DIC->language()->txt('offline')
                    );
                }

                if ($active === false && $visible === false) {
                    return false;
                }
                break;

            case 'read':
                $active = self::isActivated($a_ref_id, $a_obj_id);
                if (!$active) {
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

    public static function isActivated(int $refId, int $objId, bool &$a_visible_flag = null) : bool
    {
        if (!self::lookupOnline($objId)) {
            $a_visible_flag = false;
            return false;
        }

        $a_visible_flag = true;

        $item = ilObjectActivation::getItem($refId);
        switch ($item['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                if (time() < $item['timing_start'] || time() > $item['timing_end']) {
                    $a_visible_flag = (bool) $item['visible'];
                    return false;
                }
        }

        return true;
    }

    public static function lookupOnline(int $a_obj_id) : bool
    {
        global $DIC;

        $res = $DIC->database()->query(
            'SELECT online_status FROM chatroom_settings WHERE object_id = ' .
            $DIC->database()->quote($a_obj_id, 'integer')
        );
        $row = $DIC->database()->fetchAssoc($res);

        return (bool) ($row['online_status'] ?? false);
    }

    public function canBeDelivered(ilWACPath $ilWACPath) : bool
    {
        if (preg_match("/chatroom\\/smilies\\//ui", $ilWACPath->getPath())) {
            return true;
        }

        return false;
    }
}
