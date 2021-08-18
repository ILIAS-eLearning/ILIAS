<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Saves (mostly asynchronously) user properties of tables (e.g. filter on/off)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* @ingroup ServicesTable
* @ilCtrl_Calls ilTableTemplatesStorage:
*/
class ilTableTemplatesStorage
{
    /**
     * @var ilDB
     */
    protected $db;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Store table template
     *
     * @param	string	$a_context
     * @param	int		$a_user_id
     * @param	string	$a_name
     * @param	array	$a_state
     */
    public function store($a_context, $a_user_id, $a_name, array $a_state)
    {
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
     *
     * @param	string	$a_context
     * @param	int		$a_user_id
     * @param	string	$a_name
     * @return	array
     */
    public function load($a_context, $a_user_id, $a_name)
    {
        $ilDB = $this->db;

        if ($a_context == "" || $a_name == "") {
            return;
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
     *
     * @param	string	$a_context
     * @param	int		$a_user_id
     * @param	string	$a_name
     */
    public function delete($a_context, $a_user_id, $a_name)
    {
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
     *
     * @param	string	$a_context
     * @param	int		$a_user_id
     * @return array
     */
    public function getNames($a_context, $a_user_id)
    {
        $ilDB = $this->db;

        if ($a_context == "") {
            return;
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
