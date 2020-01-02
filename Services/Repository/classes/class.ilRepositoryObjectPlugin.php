<?php

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all repository object plugin classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRepository
*/
abstract class ilRepositoryObjectPlugin extends ilPlugin
{
    /**
     * @var ilLanguage
     */
    protected $lng;

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
        parent::__construct();

        $this->db = $DIC->database();
    }

    /**
    * Get Component Type
    *
    * @return        string        Component Type
    */
    public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }
    
    /**
    * Get Component Name.
    *
    * @return        string        Component Name
    */
    public function getComponentName()
    {
        return "Repository";
    }

    /**
    * Get Slot Name.
    *
    * @return        string        Slot Name
    */
    public function getSlot()
    {
        return "RepositoryObject";
    }

    /**
    * Get Slot ID.
    *
    * @return        string        Slot Id
    */
    public function getSlotId()
    {
        return "robj";
    }

    /**
    * Object initialization done by slot.
    */
    protected function slotInit()
    {
        // nothing to do here
    }
    
    /**
    * Get Icon
    */
    public static function _getIcon($a_type, $a_size)
    {
        return ilPlugin::_getImagePath(
            IL_COMP_SERVICE,
            "Repository",
            "robj",
            ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $a_type),
            "icon_" . $a_type . ".svg"
        );
    }
    
    /**
    * Get class name
    */
    public static function _getName($a_id)
    {
        $name = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $a_id);
        if ($name != "") {
            return $name;
        }
    }
    
    /**
    * Before activation processing
    */
    protected function beforeActivation()
    {
        $ilDB = $this->db;
        
        // before activating, we ensure, that the type exists in the ILIAS
        // object database and that all permissions exist
        $type = $this->getId();
        
        if (substr($type, 0, 1) != "x") {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }
        
        // check whether type exists in object data, if not, create the type
        $set = $ilDB->query(
            "SELECT * FROM object_data " .
            " WHERE type = " . $ilDB->quote("typ", "text") .
            " AND title = " . $ilDB->quote($type, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $t_id = $rec["obj_id"];
        } else {
            $t_id = $ilDB->nextId("object_data");
            $ilDB->manipulate("INSERT INTO object_data " .
                "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
                $ilDB->quote($t_id, "integer") . "," .
                $ilDB->quote("typ", "text") . "," .
                $ilDB->quote($type, "text") . "," .
                $ilDB->quote("Plugin " . $this->getPluginName(), "text") . "," .
                $ilDB->quote(-1, "integer") . "," .
                $ilDB->quote(ilUtil::now(), "timestamp") . "," .
                $ilDB->quote(ilUtil::now(), "timestamp") .
                ")");
        }

        // add rbac operations
        // 1: edit_permissions, 2: visible, 3: read, 4:write, 6:delete
        $ops = array(1, 2, 3, 4, 6);
        if ($this->allowCopy()) {
            $ops[] = ilRbacReview::_getOperationIdByName("copy");
        }
        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type
            $set = $ilDB->query(
                "SELECT * FROM rbac_ta " .
                " WHERE typ_id = " . $ilDB->quote($t_id, "integer") .
                " AND ops_id = " . $ilDB->quote($op, "integer")
            );
            if (!$ilDB->fetchAssoc($set)) {
                $ilDB->manipulate("INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $ilDB->quote($t_id, "integer") . "," .
                    $ilDB->quote($op, "integer") .
                    ")");
            }
        }
        
        // now add creation permission, if not existing
        $set = $ilDB->query(
            "SELECT * FROM rbac_operations " .
            " WHERE class = " . $ilDB->quote("create", "text") .
            " AND operation = " . $ilDB->quote("create_" . $type, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $create_ops_id = $rec["ops_id"];
        } else {
            $create_ops_id = $ilDB->nextId("rbac_operations");
            $ilDB->manipulate("INSERT INTO rbac_operations " .
                "(ops_id, operation, description, class) VALUES (" .
                $ilDB->quote($create_ops_id, "integer") . "," .
                $ilDB->quote("create_" . $type, "text") . "," .
                $ilDB->quote("create " . $type, "text") . "," .
                $ilDB->quote("create", "text") .
                ")");
        }
        
        // assign creation operation to root, cat, crs, grp and fold
        $par_types = $this->getParentTypes();
        foreach ($par_types as $par_type) {
            $set = $ilDB->query(
                "SELECT obj_id FROM object_data " .
                " WHERE type = " . $ilDB->quote("typ", "text") .
                " AND title = " . $ilDB->quote($par_type, "text")
            );
            if ($rec = $ilDB->fetchAssoc($set)) {
                if ($rec["obj_id"] > 0) {
                    $set = $ilDB->query(
                        "SELECT * FROM rbac_ta " .
                        " WHERE typ_id = " . $ilDB->quote($rec["obj_id"], "integer") .
                        " AND ops_id = " . $ilDB->quote($create_ops_id, "integer")
                    );
                    if (!$ilDB->fetchAssoc($set)) {
                        $ilDB->manipulate("INSERT INTO rbac_ta " .
                            "(typ_id, ops_id) VALUES (" .
                            $ilDB->quote($rec["obj_id"], "integer") . "," .
                            $ilDB->quote($create_ops_id, "integer") .
                            ")");
                    }
                }
            }
        }
        
        return true;
    }
    
    protected function beforeUninstallCustom()
    {
        // plugin-specific
        // false would indicate that anything went wrong
        return true;
    }
    
    abstract protected function uninstallCustom();
    
    final protected function beforeUninstall()
    {
        if ($this->beforeUninstallCustom()) {
            include_once "Services/Repository/classes/class.ilRepUtil.php";
            $rep_util = new ilRepUtil();
            $rep_util->deleteObjectType($this->getId());
            
            // custom database tables may be needed by plugin repository object
            $this->uninstallCustom();

            return true;
        }
        return false;
    }

    /**
     * @return string[]
     */
    public function getParentTypes()
    {
        $par_types = array("root", "cat", "crs", "grp", "fold");
        return $par_types;
    }

    /**
     * decides if this repository plugin can be copied
     *
     * @return bool
     */
    public function allowCopy()
    {
        return false;
    }
}
