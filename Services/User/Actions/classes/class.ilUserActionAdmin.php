<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User action administration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilUserActionAdmin
{
    protected static $loaded = false;
    protected static $data = array();

    /**
     * Activate action
     *
     * @param string $a_context_comp
     * @param string $a_context_id
     * @param string $a_action_comp
     * @param string $a_action_type
     * @param bool $a_active active true/false
     */
    public static function activateAction($a_context_comp, $a_context_id, $a_action_comp, $a_action_type, $a_active)
    {
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

    /**
     * Is activated?
     *
     * @param string $a_context_comp
     * @param string $a_context_id
     * @param string $a_action_comp
     * @param string $a_action_type
     * @return bool active true/false
     */
    public static function lookupActive($a_context_comp, $a_context_id, $a_action_comp, $a_action_type)
    {
        if (!self::$loaded) {
            self::loadData();
        }
        return (bool) self::$data[$a_context_comp][$a_context_id][$a_action_comp][$a_action_type];
    }

    /**
     * Load data
     *
     * @param
     * @return
     */
    protected static function loadData()
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
