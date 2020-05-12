<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestAverageReachedPointsTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->setId('tstAvgReachedPointsTbl');
        $this->setPrefix('tstAvgReachedPointsTbl');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setFormName('average_reached_points');
        $this->setTitle($this->lng->txt('average_reached_points'));
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("question_id"), 'qid', '');
        $this->addColumn($this->lng->txt("question_title"), 'title', '');
        $this->addColumn($this->lng->txt("points"), 'points', '');
        $this->addColumn($this->lng->txt("percentage"), 'percentage', '');
        $this->addColumn($this->lng->txt("number_of_answers"), 'answers', '');

        $this->setRowTemplate("tpl.il_as_tst_average_reached_points_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->enable('sort');
        $this->enable('header');
        $this->disable('select_all');
    }

    /**
     * Should this field be sorted numeric?
     * @return    boolean        numeric ordering; default is false
     */
    public function numericOrdering($a_field)
    {
        switch ($a_field) {
            case 'points':
                return true;

            case 'qid':
                return true;

            case 'percentage':
                return true;

            default:
                return false;
        }
    }

    /**
     * fill row
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        $this->tpl->setVariable("ID", $data["qid"]);
        $this->tpl->setVariable("TITLE", $data["title"]);
        $this->tpl->setVariable("POINTS", $this->formatPoints($data));
        $this->tpl->setVariable("PERCENTAGE", sprintf("%.2f", $data["percentage"]) . "%");
        $this->tpl->setVariable("ANSWERS", $data["answers"]);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function formatPoints(array $data)
    {
        return sprintf("%.2f", $data['points_reached']) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $data['points_max']);
    }
}
