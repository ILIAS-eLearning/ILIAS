<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * TableGUI class for badge user listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeUserTableGUI extends ilTable2GUI
{
    protected ilTree $tree;
    protected ?ilBadge $award_badge = null;
    protected bool $do_parent = false;
    protected array $filter = [];
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_parent_ref_id,
        ilBadge $a_award_bagde = null,
        int $a_parent_obj_id = null,
        int $a_restrict_badge_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("bdgusr");
        $this->award_badge = $a_award_bagde;
        $this->do_parent = false;
        
        $parent_type = ilObject::_lookupType($a_parent_ref_id, true);
        if (in_array($parent_type, array("grp", "crs"))) {
            $this->do_parent = (!$a_parent_obj_id && !$this->award_badge);
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);
            
        $this->setLimit(9999);
        
        if ($this->award_badge) {
            $this->setTitle($lng->txt("badge_award_badge") . ": " . $a_award_bagde->getTitle());
            $this->setDescription($a_award_bagde->getDescription());
            
            $this->addColumn("", "", 1);
            
            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->addMultiCommand("assignBadge", $lng->txt("badge_award_badge"));
            $this->addMultiCommand("confirmDeassignBadge", $lng->txt("badge_remove_badge"));
        } else {
            $parent = "";
            if ($a_parent_obj_id) {
                $title = ilObject::_lookupTitle($a_parent_obj_id);
                if (!$title) {
                    $title = ilObjectDataDeletionLog::get($a_parent_obj_id);
                    if ($title) {
                        $title = $title["title"];
                    }
                }
                if ($a_restrict_badge_id) {
                    $badge = new ilBadge($a_restrict_badge_id);
                    $title .= " - " . $badge->getTitle();
                }
                $parent = $title . ": ";
            }
            $this->setTitle($parent . $lng->txt("users"));
        }
        
        $this->addColumn($lng->txt("name"), "name");
        $this->addColumn($lng->txt("login"), "login");
        $this->addColumn($lng->txt("type"), "type");
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("badge_issued_on"), "issued");
        
        if ($this->do_parent) {
            $this->addColumn($lng->txt("object"), "parent_id");
        }
        
        $this->setDefaultOrderField("name");
                
        $this->setRowTemplate("tpl.user_row.html", "Services/Badge");
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setFilterCommand("apply" . ucfirst($this->getParentCmd()));
        $this->setResetCommand("reset" . ucfirst($this->getParentCmd()));

        $this->initFilter();
                
        $this->getItems($a_parent_ref_id, $this->award_badge, $a_parent_obj_id, $a_restrict_badge_id);
    }
    
    public function initFilter() : void
    {
        $lng = $this->lng;
        
        $name = $this->addFilterItemByMetaType("name", self::FILTER_TEXT, false, $lng->txt("name"));
        $this->filter["name"] = $name->getValue();
    }
    
    public function getItems(
        int $a_parent_ref_id,
        ilBadge $a_award_bagde = null,
        int $a_parent_obj_id = null,
        int $a_restrict_badge_id = null
    ) : void {
        $tree = $this->tree;
        $user_ids = null;
        
        $data = array();
                    
        if (!$a_parent_obj_id) {
            $a_parent_obj_id = ilObject::_lookupObjId($a_parent_ref_id);
        }
        
        // repository context: walk tree for available users
        if ($a_parent_ref_id) {
            $user_ids = ilBadgeHandler::getInstance()->getUserIds($a_parent_ref_id, $a_parent_obj_id);
        }

        $obj_ids = array($a_parent_obj_id);
        
        // add sub-items
        if ($this->do_parent) {
            foreach ($tree->getSubTree($tree->getNodeData($a_parent_ref_id)) as $node) {
                $obj_ids[] = $node["obj_id"];
            }
        }
        
        $badges = $assignments = array();
        foreach ($obj_ids as $obj_id) {
            foreach (ilBadge::getInstancesByParentId($obj_id) as $badge) {
                $badges[$badge->getId()] = $badge;
            }

            foreach (ilBadgeAssignment::getInstancesByParentId($obj_id) as $ass) {
                if ($a_restrict_badge_id &&
                    $a_restrict_badge_id !== $ass->getBadgeId()) {
                    continue;
                }
                
                // when awarding we only want to see the current badge
                if ($this->award_badge &&
                    $ass->getBadgeId() !== $this->award_badge->getId()) {
                    continue;
                }

                $assignments[$ass->getUserId()][] = $ass;
            }
        }

        // administration context: show only existing assignments
        if (!$user_ids) {
            $user_ids = array_keys($assignments);
        }

        $tmp["set"] = array();
        if (count($user_ids) > 0) {
            $uquery = new ilUserQuery();
            $uquery->setLimit(9999);
            $uquery->setUserFilter($user_ids);

            if ($this->filter["name"]) {
                $uquery->setTextFilter($this->filter["name"]);
            }

            $tmp = $uquery->query();
        }
        foreach ($tmp["set"] as $user) {
            // add 1 entry for each badge
            if (array_key_exists($user["usr_id"], $assignments)) {
                foreach ($assignments[$user["usr_id"]] as $user_ass) {
                    $idx = $user_ass->getBadgeId() . "-" . $user["usr_id"];
                    
                    $badge = $badges[$user_ass->getBadgeId()];
                    $parent = [];
                    if ($this->do_parent) {
                        $parent = $badge->getParentMeta();
                    }
                    
                    $data[$idx] = array(
                        "user_id" => $user["usr_id"],
                        "name" => $user["lastname"] . ", " . $user["firstname"],
                        "login" => $user["login"],
                        "type" => ilBadge::getExtendedTypeCaption($badge->getTypeInstance()),
                        "title" => $badge->getTitle(),
                        "issued" => $user_ass->getTimestamp(),
                        "parent_id" => $parent["id"] ?? 0,
                        "parent_meta" => $parent
                    );
                }
            }
            // no badge yet, add dummy entry (for manual awarding)
            elseif ($this->award_badge) {
                $idx = "0-" . $user["usr_id"];
                    
                $data[$idx] = array(
                    "user_id" => $user["usr_id"],
                    "name" => $user["lastname"] . ", " . $user["firstname"],
                    "login" => $user["login"],
                    "type" => "",
                    "title" => "",
                    "issued" => "",
                    "parent_id" => ""
                );
            }
        }
        
        $this->setData($data);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        
        if ($this->award_badge) {
            $this->tpl->setVariable("VAL_ID", $a_set["user_id"]);
        }
        
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
        $this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);
        $this->tpl->setVariable("TXT_TYPE", $a_set["type"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_ISSUED", $a_set["issued"]
            ? ilDatePresentation::formatDate(new ilDateTime($a_set["issued"], IL_CAL_UNIX))
            : "");
        
        if ($a_set["parent_id"]) {
            $parent = $a_set["parent_meta"];
            $this->tpl->setVariable("PARENT", $parent["title"]);
            $this->tpl->setVariable("PARENT_TYPE", $lng->txt("obj_" . $parent["type"]));
            $this->tpl->setVariable(
                "PARENT_ICON",
                ilObject::_getIcon((int) $parent["id"], "big", $parent["type"])
            );
        }
    }
}
