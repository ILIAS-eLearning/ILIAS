<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for user badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgePersonalTableGUI extends ilTable2GUI
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
     * @var ilTemplate
     */
    protected $tpl;

    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id = null)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        
        $this->setId("bdgprs");
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("badge_personal_badges"));
                
        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("object"), "parent_title");
        $this->addColumn($lng->txt("badge_issued_on"), "issued_on");
        $this->addColumn($lng->txt("badge_in_profile"), "active");
        $this->addColumn($lng->txt("actions"), "");

        if (ilBadgeHandler::getInstance()->isObiActive()) {

            
            // :TODO: use local copy instead?
            $tpl->addJavascript("https://backpack.openbadges.org/issuer.js", false);
            
            $tpl->addJavascript("Services/Badge/js/ilBadge.js");
            $tpl->addOnLoadCode('il.Badge.setUrl("' .
                $ilCtrl->getLinkTarget($this->getParentObject(), "addtoBackpack", "", true, false) .
            '")');
        }
        
        $this->setDefaultOrderField("title");
        
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.personal_row.html", "Services/Badge");
                
        $this->addMultiCommand("activate", $lng->txt("badge_add_to_profile"));
        $this->addMultiCommand("deactivate", $lng->txt("badge_remove_from_profile"));
        if (ilBadgeHandler::getInstance()->isObiActive()) {
            $this->addMultiCommand("addToBackpackMulti", $lng->txt("badge_add_to_backpack"));
        }
        $this->setSelectAllCheckbox("badge_id");
        
        $this->getItems($a_user_id);
    }
    
    public function initFilters(array $a_parents)
    {
        $lng = $this->lng;
        
        $title = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("title"));
        $this->filter["title"] = $title->getValue();
        
        $lng->loadLanguageModule("search");
                        
        $options = array(
            "" => $lng->txt("search_any"),
            "-1" => $lng->txt("none")
        );
        asort($a_parents);
        
        $obj = $this->addFilterItemByMetaType("obj", self::FILTER_SELECT, false, $lng->txt("object"));
        $obj->setOptions($options + $a_parents);
        $this->filter["obj"] = $obj->getValue();
    }
    
    public function getItems($a_user_id)
    {
        $lng = $this->lng;
        
        $data = $filter_parent = array();
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        include_once "Services/Badge/classes/class.ilBadgeRenderer.php";
        foreach (ilBadgeAssignment::getInstancesByUserId($a_user_id) as $ass) {
            $badge = new ilBadge($ass->getBadgeId());
            
            $parent = null;
            if ($badge->getParentId()) {
                $parent = $badge->getParentMeta();
                if ($parent["type"] == "bdga") {
                    $parent = null;
                } else {
                    $filter_parent[$parent["id"]] =
                        "(" . $lng->txt($parent["type"]) . ") " . $parent["title"];
                }
            }
            
            $data[] = array(
                "id" => $badge->getId(),
                "title" => $badge->getTitle(),
                "image" => $badge->getImagePath(),
                "issued_on" => $ass->getTimestamp(),
                "parent_title" => $parent ? $parent["title"] : null,
                "parent" => $parent,
                "active" => (bool) $ass->getPosition(),
                "renderer" => new ilBadgeRenderer($ass)
            );
        }
            
        $this->initFilters($filter_parent);
        
        if ($this->filter["title"]) {
            foreach ($data as $idx => $row) {
                if (!stristr($row["title"], $this->filter["title"])) {
                    unset($data[$idx]);
                }
            }
        }
        
        if ($this->filter["obj"]) {
            foreach ($data as $idx => $row) {
                if ($this->filter["obj"] > 0) {
                    if (!$row["parent"] || $row["parent"]["id"] != $this->filter["obj"]) {
                        unset($data[$idx]);
                    }
                } else {
                    if ($row["parent"]) {
                        unset($data[$idx]);
                    }
                }
            }
        }
                
        $this->setData($data);
    }
    
    public function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("PREVIEW", $a_set["renderer"]->getHTML());
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_ISSUED_ON", ilDatePresentation::formatDate(new ilDateTime($a_set["issued_on"], IL_CAL_UNIX)));
        $this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
        
        if ($a_set["parent"]) {
            $this->tpl->setVariable("TXT_PARENT", $a_set["parent_title"]);
            $this->tpl->setVariable(
                "SRC_PARENT",
                ilObject::_getIcon($a_set["parent"]["id"], "big", $a_set["parent"]["type"])
            );
        }

        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle("");

        $ilCtrl->setParameter($this->getParentObject(), "badge_id", $a_set["id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), $a_set["active"]
            ? "deactivate"
            : "activate");
        $ilCtrl->setParameter($this->getParentObject(), "badge_id", "");
        $actions->addItem($lng->txt(!$a_set["active"]
            ? "badge_add_to_profile"
            : "badge_remove_from_profile"), "", $url);
        
        if (ilBadgeHandler::getInstance()->isObiActive()) {
            $actions->addItem(
                $lng->txt("badge_add_to_backpack"),
                "",
                "",
                "",
                "",
                "",
                "",
                false,
                "il.Badge.publish(" . $a_set["id"] . ");"
            );
        }
        
        $this->tpl->setVariable("ACTIONS", $actions->getHTML());
    }
}
