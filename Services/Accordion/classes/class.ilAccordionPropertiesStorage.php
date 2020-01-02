<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Saves (mostly asynchronously) user properties of accordions
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesAccordion
* @ilCtrl_Calls ilAccordionPropertiesStorage:
*/
class ilAccordionPropertiesStorage
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
        "opened" => array("storage" => "session")
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
    public function setOpenedTab()
    {
        $ilUser = $this->user;
        
        if ($_GET["user_id"] == $ilUser->getId()) {
            switch ($_GET["act"]) {

                case "add":
                    $cur = $this->getProperty(
                        $_GET["accordion_id"],
                        (int) $_GET["user_id"],
                        "opened"
                    );
                    $cur_arr = explode(";", $cur);
                    if (!in_array((int) $_GET["tab_nr"], $cur_arr)) {
                        $cur_arr[] = (int) $_GET["tab_nr"];
                    }
                    $this->storeProperty(
                        $_GET["accordion_id"],
                        (int) $_GET["user_id"],
                        "opened",
                        implode($cur_arr, ";")
                    );
                    break;

                case "rem":
                    $cur = $this->getProperty(
                        $_GET["accordion_id"],
                        (int) $_GET["user_id"],
                        "opened"
                    );
                    $cur_arr = explode(";", $cur);
                    if (($key = array_search((int) $_GET["tab_nr"], $cur_arr)) !== false) {
                        unset($cur_arr[$key]);
                    }
                    $this->storeProperty(
                        $_GET["accordion_id"],
                        (int) $_GET["user_id"],
                        "opened",
                        implode($cur_arr, ";")
                    );
                    break;

                case "clear":
                    $this->storeProperty(
                        $_GET["accordion_id"],
                        (int) $_GET["user_id"],
                        "opened",
                        ""
                    );
                    break;

                case "set":
                default:
                    $this->storeProperty(
                        $_GET["accordion_id"],
                        (int) $_GET["user_id"],
                        "opened",
                        $_GET["tab_nr"]
                    );
                    break;
            }
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

        switch ($this->properties[$a_property]["storage"]) {
            case "session":
                $_SESSION["accordion"][$a_table_id][$a_user_id][$a_property]
                    = $a_value;
                break;
                
            case "db":
/*
                $ilDB->replace("table_properties", array(
                    "table_id" => array("text", $a_table_id),
                    "user_id" => array("integer", $a_user_id),
                    "property" => array("text", $a_property)),
                    array(
                    "value" => array("text", $a_value)
                    ));
*/
        }
    }
    
    /**
    * Get property in session or db
    */
    public function getProperty($a_table_id, $a_user_id, $a_property)
    {
        $ilDB = $this->db;

        switch ($this->properties[$a_property]["storage"]) {
            case "session":
                $r = $_SESSION["accordion"][$a_table_id][$a_user_id][$a_property];
//echo "<br><br><br><br><br><br><br><br>get-".$r;
                return $r;
                break;
                
            case "db":
/*
                $set = $ilDB->query("SELECT value FROM table_properties ".
                    " WHERE table_id = ".$ilDB->quote($a_table_id, "text").
                    " AND user_id = ".$ilDB->quote($a_user_id, "integer").
                    " AND property = ".$ilDB->quote($a_property, "text")
                    );
                $rec  = $ilDB->fetchAssoc($set);
                return $rec["value"];
                break;
*/
        }
    }
}
