<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup	ModulesTest
*/
class ilTestPassManualScoringOverviewTableGUI extends ilTable2GUI
{
    /**
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	ilObjectGUI	$parentObj
     * @param	string		$parentCmd
     */
    public function __construct($parentObj, $parentCmd)
    {
        parent::__construct($parentObj, $parentCmd);

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setPrefix('manScorePassesTable');

        $this->setFormName('manScorePassesTable');
        $this->setStyle('table', 'fullwidth');

        $this->enable('header');

        $this->setFormAction($ilCtrl->getFormAction($parentObj, $parentCmd));

        $this->setRowTemplate("tpl.il_as_tst_pass_overview_tblrow.html", "Modules/Test");

        $this->initColumns();
        $this->initOrdering();
    }
    
    private function initColumns()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $this->addColumn($lng->txt("scored_pass"), '', '150');
        $this->addColumn($lng->txt("pass"), 'pass', '');
        $this->addColumn($lng->txt("date"), 'finishdate', '');
        $this->addColumn($lng->txt("tst_answered_questions"), 'answered_questions', '');
        $this->addColumn($lng->txt("tst_reached_points"), 'reached_points', '');
        $this->addColumn($lng->txt("tst_percent_solved"), 'percentage', '');
        $this->addColumn('', '', '1%');
    }
    
    private function initOrdering()
    {
        $this->disable('sort');

        $this->setDefaultOrderField("pass");
        $this->setDefaultOrderDirection("asc");
    }

    /**
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	array		$row
     */
    public function fillRow($row)
    {
        //vd($row);
        
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->setParameter($this->parent_obj, 'active_id', $row['active_id']);
        $ilCtrl->setParameter($this->parent_obj, 'pass', $row['pass']);
        
        if ($row['is_scored_pass']) {
            $this->tpl->setCurrentBlock('selected_pass');
            $this->tpl->touchBlock('selected_pass');
            $this->tpl->parseCurrentBlock();
            $this->tpl->setVariable('CSS_ROW', 'tblrowmarked');
        }
    
        $this->tpl->setVariable("PASS_NR", $row['pass'] + 1);
        $this->tpl->setVariable("PASS_DATE", ilDatePresentation::formatDate(new ilDate($row['finishdate'], IL_CAL_UNIX)));
        $this->tpl->setVariable("PASS_ANSWERED_QUESTIONS", $row['answered_questions'] . " " . strtolower($this->lng->txt("of")) . " " . $row['total_questions']);
        $this->tpl->setVariable("PASS_REACHED_POINTS", $row['reached_points'] . " " . strtolower($this->lng->txt("of")) . " " . $row['max_points']);
        $this->tpl->setVariable("PASS_REACHED_PERCENTAGE", sprintf("%.2f%%", $row['percentage']));
        
        $this->tpl->setVariable("TXT_SHOW_PASS", $lng->txt('tst_edit_scoring'));
        $this->tpl->setVariable("HREF_SHOW_PASS", $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
    }
}
