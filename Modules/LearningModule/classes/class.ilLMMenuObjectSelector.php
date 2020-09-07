<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/**
* LM Menu Object Selector
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMMenuObjectSelector extends ilExplorer
{

    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $root_id;
    public $output;
    public $ctrl;
    public $selectable_type;
    public $ref_id;

    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_target, &$a_gui_obj)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();

        $this->ctrl = $ilCtrl;
        
        $this->gui_obj = $a_gui_obj;

        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";
        $this->setSessionExpandVariable("lm_menu_expand");
        $this->addFilter("rolf");
        $this->addFilter("adm");
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
        if (in_array($a_type, $this->selectable_types)) {
            $this->ctrl->setParameter($this->gui_obj, 'link_ref_id', $a_node_id);
            if ($_GET["menu_entry"] > 0) {
                return $this->ctrl->getLinkTarget($this->gui_obj, 'editMenuEntry');
            } else {
                return $this->ctrl->getLinkTarget($this->gui_obj, 'addMenuEntry');
            }
        }
    }

    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        return '';
    }

    public function isClickable($a_type, $a_ref_id = 0)
    {//return true;
        return in_array($a_type, $this->selectable_types) and $a_ref_id != $this->ref_id;
    }

    public function showChilds($a_ref_id)
    {
        $rbacsystem = $this->rbacsystem;

        if ($a_ref_id == 0) {
            return true;
        }

        if ($rbacsystem->checkAccess("read", $a_ref_id)) {
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
    public function formatHeader($a_tpl, $a_obj_id, $a_option)
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

        $tpl->setCurrentBlock("text");
        $tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
        $tpl->parseCurrentBlock();
        $this->output[] = $tpl->get();
    }
}
