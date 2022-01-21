<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Select file for being added into file lists
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
    
    public function buildLinkTarget($a_node_id, string $a_type) : string
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
    public function isClickable(string $a_type, $a_ref_id = 0) : bool
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
    public function showChilds($a_parent_id) : bool
    {
        $ilAccess = $this->access;

        if ($a_parent_id == 0) {
            return true;
        }

        if ($ilAccess->checkAccess("read", "", $a_parent_id)) {
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
    * @return    void
     */
    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option) : void
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
