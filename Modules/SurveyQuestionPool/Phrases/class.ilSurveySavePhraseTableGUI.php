<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*/
class ilSurveySavePhraseTableGUI extends ilTable2GUI
{
    protected $confirmdelete;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->confirmdelete = $confirmdelete;
    
        $this->setFormName('phrases');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("answer"), '', '');
        $this->addColumn($this->lng->txt("use_other_answer"), '', '');
        $this->addColumn($this->lng->txt("scale"), '', '');

        $this->setRowTemplate("tpl.il_svy_qpl_phrase_save_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->disable('sort');
        $this->disable('select_all');
        $this->enable('header');
    }

    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        $this->tpl->setVariable("ANSWER", $data["answer"]);
        $this->tpl->setVariable("OPEN_ANSWER", ($data["other"]) ? $this->lng->txt('yes') : $this->lng->txt('no'));
        $this->tpl->setVariable("SCALE", $data["scale"]);
    }
}
