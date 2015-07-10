<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Skill/classes/class.ilBasicSkill.php';

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

	public function __construct($parentOBJ, $parentCmd, ilCtrl $ctrl, ilLanguage $lng)
	{
		parent::__construct($parentOBJ, $parentCmd);

		$this->lng = $lng;
		$this->ctrl = $ctrl;

		$this->setStyle('table', 'fullwidth');

		$this->setRowTemplate("tpl.tst_skl_thresholds_row.html", "Modules/Test");

		$this->enable('header');
		#$this->disable('sort');
		$this->disable('select_all');
		
		$this->setDefaultOrderField('competence');
		$this->setDefaultOrderDirection('asc');

		$this->setFormAction($ctrl->getFormAction($parentOBJ));

		$this->addCommandButton(
			ilTestSkillLevelThresholdsGUI::CMD_SAVE_SKILL_THRESHOLDS, $this->lng->txt('tst_save_thresholds')
		);
	}

	public function initColumns()
	{
		$this->addColumn($this->lng->txt('tst_competence'),'competence', '50%');
		
		if( $this->areQuestionAssignmentColumnsEnabled() )
		{
			$this->addColumn($this->lng->txt('tst_num_questions'),'', '10%');
			$this->addColumn($this->lng->txt('tst_max_comp_points'),'', '10%');
		}

		$this->addColumn($this->lng->txt('tst_level'),'', '10%');
		$this->addColumn($this->lng->txt('tst_threshold'),'', '10%');
	}

	public function fillRow($data)
	{
		$skill = $data['skill'];
		$levels = $skill->getLevelData();

		if( $this->areQuestionAssignmentColumnsEnabled() )
		{
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

		$this->tpl->setCurrentBlock('tbl_content');

		for($i = 0, $max = count($levels); $i < $max; $i++)
		{
			$level = $levels[$i];

			$this->tpl->setVariable('LEVEL', $level['title']);

			$this->tpl->setVariable('THRESHOLD', $this->buildThresholdInput(
				$data['skill_base_id'], $data['skill_tref_id'], $level['id']
			));

			if( $i < ($max - 1) )
			{
				$this->tpl->parseCurrentBlock();
				$this->tpl->setVariable("CSS_ROW", $this->css_row);
				$this->tpl->setVariable("CSS_NO_BORDER", 'ilBorderlessRow');
			}
		}
	}

	private function getRowspan($numLevels)
	{
		if($numLevels == 0)
		{
			return 1;
		}

		return $numLevels;
	}

	private function buildThresholdInput($skillBaseId, $skillTrefId, $skillLevelId)
	{
		$threshold = $this->skillLevelThresholdList->getThreshold($skillBaseId, $skillTrefId, $skillLevelId);

		if( $threshold instanceof ilTestSkillLevelThreshold )
		{
			$thresholdValue = $threshold->getThreshold();
		}
		else
		{
			$thresholdValue = '';
		}

		$skillKey = $skillBaseId.':'.$skillTrefId;

		return "<input type\"text\" size=\"2\" name=\"threshold[{$skillKey}][$skillLevelId]\" value=\"{$thresholdValue}\" />";
	}
	
	public function completeCompetenceTitles($rows)
	{
		foreach($rows as $key => $row)
		{
			$rows[$key]['competence'] = ilBasicSkill::_lookupTitle(
				$row['skill']->getId(), $row['skill_tref_id']
			);
		}
		
		return $rows;
	}
}