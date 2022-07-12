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
    public static function getContentObjectType() : string
    {
        global $DIC;
        $user = $DIC->user();
        $lm_type = $user->getPref("lm_clipboard_type");
        if ($lm_type != "") {
            return $lm_type;
        } else {
            return "";
        }
    }

    public static function setAction(string $a_action) : void
    {
        global $DIC;
        $user = $DIC->user();
        $user->writePref("lm_clipboard_action", $a_action);
    }

    public static function getAction() : string
    {
        global $DIC;
        $user = $DIC->user();
        return (string) $user->getPref("lm_clipboard_action");
    }

    public static function getContentObjectId() : int
    {
        global $DIC;
        $user = $DIC->user();
        $lm_id = $user->getPref("lm_clipboard_id");
        if ($lm_id != "") {
            return (int) $lm_id;
        }
        return 0;
    }

    public static function storeContentObject(
        string $a_type,
        int $a_id,
        string $a_action = "cut"
    ) : void {
        global $DIC;
        $user = $DIC->user();
        $user->writePref("lm_clipboard_id", $a_id);
        $user->writePref("lm_clipboard_type", $a_type);
        self::setAction($a_action);
    }

    public static function clear() : void
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
