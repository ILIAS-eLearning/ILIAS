<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Badge/classes/class.ilBadgeHandler.php");

/**
 * TableGUI class for badge type listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgeTypesTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("bdgtps");
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
            
        $this->setLimit(9999);
        
        $this->setTitle($lng->txt("badge_types"));
        
        $lng->loadLanguageModule("cmps");

        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("name"), "name");
        $this->addColumn($lng->txt("cmps_component"), "comp");
        $this->addColumn($lng->txt("badge_manual"), "manual");
        $this->addColumn($lng->txt("badge_activity_badges"), "activity");
        $this->addColumn($lng->txt("active"), "inactive");
    
        if ((bool) $a_has_write) {
            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->addMultiCommand("activateTypes", $lng->txt("activate"));
            $this->addMultiCommand("deactivateTypes", $lng->txt("deactivate"));
        }
                    
        $this->setRowTemplate("tpl.type_row.html", "Services/Badge");
        $this->setDefaultOrderField("name");
        $this->setSelectAllCheckbox("id");
                
        $this->getItems();
    }
    
    public function getItems()
    {
        $data = array();
        
        $handler = ilBadgeHandler::getInstance();
        $inactive = $handler->getInactiveTypes();
        foreach ($handler->getComponents() as $component) {
            $provider = $handler->getProviderInstance($component);
            if ($provider) {
                foreach ($provider->getBadgeTypes() as $badge_obj) {
                    $id = $handler->getUniqueTypeId($component, $badge_obj);
                    
                    $data[] = array(
                        "id" => $id,
                        "comp" => $handler->getComponentCaption($component),
                        "name" => $badge_obj->getCaption(),
                        "manual" => (!$badge_obj instanceof ilBadgeAuto),
                        "active" => !in_array($id, $inactive),
                        "activity" => in_array("bdga", $badge_obj->getValidObjectTypes())
                    );
                }
            }
        }
        
        $this->setData($data);
        
        include_once "Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php";
    }
    
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_COMP", $a_set["comp"]);
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
        $this->tpl->setVariable("TXT_MANUAL", $a_set["manual"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
        $this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
        $this->tpl->setVariable("TXT_ACTIVITY", $a_set["activity"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
    }
}
