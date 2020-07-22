<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");


class ilRoleDesktopItemSelector extends ilExplorer
{

    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $role_desk_obj = null;


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
    public function __construct($a_target, $role_desk_item_obj)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->ctrl = $ilCtrl;

        $this->role_desk_obj = &$role_desk_item_obj;

        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";

        $this->setSessionExpandVariable("role_desk_item_link_expand");

        $this->addFilter("adm");
        $this->addFilter("rolf");
        #$this->addFilter("chat");
        #$this->addFilter('fold');

        $this->setFilterMode(IL_FM_NEGATIVE);
        $this->setFiltered(true);
        
        $this->setTitleLength(ilObject::TITLE_LENGTH);
    }

    public function buildLinkTarget($a_node_id, $a_type)
    {
        $this->ctrl->setParameterByClass('ilobjrolegui', 'item_id', $a_node_id);
        return $this->ctrl->getLinkTargetByClass('ilobjrolegui', 'assignDesktopItem');
    }

    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        return '';
    }

    public function isClickable($a_type, $a_ref_id = 0)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        return $rbacsystem->checkAccess('write', $a_ref_id) and !$this->role_desk_obj->isAssigned($a_ref_id);
    }

    public function showChilds($a_ref_id)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if ($a_ref_id) {
            return $rbacsystem->checkAccess('read', $a_ref_id);
        }
        return true;
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
        global $DIC;

        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

        $tpl->setCurrentBlock("text");
        $tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
        $tpl->parseCurrentBlock();

        //$tpl->setCurrentBlock("row");
        //$tpl->parseCurrentBlock();

        $this->output[] = $tpl->get();
    }
} // END class ilObjectSelector
