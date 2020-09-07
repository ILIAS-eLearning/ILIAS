<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for system style to category assignments
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSysStyleCatAssignmentTableGUI extends ilTable2GUI
{
    /**
     * ilSysStyleCatAssignmentTableGUI constructor.
     * @param int $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $skin_id
     * @param $style_id
     * @param $sub_style_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $skin_id, $style_id, $sub_style_id)
    {
        global $DIC;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->skin_id = $skin_id;
        $this->style_id = $style_id;
        $this->sub_style_id = $sub_style_id;

        
        $this->getStyleCatAssignments();
        $this->setTitle($this->lng->txt("sty_cat_assignments"));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("obj_cat"));

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.sty_cat_ass_row.html", "Services/Style/System");

        $this->addMultiCommand("deleteAssignments", $DIC->language()->txt("remove_assignment"));
    }


    public function getStyleCatAssignments()
    {
        $this->setData(ilSystemStyleSettings::getSubStyleCategoryAssignments($this->skin_id, $this->style_id, $this->sub_style_id));
    }
    
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("REF_ID", $a_set["ref_id"]);
        $this->tpl->setVariable(
            "CATEGORY",
            ilObject::_lookupTitle(ilObject::_lookupObjId($a_set["ref_id"]))
        );
    }
}
