<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* TableGUI class for
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup Services
*/
class ilTrashTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->obj_definition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $this->ref_id = $a_ref_id;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        //$this->setTitle($lng->txt(""));
        
        $this->addColumn($this->lng->txt(""), "", "1", 1);
        $this->addColumn($this->lng->txt("type"), "", "1");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("last_change"), "last_update");
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.trash_list_row.html", "Services/Repository");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->setSelectAllCheckbox("trash_id[]");

        $this->addMultiCommand("undelete", $lng->txt("btn_undelete"));
        $this->addMultiCommand("confirmRemoveFromSystem", $lng->txt("btn_remove_system"));
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;

        $img = ilObject::_getIcon($a_set["obj_id"], "small", $a_set["type"]);
        if (strlen($img) > 0) {
            $alt = ($objDefinition->isPlugin($a_set["type"]))
                ? $lng->txt("icon") . " " . ilObjectPlugin::lookupTxtById($a_set["type"], "obj_" . $a_set["type"])
                : $lng->txt("icon") . " " . $lng->txt("obj_" . $a_set["type"]);

            $this->tpl->setVariable("IMG_TYPE", ilUtil::img($img, $alt));
        }
        $this->tpl->setVariable("ID", $a_set["ref_id"]);
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_LAST_CHANGE", $a_set["last_update"]);
    }
}
