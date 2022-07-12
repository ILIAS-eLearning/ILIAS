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
 * Explorer View for Workspace Folders
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilWorkspaceFolderExplorer extends ilExplorer
{
    protected bool $show_details;
    protected bool $enablesmallmode;
    protected ilCtrl $ctrl;
    public int $user_id;
    public array $allowed_types;

    public function __construct(
        string $a_target,
        int $a_user_id
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        parent::__construct($a_target);
        $this->tree = new ilWorkspaceTree($a_user_id);
        $this->root_id = $this->tree->readRootId();
        $this->user_id = $a_user_id;
        $this->allowed_types = array('wfld', 'wsrt');
        $this->enablesmallmode = false;
    }

    public function setEnableSmallMode(bool $a_enablesmallmode) : void
    {
        $this->enablesmallmode = $a_enablesmallmode;
    }

    public function getEnableSmallMode() : bool
    {
        return $this->enablesmallmode;
    }


    public function setOutput($a_parent_id, int $a_depth = 1, int $a_obj_id = 0, bool $a_highlighted_subtree = false) : void
    {
        static $counter = 0;
        $parent_index = 0;
        if ($objects = $this->tree->getChilds($a_parent_id, "type DESC,title")) {
            $tab = ++$a_depth - 2;

            foreach ($objects as $key => $object) {
                if (!in_array($object["type"], $this->allowed_types)) {
                    continue;
                }

                //ask for FILTER
                if ($object["child"] != $this->root_id) {
                    //$data = $this->tree->getParentNodeData($object["child"]);
                    $parent_index = $this->getIndex($object);
                }

                $this->format_options["$counter"]["parent"] = $object["parent"];
                $this->format_options["$counter"]["child"] = $object["child"];
                $this->format_options["$counter"]["title"] = $object["title"];
                $this->format_options["$counter"]["description"] = $object["description"];
                $this->format_options["$counter"]["type"] = $object["type"];
                $this->format_options["$counter"]["depth"] = $tab;
                $this->format_options["$counter"]["container"] = false;
                $this->format_options["$counter"]["visible"] = true;

                // Create prefix array
                for ($i = 0; $i < $tab; ++$i) {
                    $this->format_options["$counter"]["tab"][] = 'blank';
                }
                // only if parent is expanded and visible, object is visible
                if ($object["child"] != $this->root_id and (!in_array($object["parent"], $this->expanded)
                                                          or !$this->format_options[$parent_index]["visible"])) {
                    $this->format_options["$counter"]["visible"] = false;
                }

                // if object exists parent is container
                if ($object["child"] != $this->root_id) {
                    $this->format_options["$parent_index"]["container"] = true;

                    if (in_array($object["parent"], $this->expanded)) {
                        $this->format_options["$parent_index"]["tab"][($tab - 2)] = 'minus';
                    } else {
                        $this->format_options["$parent_index"]["tab"][($tab - 2)] = 'plus';
                    }
                }

                ++$counter;

                // Recursive
                $this->setOutput($object["child"], $a_depth);
            } //foreach
        } //if
    } //function

    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $title = $lng->txt("personal_resources");
        
        $tpl->setCurrentBlock("icon");
        $tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath("icon_wsrt.svg"));
        $tpl->setVariable("TXT_ALT_IMG", $title);
        $tpl->parseCurrentBlock();
        
        $tpl->setCurrentBlock("link");
        $tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($this->root_id, "wsrt"));
        $tpl->setVariable("TITLE", $title);
        
        // highlighting
        $style_class = $this->getNodeStyleClass($this->root_id, "wsrt");
        if ($style_class != "") {
            $tpl->setVariable("A_CLASS", ' class="' . $style_class . '" ');
        }
        
        $tpl->parseCurrentBlock();
    }

    /**
     * Get expanded
     * @param
     * @return
     */
    protected function getExpanded() : array
    {
        $expanded = ilSession::get($this->expand_variable);
        if (!is_array($expanded)) {
            $expanded = [];
        }
        return $expanded;
    }

    public function setExpand($a_node_id) : void
    {
        if ($a_node_id == "") {
            $a_node_id = $this->root_id;
        }

        $expanded = $this->getExpanded();
        if ($a_node_id > 0 && !in_array($a_node_id, $this->getExpanded())) {
            $expanded[] = $a_node_id;
        }
        if ($a_node_id < 0) {
            $key = array_keys($this->getExpanded(), -(int) $a_node_id);
            unset($expanded[$key[0]]);
        }
        ilSession::set($this->expand_variable, $expanded);
        $this->expanded = $this->getExpanded();
    }

    public function buildLinkTarget($a_node_id, string $a_type) : string
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_type) {
            case "wsrt":
                $ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "wsp_id", $a_node_id);
                return $ilCtrl->getLinkTargetByClass("ilobjworkspacerootfoldergui", "");
                
            case "wfld":
                $ilCtrl->setParameterByClass("ilobjworkspacefoldergui", "wsp_id", $a_node_id);
                return $ilCtrl->getLinkTargetByClass("ilobjworkspacefoldergui", "");
            
            default:
                return "";
        }
    }

    public function buildFrameTarget(string $a_type, $a_child = 0, $a_obj_id = 0) : string
    {
        return '';
    }

    public function setAllowedTypes(array $a_types) : void
    {
        $this->allowed_types = $a_types;
    }

    public function setShowDetails(bool $s_details) : void
    {
        $this->show_details = $s_details;
    }

    public function buildDescription(string $a_desc, $a_id, string $a_type) : string
    {
        if ($this->show_details == 'y' && !empty($a_desc)) {
            return $a_desc;
        } else {
            return "";
        }
    }
    
    public function getImageAlt(string $a_default_text, string $a_type = "", $a_obj_id = "") : string
    {
        $lng = $this->lng;
        
        return $lng->txt("icon") . " " . $lng->txt($a_type);
    }

    public function hasFolders($a_node_id)
    {
        return sizeof($this->tree->getChildsByType($a_node_id, "wfld"));
    }

    public function getParentNode($a_node_id)
    {
        return $this->tree->getParentId($a_node_id);
    }
    
    public function getOutput() : string
    {
        $tpl = $this->tpl;
        
        $html = parent::getOutput();
        $tpl->setBodyClass("std");
        return $html;
    }
}
