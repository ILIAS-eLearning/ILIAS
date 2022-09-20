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
 * Saves (mostly asynchronously) user properties of tables (e.g. filter on/off)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @deprecated 11
 *
 */
class ilTableTemplatesStorage
{
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }

    /**
     * Store table template
     */
    public function store(
        string $a_context,
        int $a_user_id,
        string $a_name,
        array $a_state
    ): void {
        $ilDB = $this->db;

        if ($a_context == "" || $a_name == "") {
            return;
        }

        $ilDB->replace(
            "table_templates",
            array(
                "name" => array("text", $a_name),
                "user_id" => array("integer", $a_user_id),
                "context" => array("text", $a_context)),
            array(
                "value" => array("text", serialize($a_state))
            )
        );
    }

    /**
     * Get table template
     */
    public function load(
        string $a_context,
        int $a_user_id,
        string $a_name
    ): ?array {
        $ilDB = $this->db;

        if ($a_context == "" || $a_name == "") {
            return null;
        }

        $set = $ilDB->query(
            "SELECT value FROM table_templates " .
            " WHERE name = " . $ilDB->quote($a_name, "text") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND context = " . $ilDB->quote($a_context, "text")
        );
        $rec = $ilDB->fetchAssoc($set);
        return unserialize($rec["value"]);
    }

    /**
     * Delete table template
     */
    public function delete(
        string $a_context,
        int $a_user_id,
        string $a_name
    ): void {
        $ilDB = $this->db;

        if ($a_context == "" || $a_name == "") {
            return;
        }

        $ilDB->query(
            "DELETE FROM table_templates " .
            " WHERE name = " . $ilDB->quote($a_name, "text") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND context = " . $ilDB->quote($a_context, "text")
        );
    }

    /**
     * List templates
     */
    public function getNames(
        string $a_context,
        int $a_user_id
    ): array {
        $ilDB = $this->db;

        if ($a_context == "") {
            return [];
        }

        $set = $ilDB->query(
            "SELECT name FROM table_templates " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND context = " . $ilDB->quote($a_context, "text") .
            " ORDER BY name"
        );
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec["name"];
        }
        return $result;
    }
}
