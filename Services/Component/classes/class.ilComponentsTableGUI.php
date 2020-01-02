<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Component/classes/class.ilComponent.php");


/**
 * TableGUI class for components listing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesComponent
 */
class ilComponentsTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setId("cmpstblslt");
                
        $this->setTitle($lng->txt("cmps_slots"));

        $this->addColumn($lng->txt("cmps_service") . " / " . $lng->txt("cmps_module"), "subdir");
        $this->addColumn($lng->txt("cmps_plugin_slot"), "name");
        $this->addColumn($lng->txt("cmps_dir"), "dir");
        $this->addColumn($lng->txt("cmps_lang_prefix"), "lang");
        $this->addColumn($lng->txt("action"), "lang");

        $this->setDefaultOrderField("name");
        $this->setLimit(10000);
                            
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_row_component.html",
            "Services/Component"
        );
        
        $this->getComponents();
    }
    
    /**
    * Get pages for list.
    */
    public function getComponents()
    {
        $data = array();

        include_once("./Services/Component/classes/class.ilService.php");
        foreach (ilService::getAvailableCoreServices() as $obj) {
            foreach (ilComponent::lookupPluginSlots(IL_COMP_SERVICE, $obj["subdir"]) as $slot) {
                $data[] = array(
                    "subdir" => $obj["subdir"],
                    "id" => $slot["id"],
                    "name" => $slot["name"],
                    "dir" => $slot["dir_pres"],
                    "lang" => $slot["lang_prefix"],
                    "ctype" => IL_COMP_SERVICE
                );
            }
        }

        include_once("./Services/Component/classes/class.ilModule.php");
        foreach (ilModule::getAvailableCoreModules() as $obj) {
            foreach (ilComponent::lookupPluginSlots(IL_COMP_MODULE, $obj["subdir"]) as $slot) {
                $data[] = array(
                    "subdir" => $obj["subdir"],
                    "id" => $slot["id"],
                    "name" => $slot["name"],
                    "dir" => $slot["dir_pres"],
                    "lang" => $slot["lang_prefix"],
                    "ctype" => IL_COMP_MODULE
                );
            }
        }

        $this->setData($data);
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        global $DIC;
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $this->tpl->setVariable("SLOT_NAME", $a_set["name"]);
        $this->tpl->setVariable("SLOT_ID", $a_set["id"]);
        $this->tpl->setVariable("SLOT_DIR", $a_set["dir"]);
        $this->tpl->setVariable("LANG_PREFIX", $a_set["lang"]);

        $ilCtrl->setParameter($this->parent_obj, "ctype", $a_set["ctype"]);
        $ilCtrl->setParameter($this->parent_obj, "cname", $a_set["subdir"]);
        $ilCtrl->setParameter($this->parent_obj, "slot_id", $a_set["id"]);
        $this->tpl->setVariable(
            "HREF_SHOW_SLOT",
            $ilCtrl->getLinkTarget($this->parent_obj, "showPluginSlotInfo")
        );
        $this->tpl->setVariable("TXT_SHOW_SLOT", $lng->txt("cmps_show_details"));

        $this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
    }
}
