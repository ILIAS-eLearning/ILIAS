<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
* Class Mail Explorer
* class for explorer view for mailboxes
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/
class ilMailExplorer extends ilTreeExplorerGUI
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * ilMailExplorer constructor.
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param $a_user_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
    {
        global $DIC;

        $this->lng  = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->tree = new ilTree($a_user_id);
        $this->tree->setTableNames('mail_tree', 'mail_obj_data');

        parent::__construct("mail_exp", $a_parent_obj, $a_parent_cmd, $this->tree);
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setOrderField("title,m_type");
    }
    
    public function getNodeContent($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->lng->txt("mail_folders");
        }
        
        if ($a_node["depth"] < 3) {
            return $this->lng->txt("mail_" . $a_node["title"]);
        }
        
        return $a_node["title"];
    }
    
    public function getNodeIcon($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return ilUtil::getImagePath("icon_mail.svg");
        } else {
            $icon_type = ($a_node["m_type"] == "user_folder")
                ? "local"
                : $a_node["m_type"];
            return ilUtil::getImagePath("icon_" . $icon_type . ".svg");
        }
    }
    
    public function getNodeIconAlt($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->lng->txt("icon") . " " . $this->lng->txt("mail_folders");
        } else {
            return $this->lng->txt("icon") . " " . $this->lng->txt($a_node["m_type"]);
        }
    }
        
    public function getNodeHref($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $a_node["child"] = 0;
        }

        $this->ctrl->setParameterByClass('ilMailFolderGUI', 'mobj_id', $a_node['child']);
        $href = $this->ctrl->getLinkTargetByClass('ilMailFolderGUI');
        $this->ctrl->clearParametersByClass('ilMailFolderGUI');

        return $href;
    }
    
    public function isNodeHighlighted($a_node)
    {
        if ($a_node["child"] == $_GET["mobj_id"] ||
            ($_GET["mobj_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }
}
