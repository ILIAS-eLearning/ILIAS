<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Saves (mostly asynchronously) user properties of tables (e.g. filter on/off)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesTable
* @ilCtrl_Calls ilTablePropertiesStorage:
*/
class ilTablePropertiesStorage
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

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

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
    }

    public $properties = array(
        "filter" => array("storage" => "db"),
        "direction" => array("storage" => "db"),
        "order" => array("storage" => "db"),
        "rows" => array("storage" => "db"),
        "offset" => array("storage" => "session"),
        "selfields" => array("storage" => "db"),
        "selfilters" => array("storage" => "db"),
        "filter_values" => array("storage" => "db")
        );
    
    /**
    * execute command
    */
    public function &executeCommand()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        $cmd = $ilCtrl->getCmd();
        //		$next_class = $this->ctrl->getNextClass($this);

        $this->$cmd();
    }
    
    /**
     * Show Filter
     */
    public function showFilter()
    {
        $ilUser = $this->user;
        
        if ($_GET["user_id"] == $ilUser->getId()) {
            $this->storeProperty(
                $_GET["table_id"],
                $_GET["user_id"],
                "filter",
                1
            );
        }
    }
    
    /**
     * Hide Filter
     */
    public function hideFilter()
    {
        $ilUser = $this->user;
        
        if ($_GET["user_id"] == $ilUser->getId()) {
            $this->storeProperty(
                $_GET["table_id"],
                $_GET["user_id"],
                "filter",
                0
            );
        }
    }
    
    /**
    * Store property in session or db
    */
    public function storeProperty(
        $a_table_id,
        $a_user_id,
        $a_property,
        $a_value
    ) {
        $ilDB = $this->db;

        if ($a_table_id == "" || !$this->isValidProperty($a_property)) {
            return;
        }
        
        $storage = $this->properties[$a_property]["storage"];
        if ($a_user_id == ANONYMOUS_USER_ID) {
            $storage = "session";
        }
        
        switch ($storage) {
            case "session":
                $_SESSION["table"][$a_table_id][$a_user_id][$a_property]
                    = $a_value;
                break;
                
            case "db":
                $ilDB->replace(
                    "table_properties",
                    array(
                    "table_id" => array("text", $a_table_id),
                    "user_id" => array("integer", $a_user_id),
                    "property" => array("text", $a_property)),
                    array(
                    "value" => array("text", $a_value)
                    )
                );
        }
    }
    
    /**
    * Get property in session or db
    */
    public function getProperty($a_table_id, $a_user_id, $a_property)
    {
        $ilDB = $this->db;

        if ($a_table_id == "" || !$this->isValidProperty($a_property)) {
            return;
        }

        $storage = $this->properties[$a_property]["storage"];
        if ($a_user_id == ANONYMOUS_USER_ID) {
            $storage = "session";
        }
        
        switch ($storage) {
            case "session":
                return $_SESSION["table"][$a_table_id][$a_user_id][$a_property];
                break;
                
            case "db":
                $set = $ilDB->query(
                    $q = "SELECT value FROM table_properties " .
                    " WHERE table_id = " . $ilDB->quote($a_table_id, "text") .
                    " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
                    " AND property = " . $ilDB->quote($a_property, "text")
                );
                $rec = $ilDB->fetchAssoc($set);
                return $rec["value"];
                break;
        }
    }

    /**
     * Check if given property id is valid
     *
     * @param	string	$a_property
     * @return	bool
     */
    public function isValidProperty($a_property)
    {
        if (array_key_exists($a_property, $this->properties)) {
            return true;
        }
        return false;
    }
}
