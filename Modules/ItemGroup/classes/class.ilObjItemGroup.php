<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";
require_once "Services/Object/classes/class.ilObjectActivation.php";

include_once("./Modules/ItemGroup/classes/class.ilItemGroupAR.php");


/**
 * Class ilObjItemGroup
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @extends ilObject2
 */
class ilObjItemGroup extends ilObject2
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_def;

    protected $access_type; // [int]
    protected $access_begin; // [timestamp]
    protected $access_end; // [timestamp]
    protected $access_visibility; // [bool]
    protected $item_data_ar = null; // active record
    
    /**
     * Constructor
     *
     * @param int $a_id id
     * @param bool $a_reference ref id?
     * @return
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        $this->log = $DIC["ilLog"];
        $tree = $DIC->repositoryTree();
        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();
        
        $this->tree = $tree;
        $this->obj_def = $objDefinition;
        $this->db = $ilDB;

        $this->item_data_ar = new ilItemGroupAR();

        parent::__construct($a_id, $a_reference);
    }

    /**
     * Set ID
     *
     * @param int $a_val ID
     */
    public function setId($a_val)
    {
        parent::setId($a_val);
        $this->item_data_ar->setId($a_val);
    }

    /**
     * Init type
     */
    public function initType()
    {
        $this->type = "itgr";
    }

    /**
     * Set hide title
     *
     * @param bool $a_val hide title
     */
    public function setHideTitle($a_val)
    {
        $this->item_data_ar->setHideTitle($a_val);
    }

    /**
     * Get hide title
     *
     * @return bool hide title
     */
    public function getHideTitle()
    {
        return $this->item_data_ar->getHideTitle();
    }

    /**
     * Set behaviour (see ilItemGroupBehaviour)
     *
     * @param int $a_val behaviour
     */
    public function setBehaviour($a_val)
    {
        $this->item_data_ar->setBehaviour($a_val);
    }

    /**
     * Get hide title
     *
     * @return int behaviour
     */
    public function getBehaviour()
    {
        return $this->item_data_ar->getBehaviour();
    }

    /**
     * Read
     */
    protected function doRead()
    {
        $this->item_data_ar = new ilItemGroupAR($this->getId());
    }

    /**
     * Creation
     */
    protected function doCreate()
    {
        if ($this->getId()) {
            $this->item_data_ar->setId($this->getId());
            $this->item_data_ar->create();
        }
    }
        
    /**
     * Update
     */
    protected function doUpdate()
    {
        if ($this->getId()) {
            $this->item_data_ar->update();
        }
    }

    /**
     * Deletion
     */
    protected function doDelete()
    {
        if ($this->getId()) {
            $this->item_data_ar->delete();
        }
    }
    
    /**
     * Clone obj item group
     * @param ilObjItemGroup $new_obj
     * @param int $a_target_id
     * @param string $a_copy_id
     * @param bool $a_omit_tree
     */
    protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null, $a_omit_tree = false)
    {
        $new_obj->setHideTitle($this->getHideTitle());
        $new_obj->setBehaviour($this->getBehaviour());
        $new_obj->update();
    }

    /**
     * Clone dependencies
     *
     * @param
     * @return
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        $ilLog = $this->log;
        
        $ilLog->write(__METHOD__ . ': Cloning item group dependencies -' . $a_source_id . '-');
        
        parent::cloneDependencies($a_target_id, $a_copy_id);

        include_once('./Modules/ItemGroup/classes/class.ilItemGroupItems.php');
        $ig_items = new ilItemGroupItems($a_target_id);
        $ig_items->cloneItems($this->getRefId(), $a_copy_id);

        return true;
    }

    /**
     * Fix container item group references after a container has been cloned
     *
     * @param
     * @return
     */
    public static function fixContainerItemGroupRefsAfterCloning($a_source_container, $a_copy_id)
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        
        $ilLog->write(__METHOD__ . ': Fix item group references in ' . $a_source_container->getType());
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
                
        $new_container_ref_id = $mappings[$a_source_container->getRefId()];
        $ilLog->write(__METHOD__ . ': 2-' . $new_container_ref_id . '-');
        $new_container_obj_id = ilObject::_lookupObjId($new_container_ref_id);
        
        include_once("./Services/COPage/classes/class.ilPageObject.php");
        include_once("./Services/Container/classes/class.ilContainerPage.php");
        $ilLog->write(__METHOD__ . ': 3' . $new_container_obj_id . '-');
        if (ilPageObject::_exists("cont", $new_container_obj_id)) {
            $ilLog->write(__METHOD__ . ': 4');
            $new_page = new ilContainerPage($new_container_obj_id);
            $new_page->buildDom();
            include_once("./Services/COPage/classes/class.ilPCResources.php");
            ilPCResources::modifyItemGroupRefIdsByMapping($new_page, $mappings);
            $new_page->update();
        }
        $ilLog->write(__METHOD__ . ': 5');
    }

    /**
     * Lookup hide title
     *
     * @param int $a_id ID
     * @return bool
     */
    public static function lookupHideTitle($a_id)
    {
        return self::lookup($a_id, "hide_title");
    }

    /**
     * Lookup behaviour
     *
     * @param int $a_id ID
     * @return int
     */
    public static function lookupBehaviour($a_id)
    {
        return self::lookup($a_id, "behaviour");
    }

    /**
     * Lookup hide title
     *
     * @param int $a_id ID
     * @return bool
     */
    protected static function lookup($a_id, $a_key)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT " . $a_key . " FROM itgr_data " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_key];
    }
}
