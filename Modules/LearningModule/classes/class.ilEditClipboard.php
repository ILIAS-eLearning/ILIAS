<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilEditClipboard
 *
 * This class supports only a few basic clipboard functions for the
 * editor and should be further elaborated in the future.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilEditClipboard
{
    public static function getContentObjectType()
    {
        global $DIC;
        $user = $DIC->user();
        $lm_type = $user->getPref("lm_clipboard_type");
        if ($lm_type != "") {
            return $lm_type;
        } else {
            return false;
        }
    }

    public static function setAction($a_action)
    {
        global $DIC;
        $user = $DIC->user();
        $user->writePref("lm_clipboard_action", $a_action);
    }

    public static function getAction()
    {
        global $DIC;
        $user = $DIC->user();
        $lm_action = $user->getPref("lm_clipboard_action");
        if ($lm_action != "") {
            return $lm_action;
        } else {
            return false;
        }
    }

    public static function getContentObjectId()
    {
        global $DIC;
        $user = $DIC->user();
        $lm_id = $user->getPref("lm_clipboard_id");
        if ($lm_id != "") {
            return $lm_id;
        }
        return "";
    }

    public static function storeContentObject($a_type, $a_id, $a_action = "cut")
    {
        global $DIC;
        $user = $DIC->user();
        $user->writePref("lm_clipboard_id", $a_id);
        $user->writePref("lm_clipboard_type", $a_type);
        self::setAction($a_action);
    }

    public static function clear()
    {
        global $DIC;
        $user = $DIC->user();
        $user->clipboardDeleteObjectsOfType("pg");
        $user->clipboardDeleteObjectsOfType("st");
        $user->writePref("lm_clipboard_id", "");
        $user->writePref("lm_clipboard_type", "");
        $user->writePref("lm_clipboard_action", "");
    }
}
