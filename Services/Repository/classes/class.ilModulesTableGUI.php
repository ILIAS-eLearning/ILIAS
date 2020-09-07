<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Component/classes/class.ilComponent.php");
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
 * TableGUI class for module listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesRepository
 */
class ilModulesTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    protected $pos_group_options; // [array]
    protected $old_grp_id; // [int]
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        $this->settings = $DIC->settings();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setId("repmodtbl");
        
        $this->setTitle($lng->txt("cmps_repository_object_types"));

        $this->addColumn($lng->txt("cmps_add_new_rank"), "");
        $this->addColumn($lng->txt("cmps_rep_object"), "");
        $this->addColumn($lng->txt("cmps_module"), "");
        $this->addColumn($lng->txt("cmps_group"), "");
        $this->addColumn($lng->txt("cmps_enable_creation"), "");
    
        if ((bool) $a_has_write) {
            // save options command
            $this->addCommandButton("saveModules", $lng->txt("cmps_save_options"));
        }
    
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.table_row_module.html", "Services/Repository");
        $this->setLimit(10000);
        $this->setExternalSorting(true);
                
        $this->getComponents();
        
        $this->old_grp_id = 0;
    }
    
    /**
    * Get pages for list.
    */
    public function getComponents()
    {
        $objDefinition = $this->obj_definition;
        $ilSetting = $this->settings;
        $lng = $this->lng;
        $ilPluginAdmin = $this->plugin_admin;
        
        // unassigned objects should be last
        $this->pos_group_options = array(0 => $lng->txt("rep_new_item_group_unassigned"));
        $pos_group_map[0] = "9999";
        
        include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");
        foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
            // #12807
            if ($item["type"] == ilObjRepositorySettings::NEW_ITEM_GROUP_TYPE_GROUP) {
                $this->pos_group_options[$item["id"]] = $item["title"];
                $pos_group_map[$item["id"]] = $item["pos"];
            }
        }
                
        $obj_types = array();
        
        // parse modules
        include_once("./Services/Component/classes/class.ilModule.php");
        foreach (ilModule::getAvailableCoreModules() as $mod) {
            $has_repo = false;
            $rep_types =
                $objDefinition->getRepositoryObjectTypesForComponent(IL_COMP_MODULE, $mod["subdir"]);
            if (sizeof($rep_types) > 0) {
                foreach ($rep_types as $ridx => $rt) {
                    // we only want to display repository modules
                    if ($rt["repository"]) {
                        $has_repo = true;
                    } else {
                        unset($rep_types[$ridx]);
                    }
                }
            }
            if ($has_repo) {
                foreach ($rep_types as $rt) {
                    $obj_types[$rt["id"]] = array(
                        "object" => $rt["class_name"],
                        "caption" => $lng->txt("obj_" . $rt["id"]),
                        "subdir" => $mod["subdir"],
                        "grp" => $rt["grp"],
                        "default_pos" => $rt["default_pos"]
                    );
                }
            }
        }
        
        // parse plugins
        $obj_types = $this->getPluginComponents($obj_types, IL_COMP_SERVICE, "Repository", "robj");
        $obj_types = $this->getPluginComponents($obj_types, IL_COMP_MODULE, "OrgUnit", "orguext");

        // parse positions
        $data = array();
        foreach ($obj_types as $obj_type => $item) {
            $org_pos = $ilSetting->get("obj_add_new_pos_" . $obj_type);
            if (!(int) $org_pos) {
                // no setting yet, use default
                $org_pos = $item["default_pos"];
            }
            if (strlen($org_pos) < 8) {
                // "old" setting without group part, add "unassigned" group
                $org_pos = $pos_group_map[0] . str_pad($org_pos, 4, "0", STR_PAD_LEFT);
            }
            
            $pos_grp_id = $ilSetting->get("obj_add_new_pos_grp_" . $obj_type, 0);

            $group = null;
            if ($item["grp"] != "") {
                $group = $objDefinition->getGroup($item["grp"]);
                $group = $group["name"];
            }

            $data[] = array(
                "id" => $obj_type,
                "object" => $item["object"],
                "caption" => $item["caption"],
                "subdir" => $item["subdir"],
                "pos" => (int) substr($org_pos, 4),
                "pos_group" => $pos_grp_id,
                "creation" => !(bool) $ilSetting->get("obj_dis_creation_" . $obj_type, false),
                "group_id" => $item["grp"],
                "group" => $group,
                "sort_key" => (int) $org_pos
            );
        }
        
        $data = ilUtil::sortArray($data, "sort_key", "asc", true);
        
        $this->setData($data);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        if ($a_set["pos_group"] != $this->old_grp_id) {
            $this->tpl->setCurrentBlock("pos_grp_bl");
            $this->tpl->setVariable("TXT_POS_GRP", $this->pos_group_options[$a_set["pos_group"]]);
            $this->tpl->parseCurrentBlock();
                        
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
            
            $this->css_row = ($this->css_row != "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
            $this->tpl->setVariable("CSS_ROW", $this->css_row);
                        
            $this->old_grp_id = $a_set["pos_group"];
        }
        
        // group
        if ($a_set["group_id"] != "") {
            $this->tpl->setCurrentBlock("group");
            $this->tpl->setVariable("VAL_GROUP", $a_set["group"]);
            $this->tpl->setVariable("VAL_GROUP_ID", $a_set["group_id"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("rep_object");
        // #11598 - using "caption" (from lng) instead of "object"
        $this->tpl->setVariable("TXT_REP_OBJECT", $a_set["caption"]);
        $this->tpl->setVariable("TXT_REP_OBJECT_ID", $a_set["id"]);
        $this->tpl->setVariable(
            "IMG_REP_OBJECT",
            ilObject::_getIcon("", "tiny", $a_set["id"])
        );

        // grouping
        $sel = ilUtil::formSelect(
            $a_set["pos_group"],
            "obj_grp[" . $a_set["id"] . "]",
            $this->pos_group_options,
            false,
            true
        );
        $this->tpl->setVariable("GROUP_SEL", $sel);
        
        // position
        $this->tpl->setVariable("VAR_POS", "obj_pos[" . $a_set["id"] . "]");
        $this->tpl->setVariable("VAL_POS", ilUtil::prepareFormOutput($a_set["pos"]));

        // enable creation
        $this->tpl->setVariable("VAR_DISABLE_CREATION", "obj_enbl_creation[" . $a_set["id"] . "]");
        if ($a_set["creation"]) {
            $this->tpl->setVariable(
                "CHECKED_DISABLE_CREATION",
                ' checked="checked" '
            );
        }
        
        $this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
    }

    /**
     * @param $obj_types
     * @param $component
     * @param $slotName
     * @param $slotId
     * @return mixed
     */
    protected function getPluginComponents($obj_types, $component, $slotName, $slotId)
    {
        $ilPluginAdmin = $this->plugin_admin;
        $lng = $this->lng;
        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot($component, $slotName, $slotId);
        foreach ($pl_names as $pl_name) {
            $pl_id = ilPlugin::lookupIdForName($component, $slotName, $slotId, $pl_name);
            if ($pl_id) {
                $obj_types[$pl_id] = array(
                    "object" => $pl_name,
                    "caption" => ilObjectPlugin::lookupTxtById($pl_id, "obj_" . $pl_id),
                    "subdir" => $lng->txt("cmps_plugin"),
                    "grp" => "",
                    "default_pos" => 2000
                );
            }
        }
        return $obj_types;
    }
}
