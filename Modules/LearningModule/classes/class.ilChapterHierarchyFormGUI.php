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
 * This class represents a hierarchical form. These forms are used for
 * quick editing, where each node is represented by it's title.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilChapterHierarchyFormGUI extends ilHierarchyFormGUI
{
    protected ilObjUser $user;
    protected array $page_layouts;
    protected string $lang;
    protected string $lm_type;

    public function __construct(string $a_lm_type, string $a_lang = "-")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lm_type = $a_lm_type;

        $this->lang = ($a_lang == "")
            ? "-"
            : $a_lang;
        parent::__construct();
        $this->setCheckboxName("id");

        $this->page_layouts = ilPageLayout::activeLayouts(
            ilPageLayout::MODULE_LM
        );
    }
    
    public function getChildTitle(array $a_child) : string
    {
        if ($this->lang != "-") {
            $lmobjtrans = new ilLMObjTranslation($a_child["node_id"], $this->lang);
            return $lmobjtrans->getTitle();
        }
        return $a_child["title"];
    }

    /**
     * Get child info
     * @param array $a_child node array
     * @return string node title
     */
    public function getChildInfo(array $a_child) : string
    {
        if ($this->lang != "-") {
            return $a_child["title"];
        }
        return "";
    }

    /**
    * Get menu items
    */
    public function getMenuItems(array $a_node, int $a_depth, bool $a_first_child = false, ?array $a_next_sibling = null, ?array $a_childs = null) : array
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $cmds = array();

        if ($a_childs == null) {
            $a_childs = [];
        }
        
        if (!$a_first_child) {		// drop area of node
            if ($a_node["type"] == "pg" || ($a_node["type"] == "st" && count($a_childs) == 0 && $this->getMaxDepth() != 0)) {
                if ($a_node["type"] == "st") {
                    $cmds[] = array("text" => $lng->txt("cont_insert_page"), "cmd" => "insertPage", "multi" => 10,
                        "as_subitem" => true);
                    if (count($this->page_layouts) > 0) {
                        $cmds[] = array("text" => $lng->txt("cont_insert_pagelayout"), "cmd" => "insertTemplate", "multi" => 10,
                            "as_subitem" => true);
                    }
                    if ($ilUser->clipboardHasObjectsOfType("pg")) {
                        $cmds[] = array("text" => $lng->txt("cont_insert_page_from_clip"), "multi" => 0,
                            "cmd" => "insertPageClip", "as_subitem" => true);
                    }
                } else {
                    $cmds[] = array("text" => $lng->txt("cont_insert_page"), "cmd" => "insertPage", "multi" => 10);
                    if (count($this->page_layouts) > 0) {
                        $cmds[] = array("text" => $lng->txt("cont_insert_pagelayout"), "cmd" => "insertTemplate", "multi" => 10);
                    }
                    if ($ilUser->clipboardHasObjectsOfType("pg")) {
                        $cmds[] = array("text" => $lng->txt("cont_insert_page_from_clip"), "multi" => 0,
                            "cmd" => "insertPageClip");
                    }
                }
            }
            if ($a_node["type"] != "pg" && $this->getMaxDepth() != 0) {
                $cmds[] = array("text" => $lng->txt("cont_insert_subchapter"),
                    "cmd" => "insertSubchapter", "multi" => 10);
                if ($ilUser->clipboardHasObjectsOfType("st")) {
                    $cmds[] = array("text" => $lng->txt("cont_insert_subchapter_from_clip"), "multi" => 0,
                        "cmd" => "insertSubchapterClip");
                }
            }

            if ((($a_next_sibling["type"] ?? "") != "pg" && ($a_depth == 0 || ($a_next_sibling["type"] ?? "") == "st"))
                || $a_node["type"] == "st") {
                $cmds[] = array("text" => $lng->txt("cont_insert_chapter"),
                                "cmd" => "insertChapter",
                                "multi" => 10
                );
                if ($ilUser->clipboardHasObjectsOfType("st")) {
                    $cmds[] = array("text" => $lng->txt("cont_insert_chapter_from_clip"),
                                    "cmd" => "insertChapterClip", "multi" => 0
                    );
                }
            }
        } else {						// drop area before first child of node
            if ($a_node["type"] == "st" && $this->getMaxDepth() != 0) {
                $cmds[] = array("text" => $lng->txt("cont_insert_page"),
                    "cmd" => "insertPage", "multi" => 10);
                if (count($this->page_layouts) > 0) {
                    $cmds[] = array("text" => $lng->txt("cont_insert_pagelayout"), "cmd" => "insertTemplate", "multi" => 10);
                }
                if ($ilUser->clipboardHasObjectsOfType("pg")) {
                    $cmds[] = array("text" => $lng->txt("cont_insert_page_from_clip"),"multi" => 0,
                        "cmd" => "insertPageClip");
                }
            }
            if (($a_childs[0]["type"] ?? "") != "pg") {
                $cmds[] = array("text" => $lng->txt("cont_insert_chapter"),
                    "cmd" => "insertChapter", "multi" => 10);
                if ($ilUser->clipboardHasObjectsOfType("st")) {
                    $cmds[] = array("text" => $lng->txt("cont_insert_chapter_from_clip"),"multi" => 0,
                        "cmd" => "insertChapterClip");
                }
            }
        }

        return $cmds;
    }

    /**
    * Which nodes allow child nodes?
    */
    public function nodeAllowsChilds(array $a_node) : bool
    {
        if ($a_node["type"] == "pg") {
            return false;
        }
        return true;
    }

    /**
    * Makes nodes drag and drop content and targets.
    * @param	array $a_node node array
    */
    public function manageDragAndDrop(array $a_node, int $a_depth, bool $a_first_child = false, ?array $a_next_sibling = null, ?array $a_childs = null) : void
    {
        $lng = $this->lng;
        
        $this->makeDragContent($a_node["node_id"], "grp_" . $a_node["type"]);

        if ($a_childs == null) {
            $a_childs = [];
        }

        if (!$a_first_child) {
            if ($a_node["type"] == "pg" || ($a_node["type"] == "st" && count($a_childs) == 0 && $this->getMaxDepth() != 0)) {
                if ($a_node["type"] == "st") {
                    $this->makeDragTarget(
                        $a_node["node_id"],
                        "grp_pg",
                        $a_first_child,
                        true,
                        ""
                    );
                } else {
                    $this->makeDragTarget(
                        $a_node["node_id"],
                        "grp_pg",
                        $a_first_child,
                        false,
                        ""
                    );
                }
            }
            
            if ($a_node["type"] != "pg" && $this->getMaxDepth() != 0) {
                $this->makeDragTarget(
                    $a_node["node_id"],
                    "grp_st",
                    $a_first_child,
                    true,
                    $lng->txt("cont_insert_as_subchapter")
                );
            }

            if ($a_next_sibling) {
                if (($a_next_sibling["type"] != "pg" && ($a_depth == 0 || $a_next_sibling["type"] == "st"))
                    || $a_node["type"] == "st") {
                    $this->makeDragTarget(
                        $a_node["node_id"],
                        "grp_st",
                        $a_first_child,
                        false,
                        $lng->txt("cont_insert_as_chapter")
                    );
                }
            }
        } else {
            if ($a_node["type"] == "st" && $this->getMaxDepth() != 0) {
                $this->makeDragTarget(
                    $a_node["node_id"],
                    "grp_pg",
                    $a_first_child,
                    true
                );
            }
            if (($a_childs[0]["type"] ?? "") != "pg") {
                $this->makeDragTarget(
                    $a_node["node_id"],
                    "grp_st",
                    $a_first_child,
                    true
                );
            }
        }
    }

    /**
    * Get icon path for an item.
    *
    * @param	array $a_item	itema array
    * @return	string		icon path
    */
    public function getChildIcon(array $a_item) : string
    {
        $img = "icon_" . $a_item["type"] . ".svg";
        
        if ($a_item["type"] == "pg") {
            $lm_set = new ilSetting("lm");
            $active = ilLMPage::_lookupActive(
                $a_item["node_id"],
                $this->lm_type,
                $lm_set->get("time_scheduled_page_activation")
            );
                
            // is page scheduled?
            $img_sc = ($lm_set->get("time_scheduled_page_activation") &&
                ilLMPage::_isScheduledActivation($a_item["node_id"], $this->lm_type))
                ? "_sc"
                : "";
                
            $img = "icon_pg" . $img_sc . ".svg";

            if (!$active) {
                $img = "icon_pg_d" . $img_sc . ".svg";
            } else {
                $contains_dis = ilLMPage::_lookupContainsDeactivatedElements(
                    $a_item["node_id"],
                    $this->lm_type
                );
                if ($contains_dis) {
                    $img = "icon_pg_del" . $img_sc . ".svg";
                }
            }
        }
        return ilUtil::getImagePath($img);
    }

    /**
    * Get icon alt text
    *
    * @param	array $a_item	itema array
    * @return	string		icon alt text
    */
    public function getChildIconAlt(array $a_item) : string
    {
        $lng = $this->lng;
        

        if ($a_item["type"] == "pg") {
            $active = ilLMPage::_lookupActive($a_item["node_id"], $this->lm_type);

            if (!$active) {
                return $lng->txt("cont_page_deactivated");
            } else {
                $contains_dis = ilLMPage::_lookupContainsDeactivatedElements(
                    $a_item["node_id"],
                    $this->lm_type
                );
                if ($contains_dis) {
                    return $lng->txt("cont_page_deactivated_elements");
                }
            }
        }
        return ilUtil::getImagePath("icon_" . $a_item["type"] . ".svg");
    }

    /**
     * Get item commands
     * @param array $a_item
     * @return array
     */
    public function getChildCommands(array $a_item) : array
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $lm_class = "ilobjlearningmodulegui";
        
        $commands = array();
        switch ($a_item["type"]) {
            case "pg":
                $ilCtrl->setParameterByClass(
                    "illmpageobjectgui",
                    "obj_id",
                    $a_item["node_id"]
                );
                $commands[] = array("text" => $lng->txt("edit"),
                    "link" => $ilCtrl->getLinkTargetByClass(array($lm_class, "illmpageobjectgui"), "edit"));
                break;

            case "st":
                $ilCtrl->setParameterByClass(
                    "ilstructureobjectgui",
                    "obj_id",
                    $a_item["node_id"]
                );
                $commands[] = array("text" => $lng->txt("edit"),
                    "link" => $ilCtrl->getLinkTargetByClass(array($lm_class, "ilstructureobjectgui"), "view"));
                break;
        }
        
        return $commands;
    }
}
