<?php declare(strict_types = 1);

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
 * Block Setting class.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBlockSetting
{
    /**
     * @var array<string,?string>
     */
    public static array $setting = array();
    public static bool $pd_preloaded = false;

    /**
     * Lookup setting from database.
     */
    public static function _lookup(
        string $a_type,
        string $a_setting,
        int $a_user = 0,
        int $a_block_id = 0
    ) : ?string {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();
        
        $key = $a_type . ":" . $a_setting . ":" . $a_user . ":" . $a_block_id;
        if (isset(self::$setting[$key])) {
            return (string) self::$setting[$key];
        }
        
        $set = $ilDB->query(sprintf(
            "SELECT value FROM il_block_setting WHERE type = %s " .
            "AND user_id = %s AND setting = %s AND block_id = %s",
            $ilDB->quote($a_type, "text"),
            $ilDB->quote($a_user, "integer"),
            $ilDB->quote($a_setting, "text"),
            $ilDB->quote($a_block_id, "integer")
        ));
        if ($rec = $ilDB->fetchAssoc($set)) {
            self::$setting[$key] = $rec["value"];
            return $rec["value"];
        } elseif ($ilSetting->get('block_default_setting_' . $a_type . '_' . $a_setting, null)) {
            self::$setting[$key] = $ilSetting->get('block_default_setting_' . $a_type . '_' . $a_setting, null);
            return $ilSetting->get('block_default_setting_' . $a_type . '_' . $a_setting, null);
        } else {
            self::$setting[$key] = false;
            return null;
        }
    }

    /**
     * Preload pd info
     */
    public static function preloadPDBlockSettings() : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        if (!self::$pd_preloaded) {
            $blocks = array("pdbookm", "pdcal", "pdfeedb", "pditems",
                "pdmail", "pdnews", "pdnotes", "pdtag");
            $settings = array("detail", "nr", "side");
            $user_id = $ilUser->getId();

            foreach ($blocks as $b) {
                foreach ($settings as $s) {
                    $key = $b . ":" . $s . ":" . $user_id . ":0";
                    if ($s == "detail") {
                        self::$setting[$key] = 2;
                    } else {
                        self::$setting[$key] = false;
                    }
                }
            }

            $set = $ilDB->query(
                $q = "SELECT type, setting, value FROM il_block_setting WHERE " .
                " user_id = " . $ilDB->quote($user_id, "integer") .
                " AND " . $ilDB->in("type", $blocks, false, "text") .
                " AND " . $ilDB->in("setting", $settings, false, "text")
            );
            while ($rec = $ilDB->fetchAssoc($set)) {
                $key = $rec["type"] . ":" . $rec["setting"] . ":" . $user_id . ":0";
                self::$setting[$key] = $rec["value"];
            }

            self::$pd_preloaded = true;
        }
    }

    /**
     * Write setting to database.
     */
    public static function _write(
        string $a_type,
        string $a_setting,
        string $a_value,
        int $a_user = 0,
        int $a_block_id = 0
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(sprintf(
            "DELETE FROM il_block_setting WHERE type = %s AND user_id = %s AND block_id = %s AND setting = %s",
            $ilDB->quote($a_type, "text"),
            $ilDB->quote($a_user, "integer"),
            $ilDB->quote($a_block_id, "integer"),
            $ilDB->quote($a_setting, "text")
        ));
        $ilDB->manipulate(sprintf(
            "INSERT INTO il_block_setting  (type, user_id, setting, block_id, value) VALUES (%s,%s,%s,%s,%s)",
            $ilDB->quote($a_type, "text"),
            $ilDB->quote($a_user, "integer"),
            $ilDB->quote($a_setting, "text"),
            $ilDB->quote($a_block_id, "integer"),
            $ilDB->quote($a_value, "text")
        ));
    }

    /**
     * Lookup detail level.
     */
    public static function _lookupDetailLevel(
        string $a_type,
        int $a_user = 0,
        int $a_block_id = 0
    ) : int {
        $detail = self::_lookup($a_type, "detail", $a_user, $a_block_id);

        if (is_null($detail)) {		// return a level of 2 (standard value) if record does not exist
            return 2;
        }

        return (int) $detail;
    }

    public static function _writeDetailLevel(
        string $a_type,
        string $a_value,
        int $a_user = 0,
        int $a_block_id = 0
    ) : void {
        ilBlockSetting::_write($a_type, "detail", $a_value, $a_user, $a_block_id);
    }

    public static function _writeNumber(
        string $a_type,
        string $a_value,
        int $a_user = 0,
        int $a_block_id = 0
    ) : void {
        ilBlockSetting::_write($a_type, "nr", $a_value, $a_user, $a_block_id);
    }

    /**
     * Lookup side.
     */
    public static function _lookupSide(
        string $a_type,
        int $a_user = 0,
        int $a_block_id = 0
    ) : ?string {
        return ilBlockSetting::_lookup($a_type, "side", $a_user, $a_block_id);
    }

    public static function _writeSide(
        string $a_type,
        string $a_value,
        int $a_user = 0,
        int $a_block_id = 0
    ) : void {
        ilBlockSetting::_write($a_type, "side", $a_value, $a_user, $a_block_id);
    }

    public static function _deleteSettingsOfUser(
        int $a_user
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_user > 0) {
            $ilDB->manipulate("DELETE FROM il_block_setting WHERE user_id = " .
                $ilDB->quote($a_user, "integer"));
        }
    }

    public static function _deleteSettingsOfBlock(
        int $a_block_id,
        string $a_block_type
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_block_id > 0) {
            $ilDB->manipulate("DELETE FROM il_block_setting WHERE block_id = " .
                $ilDB->quote($a_block_id, "integer") .
                " AND type = " . $ilDB->quote($a_block_type, "text"));
        }
    }

    public static function cloneSettingsOfBlock(
        string $block_type,
        int $block_id,
        int $new_block_id
    ) : void {
        global $DIC;

        $db = $DIC->database();

        $set = $db->queryF(
            "SELECT * FROM il_block_setting " .
            " WHERE block_id = %s AND type = %s AND user_id = %s",
            array("integer", "text", "integer"),
            array($block_id, $block_type, 0)
        );
        while ($rec = $db->fetchAssoc($set)) {
            self::_write($block_type, $rec["setting"], $rec["value"], 0, $new_block_id);
        }
    }
}
