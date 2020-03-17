<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for survey question materials
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyMaterialsTableGUI extends ilTable2GUI
{
    private $counter;
    private $write_access;
    
    public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->write_access = $a_write_access;
        $this->counter = 1;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->setFormName('evaluation_all');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($lng->txt("type"), "type", "");
        $this->addColumn($lng->txt("material"), "material", "");
        $this->setTitle($this->lng->txt('materials'));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.il_svy_qpl_material_row.html", "Modules/SurveyQuestionPool");
        $this->setPrefix('idx');
        $this->setSelectAllCheckbox('idx');
        $this->disable('sort');
        $this->enable('header');

        if ($this->write_access) {
            $this->addMultiCommand('deleteMaterial', $this->lng->txt('remove'));
        }
    }
    
    /**
    * Fill data row
    */
    protected function fillRow($data)
    {
        $this->tpl->setVariable("TYPE", $data['type']);
        $this->tpl->setVariable("TITLE", $data['title']);
        $this->tpl->setVariable("HREF", $data['href']);
        $this->tpl->setVariable("CHECKBOX_VALUE", $this->counter - 1);
        $this->tpl->setVariable("COUNTER", $this->counter++);
    }
}
