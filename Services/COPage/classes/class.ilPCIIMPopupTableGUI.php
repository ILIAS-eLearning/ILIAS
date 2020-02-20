<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for content popup
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCIIMPopupTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;


    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_content_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("title"), "", "100%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.iim_popup_content_row.html",
            "Services/COPage"
        );
            
        $this->content_obj = $a_content_obj;
        $this->setData($this->content_obj->getPopups());
        $this->setLimit(0);
        
        $this->addMultiCommand("confirmPopupDeletion", $lng->txt("delete"));
        $this->addCommandButton("savePopups", $lng->txt("cont_save_all_titles"));
        
        $this->setTitle($lng->txt("cont_content_popups"));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("TID", $a_set["hier_id"] . ":" . $a_set["pc_id"]);
        $this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($a_set["title"]));
    }
}
