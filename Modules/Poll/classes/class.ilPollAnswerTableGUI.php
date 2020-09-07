<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for poll answers
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ModulesPoll
*/
class ilPollAnswerTableGUI extends ilTable2GUI
{
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("ilobjpollaw");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($lng->txt("poll_sortorder"), "pos");
        $this->addColumn($lng->txt("poll_answer"), "answer");
        $this->addColumn($lng->txt("poll_absolute"), "votes");
        $this->addColumn($lng->txt("poll_percentage"), "percentage");
        
        $total = $this->getItems();
        
        $this->setTitle($this->lng->txt("poll_question") . ": \"" .
            $a_parent_obj->object->getQuestion() . "\"");
        $this->setDescription(sprintf($lng->txt("poll_population"), $total));

        if ($total) {
            $this->addCommandButton("confirmDeleteAllVotes", $lng->txt("poll_delete_votes"));
        }
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.answer_row.html", "Modules/Poll");
        $this->setDefaultOrderField("pos");
        $this->setDefaultOrderDirection("asc");
                
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
    }
    
    public function numericOrdering($a_field)
    {
        if ($a_field != "answer") {
            return true;
        }
        return false;
    }

    public function getItems()
    {
        $data = $this->parent_obj->object->getAnswers();
        $perc = $this->parent_obj->object->getVotePercentages();
        $total = $perc["total"];
        $perc = $perc["perc"];
        
        // add current percentages
        foreach ($data as $idx => $item) {
            if (!isset($perc[$item["id"]])) {
                $data[$idx]["percentage"] = 0;
                $data[$idx]["votes"] = 0;
            } else {
                $data[$idx]["percentage"] = round($perc[$item["id"]]["perc"]);
                $data[$idx]["votes"] = $perc[$item["id"]]["abs"];
            }
        }

        $this->setData($data);
        
        return $total;
    }
    
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("VALUE_POS", $a_set["pos"] / 10);
        $this->tpl->setVariable("TXT_ANSWER", nl2br($a_set["answer"]));
        $this->tpl->setVariable("VALUE_VOTES", $a_set["votes"]);
        $this->tpl->setVariable("VALUE_PERCENTAGE", $a_set["percentage"]);
    }
    
    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn($a_set["pos"] / 10);
        $a_csv->addColumn($a_set["answer"]);
        $a_csv->addColumn($a_set["votes"]);
        $a_csv->addColumn($a_set["percentage"]);
        $a_csv->addRow();
    }
    
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, $a_set["pos"] / 10);
        $a_excel->setCell($a_row, 1, $a_set["answer"]);
        $a_excel->setCell($a_row, 2, $a_set["votes"]);
        $a_excel->setCell($a_row, 3, $a_set["percentage"] . "%");
    }
}
