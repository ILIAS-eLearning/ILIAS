<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Explorer for selecting a personal workspace item
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 */
class ilWorkspaceExplorerGUI extends ilTreeExplorerGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var bool
     */
    protected $link_to_node_class = false;

    /**
     * @var string
     */
    protected $custom_link_target = "";

    /**
     * @var null|object
     */
    protected $select_gui = null;

    /**
     * @var string
     */
    protected $select_cmd = "";

    /**
     * @var string
     */
    protected $select_par = "";

    /**
     * @var array
     */
    protected $selectable_types = array();

    /**
     * @var bool
     */
    protected $activate_highlighting = false;

    /**
     * Constructor
     */
    public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd, $a_select_gui, $a_select_cmd, $a_select_par = "sel_wsp_obj")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        
        $this->select_gui = (is_object($a_select_gui))
            ? strtolower(get_class($a_select_gui))
            : $a_select_gui;
        $this->select_cmd = $a_select_cmd;
        $this->select_par = $a_select_par;

        $this->tree = new ilWorkspaceTree($a_user_id);
        $this->root_id = $this->tree->readRootId();
        $this->access_handler = new ilWorkspaceAccessHandler($this->tree);

        parent::__construct("wsp_sel", $a_parent_obj, $a_parent_cmd, $this->tree);
        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setPathOpen($this->root_id);
        
        $this->setTypeWhiteList(array("wsrt", "wfld"));
    }
    
    /**
     * Set link to node class
     *
     * @param bool $a_val link to gui class of node
     */
    public function setLinkToNodeClass($a_val)
    {
        $this->link_to_node_class = $a_val;
    }
    
    /**
     * Get link to node class
     *
     * @return bool link to gui class of node
     */
    public function getLinkToNodeClass()
    {
        return $this->link_to_node_class;
    }
    
    /**
     * Set activate highlighting
     *
     * @param bool $a_val activate highlighting
     */
    public function setActivateHighlighting($a_val)
    {
        $this->activate_highlighting = $a_val;
    }
    
    /**
     * Get activate highlighting
     *
     * @return bool activate highlighting
     */
    public function getActivateHighlighting()
    {
        return $this->activate_highlighting;
    }

    /**
     * Set selectable types
     *
     * @param array $a_val selectable types
     */
    public function setSelectableTypes($a_val)
    {
        $this->selectable_types = $a_val;
    }
    
    /**
     * Get selectable types
     *
     * @return array selectable types
     */
    public function getSelectableTypes()
    {
        return $this->selectable_types;
    }

    /**
     * Set custom link target
     *
     * @param string $a_val custom link target
     */
    public function setCustomLinkTarget($a_val)
    {
        $this->custom_link_target = $a_val;
    }

    /**
     * Get custom link target
     *
     * @return string custom link target
     */
    public function getCustomLinkTarget()
    {
        return $this->custom_link_target;
    }

    /**
     * Get href for node
     *
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node)
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
        $target_path = $target_path + [$target_class];
        $ilCtrl->setParameterByClass($target_class, $this->select_par, $a_node["child"]);
        //$ret = $ilCtrl->getLinkTargetByClass(["ilPersonalWorkspaceGUI", $target_class], $this->select_cmd);
        $ret = $ilCtrl->getLinkTargetByClass($target_path, $this->select_cmd);
        $ilCtrl->setParameterByClass($target_class, $this->select_par, $_GET[$this->select_par]);

        //$ret = ILIAS_HTTP_PATH . "/" . $ret;

        return $ret;
    }

    /**
     * Get node content
     *
     * @param array
     * @return
     */
    public function getNodeContent($a_node)
    {
        $lng = $this->lng;

        if ($a_node["child"] == $this->tree->getRootId()) {
            return $lng->txt("personal_resources");
        }

        return $a_node["title"];
    }
    
    /**
     * Is clickable
     *
     * @param
     * @return
     */
    public function isNodeClickable($a_node)
    {
        if (in_array($a_node["type"], $this->getSelectableTypes())) {
            return true;
        }
        return false;
    }

    /**
     * Is selectable
     *
     * @param
     * @return
     */
    public function isNodeSelectable($a_node)
    {
        if (in_array($a_node["type"], $this->getSelectableTypes())) {
            return true;
        }
        return false;
    }

    /**
     * get image path (may be overwritten by derived classes)
     */
    public function getNodeIcon($a_node)
    {
        $t = $a_node["type"];
        if (in_array($t, array("sktr"))) {
            return ilUtil::getImagePath("icon_skll.svg");
        }
        return ilUtil::getImagePath("icon_" . $t . ".svg");
    }

    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node highlighted true/false
     */
    public function isNodeHighlighted($a_node)
    {
        if ($this->getActivateHighlighting() &&
            ($a_node["child"] == $_GET["wsp_id"] || $_GET["wsp_id"] == "" && $a_node["child"] == $this->getRootId())) {
            return true;
        }
        return false;
    }
}
