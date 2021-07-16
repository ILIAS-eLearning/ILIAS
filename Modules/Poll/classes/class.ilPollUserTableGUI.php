<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for poll users
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollUserTableGUI extends ilTable2GUI
{
    protected $answer_ids; // [array]
    
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("ilobjpollusr");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($lng->txt("login"), "login");
        $this->addColumn($lng->txt("lastname"), "lastname");
        $this->addColumn($lng->txt("firstname"), "firstname");
        
        foreach ($this->getParentObject()->object->getAnswers() as $answer) {
            $this->answer_ids[] = (int) ($answer["id"] ?? 0);
            $this->addColumn((string) ($answer["answer"] ?? ''), "answer" . (int) ($answer["id"] ?? 0));
        }
                
        $this->getItems($this->answer_ids);
        
        $this->setTitle($this->lng->txt("poll_question") . ": \"" .
            $this->getParentObject()->object->getQuestion() . "\"");
    
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.user_row.html", "Modules/Poll");
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");
        
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
    }
    
    protected function getItems(array $a_answer_ids)
    {
        $data = array();
        
        foreach ($this->getParentObject()->object->getVotesByUsers() as $user_id => $vote) {
            $answers = (array) ($vote["answers"] ?? array());
            unset($vote["answers"]);
            
            foreach ($a_answer_ids as $answer_id) {
                $vote["answer" . $answer_id] = in_array($answer_id, $answers);
            }
            
            $data[] = $vote;
        }
        
        $this->setData($data);
    }
    
    protected function fillRow($a_set)
    {
        $this->tpl->setCurrentBlock("answer_bl");
        foreach ($this->answer_ids as $answer_id) {
            if ($a_set["answer" . $answer_id]) {
                $this->tpl->setVariable("ANSWER", '<img src="' . ilUtil::getImagePath("icon_ok.svg") . '" />');
            } else {
                $this->tpl->setVariable("ANSWER", "&nbsp;");
            }
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("LOGIN", (string) ($a_set["login"] ?? ''));
        $this->tpl->setVariable("FIRSTNAME", (string) ($a_set["firstname"] ?? ''));
        $this->tpl->setVariable("LASTNAME", (string) ($a_set["lastname"] ?? ''));
    }
    
    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn((string) ($a_set["login"] ?? ''));
        $a_csv->addColumn((string) ($a_set["lastname"] ?? ''));
        $a_csv->addColumn((string) ($a_set["firstname"] ?? ''));
        foreach ($this->answer_ids as $answer_id) {
            if ($a_set["answer" . $answer_id]) {
                $a_csv->addColumn(true);
            } else {
                $a_csv->addColumn(false);
            }
        }
        $a_csv->addRow();
    }
    
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, (string) ($a_set["login"] ?? ''));
        $a_excel->setCell($a_row, 1, (string) ($a_set["lastname"] ?? ''));
        $a_excel->setCell($a_row, 2, (string) ($a_set["firstname"] ?? ''));
        
        $col = 2;
        foreach ($this->answer_ids as $answer_id) {
            if ($a_set["answer" . $answer_id]) {
                $a_excel->setCell($a_row, ++$col, true);
            } else {
                $a_excel->setCell($a_row, ++$col, false);
            }
        }
    }
}
