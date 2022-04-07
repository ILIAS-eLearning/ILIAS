<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\PersonalWorkspace\StandardGUIRequest;

/**
 * Explorer for selecting a personal workspace item
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWorkspaceExplorerGUI extends ilTreeExplorerGUI
{
    protected ilWorkspaceAccessHandler $access_handler;
    protected bool $link_to_node_class = false;
    protected string $custom_link_target = "";
    /**
     * @var object|string|null
     */
    protected $select_gui = null;
    protected string $select_cmd = "";
    protected string $select_par = "";
    protected array $selectable_types = array(); //PHP8-Review: Property ilWorkspaceExplorerGUI::$selectable_types type has no value type specified in iterable type array.
    protected bool $activate_highlighting = false;
    protected StandardGUIRequest $request;

    /**
     * ilWorkspaceExplorerGUI constructor.
     * @param object|array $a_parent_obj
     * @param string|object $a_select_gui
     */
    public function __construct(
        int $a_user_id,
        $a_parent_obj,
        string $a_parent_cmd,
        $a_select_gui,
        string $a_select_cmd,
        string $a_select_par = "sel_wsp_obj"
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        
        $this->select_gui = (is_object($a_select_gui))
            ? strtolower(get_class($a_select_gui))
            : $a_select_gui;
        $this->select_cmd = $a_select_cmd;
        $this->select_par = $a_select_par;

        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->tree = new ilWorkspaceTree($a_user_id);
        $this->root_id = $this->tree->readRootId();
        $this->access_handler = new ilWorkspaceAccessHandler($this->tree);

        parent::__construct("wsp_sel", $a_parent_obj, $a_parent_cmd, $this->tree);
        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setPathOpen($this->root_id);
        
        $this->setTypeWhiteList(array("wsrt", "wfld"));
    }
    
    public function setLinkToNodeClass(bool $a_val) : void
    {
        $this->link_to_node_class = $a_val;
    }
    
    public function getLinkToNodeClass() : bool
    {
        return $this->link_to_node_class;
    }
    
    public function setActivateHighlighting(bool $a_val) : void
    {
        $this->activate_highlighting = $a_val;
    }
    
    public function getActivateHighlighting() : bool
    {
        return $this->activate_highlighting;
    }

    public function setSelectableTypes(array $a_val) : void // PHP8-Review: Method has parameter with no value type specified in iterable type array.
    {
        $this->selectable_types = $a_val;
    }
    
    public function getSelectableTypes() : array // PHP8-Review: Method return type has no value type specified in iterable type array.
    {
        return $this->selectable_types;
    }

    public function setCustomLinkTarget(string $a_val) : void
    {
        $this->custom_link_target = $a_val;
    }

    public function getCustomLinkTarget() : string
    {
        return $this->custom_link_target;
    }

    public function getNodeHref($a_node) : string // PHP8-Review: Method has parameter $a_node with no value type specified.
    {
        if ($this->select_postvar != "") {
            return "";
        }
        if ($this->getCustomLinkTarget() != "") {
            return $this->getCustomLinkTarget() . "&" . $this->select_par . "=" . $a_node["child"];
        }

        $ilCtrl = $this->ctrl;

        $target_class = $this->select_gui;

        if ($this->getLinkToNodeClass()) {
            switch ($a_node["type"]) {
                case "wsrt":
                    $target_class = "ilobjworkspacerootfoldergui";
                    break;
                case "wfld":
                    $target_class = "ilobjworkspacefoldergui";
                    break;
            }
        }

        if (is_object($this->parent_obj)) {
            $target_path = [get_class($this->parent_obj)];
        } else {
            $target_path = $this->parent_obj;
        }
        $target_path = $target_path + [$target_class]; // PHP8-Review: Binary operation "+" between array|string and array{object|string|null} results in an error.
        $ilCtrl->setParameterByClass($target_class, $this->select_par, $a_node["child"]); //PHP8-Review: Parameter #1 $a_class of method ilCtrl::setParameterByClass() expects string, object|string|null given.
        $ret = $ilCtrl->getLinkTargetByClass($target_path, $this->select_cmd);
        $ilCtrl->setParameterByClass($target_class, $this->select_par, $this->request->getSelectPar());  //PHP8-Review: Parameter #1 $a_class of method ilCtrl::setParameterByClass() expects string, object|string|null given.

        return $ret;
    }

    public function getNodeContent($a_node) : string // PHP8-Review: Method has parameter $a_node with no value type specified.
    {
        $lng = $this->lng;

        if ($a_node["child"] == $this->tree->getRootId()) {
            return $lng->txt("personal_resources");
        }

        return $a_node["title"];
    }
    
    public function isNodeClickable($a_node) : bool // PHP8-Review: Method has parameter $a_node with no value type specified.
    {
        if (in_array($a_node["type"], $this->getSelectableTypes())) {
            return true;
        }
        return false;
    }

    protected function isNodeSelectable($a_node) : bool // PHP8-Review: Method has parameter $a_node with no value type specified.
    {
        if (in_array($a_node["type"], $this->getSelectableTypes())) {
            return true;
        }
        return false;
    }

    public function getNodeIcon($a_node) : string // PHP8-Review: Method has parameter $a_node with no value type specified.
    {
        $t = $a_node["type"];
        // PHP8-Review: 'in_array' can be replaced with comparison
        if (in_array($t, array("sktr"))) {
            return ilUtil::getImagePath("icon_skll.svg");
        }
        return ilUtil::getImagePath("icon_" . $t . ".svg");
    }

    public function isNodeHighlighted($a_node) : bool // PHP8-Review: Method has parameter $a_node with no value type specified.
    {
        $wsp_id = $this->request->getWspId();
        if ($this->getActivateHighlighting() &&
            ((int) $a_node["child"] == $wsp_id || $wsp_id == 0 && $a_node["child"] == $this->getRootId())) {
            return true;
        }
        return false;
    }
}
