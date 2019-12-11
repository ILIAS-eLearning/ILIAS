<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Badge/classes/class.ilBadge.php");

/**
 * TableGUI class for badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgeTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $has_write; // [bool]
    protected $parent_type; // [string]
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_parent_obj_id, $a_has_write = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("bdgbdg");
        $this->has_write = (bool) $a_has_write;
        $this->parent_type = ilObject::_lookupType($a_parent_obj_id);
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
            
        $this->setLimit(9999);
        
        $this->setTitle($lng->txt("obj_bdga"));
                        
        if ($this->has_write) {
            $this->addColumn("", "", 1);
        }
        
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("type"), "type");
        $this->addColumn($lng->txt("active"), "active");
                
        if ($this->has_write) {
            $this->addColumn($lng->txt("action"), "");
            
            $lng->loadLanguageModule("content");
            $this->addMultiCommand("copyBadges", $lng->txt("cont_copy_to_clipboard"));
            $this->addMultiCommand("activateBadges", $lng->txt("activate"));
            $this->addMultiCommand("deactivateBadges", $lng->txt("deactivate"));
            $this->addMultiCommand("confirmDeleteBadges", $lng->txt("delete"));
            $this->setSelectAllCheckbox("id");
        }
            
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.badge_row.html", "Services/Badge");
        $this->setDefaultOrderField("title");
        
        $this->setFilterCommand("applyBadgeFilter");
        $this->setResetCommand("resetBadgeFilter");
        
        $this->initFilter();
                                
        $this->getItems($a_parent_obj_id);
    }
    
    public function initFilter()
    {
        $lng = $this->lng;
        
        $title = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("title"));
        $this->filter["title"] = $title->getValue();
        
        $handler = ilBadgeHandler::getInstance();
        $valid_types = $handler->getAvailableTypesForObjType($this->parent_type);
        if ($valid_types &&
            sizeof($valid_types) > 1) {
            $lng->loadLanguageModule("search");
                    
            $options = array("" => $lng->txt("search_any"));
            foreach ($valid_types as $id => $type) {
                $options[$id] = ilBadge::getExtendedTypeCaption($type);
            }
            asort($options);

            $type = $this->addFilterItemByMetaType("type", self::FILTER_SELECT, false, $lng->txt("type"));
            $type->setOptions($options);
            $this->filter["type"] = $type->getValue();
        }
    }
    
    public function getItems($a_parent_obj_id)
    {
        $data = array();
        
        include_once "Services/Badge/classes/class.ilBadgeRenderer.php";
        
        foreach (ilBadge::getInstancesByParentId($a_parent_obj_id, $this->filter) as $badge) {
            $data[] = array(
                "id" => $badge->getId(),
                "title" => $badge->getTitle(),
                "active" => $badge->isActive(),
                "type" => ($this->parent_type != "bdga")
                    ? ilBadge::getExtendedTypeCaption($badge->getTypeInstance())
                    : $badge->getTypeInstance()->getCaption(),
                "manual" => (!$badge->getTypeInstance() instanceof ilBadgeAuto),
                "renderer" => new ilBadgeRenderer(null, $badge)
            );
        }
        
        $this->setData($data);
    }
    
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if ($this->has_write) {
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        }
        
        $this->tpl->setVariable("PREVIEW", $a_set["renderer"]->getHTML());
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_TYPE", $a_set["type"]);
        $this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
        
        if ($this->has_write) {
            include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
            $actions = new ilAdvancedSelectionListGUI();
            $actions->setListTitle($lng->txt("actions"));
            
            if ($a_set["manual"] &&
                $a_set["active"]) {
                $ilCtrl->setParameter($this->getParentObject(), "bid", $a_set["id"]);
                $ilCtrl->setParameter($this->getParentObject(), "tgt", "bdgl");
                $url = $ilCtrl->getLinkTarget($this->getParentObject(), "awardBadgeUserSelection");
                $ilCtrl->setParameter($this->getParentObject(), "bid", "");
                $ilCtrl->setParameter($this->getParentObject(), "tgt", "");
                $actions->addItem($lng->txt("badge_award_badge"), "", $url);
            }
            
            $ilCtrl->setParameter($this->getParentObject(), "bid", $a_set["id"]);
            $url = $ilCtrl->getLinkTarget($this->getParentObject(), "editBadge");
            $ilCtrl->setParameter($this->getParentObject(), "bid", "");
            $actions->addItem($lng->txt("edit"), "", $url);
            
            $this->tpl->setVariable("ACTIONS", $actions->getHTML());
        }
    }
}
