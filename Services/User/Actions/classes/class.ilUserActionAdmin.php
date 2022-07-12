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
 * User action administration
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionAdmin
{
    protected static bool $loaded = false;
    protected static array $data = array();

    public static function activateAction(
        string $a_context_comp,
        string $a_context_id,
        string $a_action_comp,
        string $a_action_type,
        bool $a_active
    ) : void {
        global $DIC;

        $db = $DIC->database();
        $db->replace(
            "user_action_activation",
            array(
                "context_comp" => array("text", $a_context_comp),
                "context_id" => array("text", $a_context_id),
                "action_comp" => array("text", $a_action_comp),
                "action_type" => array("text", $a_action_type),
            ),
            array(
                "active" => array("integer", $a_active))
        );

        self::$loaded = false;
    }

    public static function lookupActive(
        string $a_context_comp,
        string $a_context_id,
        string $a_action_comp,
        string $a_action_type
    ) : bool {
        if (!self::$loaded) {
            self::loadData();
        }
        if (
            !isset(self::$data[$a_context_comp])
        || !isset(self::$data[$a_context_comp][$a_context_id])
        || !isset(self::$data[$a_context_comp][$a_context_id][$a_action_comp])
        || !isset(self::$data[$a_context_comp][$a_context_id][$a_action_comp][$a_action_type])
        ) {
            return false;
        }
        return (bool) self::$data[$a_context_comp][$a_context_id][$a_action_comp][$a_action_type];
    }

    protected static function loadData() : void
    {
        global $DIC;

        $db = $DIC->database();

        $set = $db->query("SELECT * FROM user_action_activation");
        self::$data = array();
        while ($rec = $db->fetchAssoc($set)) {
            self::$data[$rec["context_comp"]][$rec["context_id"]][$rec["action_comp"]][$rec["action_type"]] = (bool) $rec["active"];
        }

        self::$loaded = true;
    }
}
