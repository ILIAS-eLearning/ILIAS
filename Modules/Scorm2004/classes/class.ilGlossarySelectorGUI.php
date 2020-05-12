<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/**
* Select file for being added into file lists
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilGlossarySelectorGUI extends ilExplorer
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_target, $a_par_class = "")
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();

        $this->ctrl = $ilCtrl;
        $this->parent_class = $a_par_class;
        parent::__construct($a_target);
    }

    public function setSelectableTypes($a_types)
    {
        $this->selectable_types = $a_types;
    }
    
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    
    public function buildLinkTarget($a_node_id, $a_type)
    {
        $ilCtrl = $this->ctrl;
        
        //$ilCtrl->setParameterByClass($this->parent_class, "subCmd", "selectGlossary");
        $ilCtrl->setParameterByClass($this->parent_class, "glo_ref_id", $a_node_id);
        $link = $ilCtrl->getLinkTargetByClass($this->parent_class, "selectGlossary");

        return $link;
    }
    

    /**
    * Item clickable?
    */
    public function isClickable($a_type, $a_ref_id = 0)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        
        if ($a_type == "glo" &&
            $ilAccess->checkAccess("read", "", $a_ref_id)) {
            return true;
        }
        return false;
    }

    /**
    * Show childs y/n?
    */
    public function showChilds($a_ref_id)
    {
        $ilAccess = $this->access;

        if ($a_ref_id == 0) {
            return true;
        }

        if ($ilAccess->checkAccess("read", "", $a_ref_id)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    * @return	string
    */
    public function formatHeader($tpl, $a_obj_id, $a_option)
    {
        $lng = $this->lng;
        
        $tpl->setCurrentBlock("icon");
        $tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath("icon_root.svg"));
        $tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("text");
        $tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
        $tpl->parseCurrentBlock();

        //$tpl->setCurrentBlock("row");
        //$tpl->parseCurrentBlock();
        
        $tpl->touchBlock("element");
    }
} // END class ilFileSelectorGUI
