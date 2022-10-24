<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdsTableGUI extends ilTable2GUI
{
    private ?ilTestSkillLevelThresholdList $skillLevelThresholdList;

    private bool $questionAssignmentColumnsEnabled = false;

    /**
     * @var ilNumberInputGUI[]
     */
    protected array $input_elements_by_id = array();

    public function setSkillLevelThresholdList(ilTestSkillLevelThresholdList $skillLevelThresholdList): void
    {
        $this->skillLevelThresholdList = $skillLevelThresholdList;
    }

    public function getSkillLevelThresholdList(): ?ilTestSkillLevelThresholdList
    {
        return $this->skillLevelThresholdList;
    }

    public function areQuestionAssignmentColumnsEnabled(): bool
    {
        return $this->questionAssignmentColumnsEnabled;
    }

    public function setQuestionAssignmentColumnsEnabled(bool $questionAssignmentColumnsEnabled): void
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

    public function initColumns(): void
    {
        $this->addColumn($this->lng->txt('tst_competence'), 'competence', '50%');

        if ($this->areQuestionAssignmentColumnsEnabled()) {
            $this->addColumn($this->lng->txt('tst_num_questions'), '', '10%');
            $this->addColumn($this->lng->txt('tst_max_comp_points'), '', '10%');
        }

        $this->addColumn($this->lng->txt('tst_level'), '', '10%');
        $this->addColumn($this->lng->txt('tst_threshold'), '', '10%');
    }

    public function fillRow(array $a_set): void
    {
        $skill = $a_set['skill'];
        $levels = $skill->getLevelData();

        if ($this->areQuestionAssignmentColumnsEnabled()) {
            $this->tpl->setCurrentBlock('quest_assign_info');
            $this->tpl->setVariable('ROWSPAN', $this->getRowspan(count($levels)));
            $this->tpl->setVariable('NUM_QUESTIONS', $a_set['num_assigns']);
            $this->tpl->setVariable('MAX_COMP_POINTS', $a_set['max_points']);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock('competence');
        $this->tpl->setVariable('ROWSPAN', $this->getRowspan(count($levels)));
        $this->tpl->setVariable('COMPETENCE', $a_set['competence']);
        $this->tpl->parseCurrentBlock();

        $this->addHiddenInput('rendered[]', $this->buildUniqueRecordIdentifier($a_set));

        $this->tpl->setCurrentBlock('tbl_content');

        for ($i = 0, $max = count($levels); $i < $max; $i++) {
            $level = $levels[$i];

            $this->tpl->setVariable('LEVEL', $level['title']);

            $this->tpl->setVariable('THRESHOLD', $this->buildThresholdInput(
                $a_set['skill_base_id'],
                $a_set['skill_tref_id'],
                $level['id']
            )->render());

            if ($i < ($max - 1)) {
                $this->tpl->parseCurrentBlock();
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->tpl->setVariable("CSS_NO_BORDER", 'ilBorderlessRow');
            }
        }
    }

    private function buildUniqueRecordIdentifier(array $row): string
    {
        return 'threshold_' . $row['skill_base_id'] . ':' . $row['skill_tref_id'];
    }

    private function getRowspan($numLevels): int
    {
        if ($numLevels == 0) {
            return 1;
        }

        return $numLevels;
    }

    /**
     * @return ilNumberInputGUI[]
     */
    public function getInputElements(array $idFilter): array
    {
        $elements = array();

        foreach ($this->getData() as $data) {
            $id = $this->buildUniqueRecordIdentifier($data);
            if (!in_array($id, $idFilter)) {
                continue;
            }

            $skill = $data['skill'];
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

    private function buildThresholdInput($skillBaseId, $skillTrefId, $skillLevelId): ilNumberInputGUI
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
        $value->setMaxValue(100);

        if (!isset($this->input_elements_by_id[$skillKey])) {
            $this->input_elements_by_id[$skillKey] = array();
        }

        $this->input_elements_by_id[$skillKey][$skillLevelId] = $value;

        return $value;
    }

    public function completeCompetenceTitles(array $rows): array
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
