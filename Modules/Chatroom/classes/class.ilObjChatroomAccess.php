<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectAccess.php';
require_once 'Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php';

/**
 * Access class for chatroom objects.
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAccess extends ilObjectAccess implements ilWACCheckingClass
{
    /**
     * @var null|bool
     */
    private static $chat_enabled = null;

    /**
     * {@inheritdoc}
     */
    public static function _getCommands()
    {
        $commands = array();
        $commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "enter", "default" => true);
        $commands[] = array("permission" => "write", "cmd" => "settings-general", "lang_var" => "settings");

        // alex 3 Oct 2012: this leads to a blank screen, i guess it is a copy/paste bug from files
        //$commands[] = array("permission" => "write", "cmd" => "versions", "lang_var" => "versions");

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        if (is_string($a_target)) {
            $t_arr = explode("_", $a_target);

            if (count($t_arr) < 2 || $t_arr[0] != "chtr" || ((int) $t_arr[1]) <= 0) {
                return false;
            }

            if ($DIC->rbac()->system()->checkAccess("read", $t_arr[1])) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        if ($a_user_id == '') {
            $a_user_id = $GLOBALS['DIC']->user()->getId();
        }

        return self::checkRoomAccess($a_permission, $a_ref_id, $a_obj_id, $a_user_id);
    }
    
    
    public static function checkRoomAccess($a_permission, $a_ref_id, $a_obj_id, $a_user_id)
    {
        if (self::$chat_enabled === null) {
            $chatSetting = new ilSetting('chatroom');
            self::$chat_enabled = (boolean) $chatSetting->get('chat_enabled');
        }

        if ($GLOBALS['DIC']->rbac()->system()->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
            return true;
        }

        switch ($a_permission) {
            case 'visible':
                $visible = null;

                $active = self::isActivated($a_ref_id, $a_obj_id, $visible);
                $hasWriteAccess = $GLOBALS['DIC']->rbac()->system()->checkAccessOfUser($a_user_id, 'write', $a_ref_id);

                if (!$active) {
                    $GLOBALS['DIC']->access()->addInfoItem(IL_NO_OBJECT_ACCESS, $GLOBALS['DIC']->language()->txt('offline'));
                }

                if (!$hasWriteAccess && !$active && !$visible) {
                    return false;
                }
                break;

            case 'read':
                $hasWriteAccess = $GLOBALS['DIC']->rbac()->system()->checkAccessOfUser($a_user_id, 'write', $a_ref_id);
                if ($hasWriteAccess) {
                    return true;
                }

                $active = self::isActivated($a_ref_id, $a_obj_id);
                if (!$active) {
                    $GLOBALS['DIC']->access()->addInfoItem(IL_NO_OBJECT_ACCESS, $GLOBALS['DIC']->language()->txt('offline'));
                    return false;
                }
                break;
        }

        return self::$chat_enabled;
    }

    /**
     * @param int $refId
     * @param int $objId
     * @param null $a_visible_flag
     * @return bool
     */
    public static function isActivated($refId, $objId, &$a_visible_flag = null)
    {
        if (!self::lookupOnline($objId)) {
            $a_visible_flag = false;
            return false;
        }

        $a_visible_flag = true;

        require_once 'Services/Object/classes/class.ilObjectActivation.php';
        $item = ilObjectActivation::getItem($refId);
        switch ($item['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                if (time() < $item['timing_start'] || time() > $item['timing_end']) {
                    $a_visible_flag = $item['visible'];
                    return false;
                }

                // no break
            default:
                return true;
        }
    }

    /**
     * @param int $a_obj_id
     * @return bool
     */
    public static function lookupOnline($a_obj_id)
    {
        global $DIC;

        $res = $DIC->database()->query("SELECT online_status FROM chatroom_settings WHERE object_id = " . $DIC->database()->quote($a_obj_id, 'integer'));
        $row = $DIC->database()->fetchAssoc($res);

        return (bool) $row['online_status'];
    }

    /**
     * @inheritdoc
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        if (preg_match("/chatroom\\/smilies\\//ui", $ilWACPath->getPath())) {
            return true;
        }

        return false;
    }
}
