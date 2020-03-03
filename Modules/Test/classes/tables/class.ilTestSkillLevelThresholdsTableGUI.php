<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Skill/classes/class.ilBasicSkill.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdsTableGUI extends ilTable2GUI
{
    private $skillLevelThresholdList;
    
    private $questionAssignmentColumnsEnabled = false;

    /**
     * @var ilNumberInputGUI[]
     */
    protected $input_elements_by_id = array();

    public function setSkillLevelThresholdList(ilTestSkillLevelThresholdList $skillLevelThresholdList)
    {
        $this->skillLevelThresholdList = $skillLevelThresholdList;
    }

    public function getSkillLevelThresholdList()
    {
        return $this->skillLevelThresholdList;
    }

    public function areQuestionAssignmentColumnsEnabled()
    {
        return $this->questionAssignmentColumnsEnabled;
    }

    public function setQuestionAssignmentColumnsEnabled($questionAssignmentColumnsEnabled)
    {
        $this->questionAssignmentColumnsEnabled = $questionAssignmentColumnsEnabled;
    }

    public function __construct($parentOBJ, $testId, $parentCmd, ilCtrl $ctrl, ilLanguage $lng)
    {
        $this->setId('tst_skl_lev_thr_' . $testId);
        parent::__construct($parentOBJ, $parentCmd);

        $this->lng = $lng;
        $this->ctrl = $ctrl;

        $this->lng->loadLanguageModule('form');

        $this->setStyle('table', 'fullwidth');

        $this->setRowTemplate("tpl.tst_skl_thresholds_row.html", "Modules/Test");

        $this->enable('header');
        #$this->disable('sort');
        $this->disable('select_all');
        
        $this->setDefaultOrderField('competence');
        $this->setDefaultOrderDirection('asc');
        $this->setShowRowsSelector(true);

        $this->setFormAction($ctrl->getFormAction($parentOBJ));

        $this->addCommandButton(
            ilTestSkillLevelThresholdsGUI::CMD_SAVE_SKILL_THRESHOLDS,
            $this->lng->txt('tst_save_thresholds')
        );
    }

    public function initColumns()
    {
        $this->addColumn($this->lng->txt('tst_competence'), 'competence', '50%');
        
        if ($this->areQuestionAssignmentColumnsEnabled()) {
            $this->addColumn($this->lng->txt('tst_num_questions'), '', '10%');
            $this->addColumn($this->lng->txt('tst_max_comp_points'), '', '10%');
        }

        $this->addColumn($this->lng->txt('tst_level'), '', '10%');
        $this->addColumn($this->lng->txt('tst_threshold'), '', '10%');
    }

    public function fillRow($data)
    {
        $skill = $data['skill'];
        $levels = $skill->getLevelData();

        if ($this->areQuestionAssignmentColumnsEnabled()) {
            $this->tpl->setCurrentBlock('quest_assign_info');
            $this->tpl->setVariable('ROWSPAN', $this->getRowspan(count($levels)));
            $this->tpl->setVariable('NUM_QUESTIONS', $data['num_assigns']);
            $this->tpl->setVariable('MAX_COMP_POINTS', $data['max_points']);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock('competence');
        $this->tpl->setVariable('ROWSPAN', $this->getRowspan(count($levels)));
        $this->tpl->setVariable('COMPETENCE', $data['competence']);
        $this->tpl->parseCurrentBlock();

        $this->addHiddenInput('rendered[]', $this->buildUniqueRecordIdentifier($data));

        $this->tpl->setCurrentBlock('tbl_content');

        for ($i = 0, $max = count($levels); $i < $max; $i++) {
            $level = $levels[$i];

            $this->tpl->setVariable('LEVEL', $level['title']);

            $this->tpl->setVariable('THRESHOLD', $this->buildThresholdInput(
                $data['skill_base_id'],
                $data['skill_tref_id'],
                $level['id']
            )->render());

            if ($i < ($max - 1)) {
                $this->tpl->parseCurrentBlock();
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->tpl->setVariable("CSS_NO_BORDER", 'ilBorderlessRow');
            }
        }
    }

    /**
     * @param array $row
     * @return string
     */
    private function buildUniqueRecordIdentifier(array $row)
    {
        return 'threshold_' . $row['skill_base_id'] . ':' . $row['skill_tref_id'];
    }

    private function getRowspan($numLevels)
    {
        if ($numLevels == 0) {
            return 1;
        }

        return $numLevels;
    }

    /**
     * @param array $idFilter
     * @return ilNumberInputGUI[]
     */
    public function getInputElements(array $idFilter) : array
    {
        $elements = array();

        foreach ($this->getData() as $data) {
            $id = $this->buildUniqueRecordIdentifier($data);
            if (!in_array($id, $idFilter)) {
                continue;
            }

            $skill  = $data['skill'];
            $levels = $skill->getLevelData();
            for ($i = 0, $max = count($levels); $i < $max; $i++) {
                $level = $levels[$i];

                $elements[] = $this->buildThresholdInput(
                    $data['skill_base_id'],
                    $data['skill_tref_id'],
                    $level['id']
                );
            }
        }
        
        return $elements;
    }

    /**
     * @param $skillBaseId
     * @param $skillTrefId
     * @param $skillLevelId
     * @return ilNumberInputGUI
     */
    private function buildThresholdInput($skillBaseId, $skillTrefId, $skillLevelId)
    {
        $skillKey = $skillBaseId . ':' . $skillTrefId;
        
        if (isset($this->input_elements_by_id[$skillKey][$skillLevelId])) {
            return $this->input_elements_by_id[$skillKey][$skillLevelId];
        }

        $threshold = $this->skillLevelThresholdList->getThreshold($skillBaseId, $skillTrefId, $skillLevelId);
        if ($threshold instanceof ilTestSkillLevelThreshold) {
            $thresholdValue = $threshold->getThreshold();
        } else {
            $thresholdValue = '';
        }

        $value = new ilNumberInputGUI('', 'threshold_' . $skillKey . '_' . $skillLevelId);
        $value->setValue($thresholdValue);
        $value->setSize(5);
        $value->setMinValue(0);

        if (!isset($this->input_elements_by_id[$skillKey])) {
            $this->input_elements_by_id[$skillKey] = array();
        }

        $this->input_elements_by_id[$skillKey][$skillLevelId] = $value;

        return $value;
    }
    
    public function completeCompetenceTitles($rows)
    {
        foreach ($rows as $key => $row) {
            $rows[$key]['competence'] = ilBasicSkill::_lookupTitle(
                $row['skill']->getId(),
                $row['skill_tref_id']
            );
        }
        
        return $rows;
    }
}
