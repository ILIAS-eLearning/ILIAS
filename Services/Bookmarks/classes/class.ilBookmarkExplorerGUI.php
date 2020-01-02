<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Bookmark explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesBookmarks
 */
class ilBookmarkExplorerGUI extends ilTreeExplorerGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id = 0)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        
        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }
        include_once("./Services/Bookmarks/classes/class.ilBookmarkTree.php");
        $tree = new ilBookmarkTree($a_user_id);
        parent::__construct("bm_exp", $a_parent_obj, $a_parent_cmd, $tree);

        $this->setTypeWhiteList(array("bmf", "dum"));
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setOrderField("title");
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
        
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $lng->txt("bookmarks");
        }
                
        return $a_node["title"];
    }
    
    /**
     * Get node icon
     *
     * @param array
     * @return
     */
    public function getNodeIcon($a_node)
    {
        $icon = ilUtil::getImagePath("icon_" . $a_node["type"] . ".svg");
        
        return $icon;
    }
    
    /**
     * Get node icon alt attribute
     *
     * @param mixed $a_node node object/array
     * @return string image alt attribute
     */
    public function getNodeIconAlt($a_node)
    {
        $lng = $this->lng;
        
        return $lng->txt("icon") . " " . $lng->txt($a_node["type"]);
    }


    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeHighlighted($a_node)
    {
        if ($a_node["child"] == $_GET["bmf_id"] ||
            ($_GET["bmf_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }
    
    /**
     * Get href for node
     *
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_node["type"]) {
            // bookmark folder
            case "bmf":
            // dummy root
            case "dum":
                $ilCtrl->setParameterByClass("ilbookmarkadministrationgui", "bmf_id", $a_node["child"]);

                $ret = $ilCtrl->getLinkTargetByClass("ilbookmarkadministrationgui", "");
                if (isset($_GET['bm_link'])) {
                    $this->ctrl->setParameterByClass(
                        "ilbookmarkadministrationgui",
                        'bm_link',
                        urlencode(\ilUtil::stripSlashes($_GET['bm_link']))
                    );
                    if (isset($_GET['bm_title'])) {
                        $this->ctrl->setParameterByClass(
                            "ilbookmarkadministrationgui",
                            'bm_title',
                            urlencode(\ilUtil::stripSlashes($_GET['bm_title']))
                        );
                    }
                    $ret = $ilCtrl->getLinkTargetByClass("ilbookmarkadministrationgui", "newFormBookmark");
                }

                $ilCtrl->setParameterByClass("ilbookmarkadministrationgui", "bmf_id", $_GET["bmf_id"]);
                return $ret;
                break;
        }
    }
}
