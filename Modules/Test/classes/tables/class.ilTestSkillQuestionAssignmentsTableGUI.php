<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillQuestionAssignmentsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilTestSkillQuestionAssignmentList
	 */
	private $skillQuestionAssignmentList;

	public function setSkillQuestionAssignmentList(ilTestSkillQuestionAssignmentList $assignmentList)
	{
		$this->skillQuestionAssignmentList = $assignmentList;
	}

	public function __construct($parentOBJ, $parentCmd, ilCtrl $ctrl, ilLanguage $lng)
	{
		parent::__construct($parentOBJ, $parentCmd);

		$this->lng = $lng;
		$this->ctrl = $ctrl;
		
		$this->setId('assQstSkl');
		$this->getPrefix('assQstSkl');

		$this->setStyle('table', 'fullwidth');

		$this->setRowTemplate("tpl.tst_skl_qst_assignment_row.html", "Modules/Test");

		$this->enable('header');
		$this->disable('sort');
		$this->disable('select_all');

		$this->initColumns();

		$this->setFormAction($ctrl->getFormAction($parentOBJ));

		$this->addCommandButton(
			ilTestSkillQuestionAssignmentsGUI::CMD_SAVE_SKILL_POINTS, $this->lng->txt('tst_save_comp_points')
		);
	}

	private function initColumns()
	{
		$this->addColumn($this->lng->txt('tst_question'),'question', '25%');
		$this->addColumn($this->lng->txt('tst_competence'),'competence', '55%');
		$this->addColumn($this->lng->txt('tst_comp_points'),'points', '');
		$this->addColumn($this->lng->txt('actions') ,'actions', '');
	}

	public function fillRow($question)
	{
		$assignments = $this->skillQuestionAssignmentList->getAssignmentsByQuestionId($question['question_id']);

		$this->ctrl->setParameter($this->parent_obj, 'question_id', $question['question_id']);

		$this->tpl->setCurrentBlock('question_title');
		$this->tpl->setVariable('ROWSPAN', $this->getRowspan($assignments));
		$this->tpl->setVariable('QUESTION', $question['title']);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('tbl_content');

		for($i = 0, $max = count($assignments); $i < $max; $i++)
		{
			$assignment = $assignments[$i];

			$this->tpl->setVariable('COMPETENCE', $assignment->getSkillTitle());
			$this->tpl->setVariable('COMPETENCE_PATH', $assignment->getSkillPath());
			$this->tpl->setVariable('QUANTIFIER', $this->buildQuantifierInput($assignment));
			$this->tpl->setVariable('ACTION', $this->getRemoveCompetenceActionLink($assignment));

			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("CSS_ROW", $this->css_row);
			$this->tpl->setVariable("CSS_NO_BORDER", 'ilBorderlessRow');
		}

		$this->tpl->setVariable('ACTION', $this->getAddCompetenceActionLink());
	}

	private function getRowspan($assignments)
	{
		$cnt = count($assignments);

		if( $cnt == 0 )
		{
			return 1;
		}

		return $cnt + 1;
	}

	private function buildQuantifierInput(ilTestSkillQuestionAssignment $assignment)
	{
		$assignmentKey = implode(':', array(
			$assignment->getSkillBaseId(), $assignment->getSkillTrefId(), $assignment->getQuestionId()
		));

		return "<input type\"text\" size=\"2\" name=\"quantifiers[{$assignmentKey}]\" value=\"{$assignment->getSkillPoints()}\" />";
	}

	private function getAddCompetenceActionLink()
	{
		$href = $this->ctrl->getLinkTarget(
			$this->parent_obj, ilTestSkillQuestionAssignmentsGUI::CMD_SHOW_SKILL_SELECT
		);

		$label = $this->lng->txt('tst_assign_competence');

		return $this->buildActionLink($href, $label);
	}

	private function getRemoveCompetenceActionLink(ilTestSkillQuestionAssignment $assignment)
	{
		$this->ctrl->setParameter($this->parent_obj, 'skill_base_id', $assignment->getSkillBaseId());
		$this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', $assignment->getSkillTrefId());

		$href = $this->ctrl->getLinkTarget(
			$this->parent_obj, ilTestSkillQuestionAssignmentsGUI::CMD_REMOVE_SKILL_QUEST_ASSIGN
		);

		$label = $this->lng->txt('tst_remove_competence');

		$this->ctrl->setParameter($this->parent_obj, 'skill_base_id', null);
		$this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', null);

		return $this->buildActionLink($href, $label);
	}

	private function buildActionLink($href, $label)
	{
		return "<a href=\"{$href}\" title=\"{$label}\">{$label}</a>";
	}

	private function buildActionColumnHTML($assignments)
	{
		$actions = array();

		foreach($assignments as $assignment)
		{
			$this->ctrl->setParameter($this->parent_obj, 'skill_base_id', $assignment->getSkillBaseId());
			$this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', $assignment->getSkillTrefId());

			$href = $this->ctrl->getLinkTarget(
				$this->parent_obj, ilTestSkillQuestionAssignmentsGUI::CMD_REMOVE_SKILL_QUEST_ASSIGN
			);

			$label = $this->lng->txt('tst_remove_competence');

			$actions[] = $this->buildActionLink($href, $label);
		}

		$href = $this->ctrl->getLinkTarget(
			$this->parent_obj, ilTestSkillQuestionAssignmentsGUI::CMD_SHOW_SKILL_SELECT
		);

		$label = $this->lng->txt('tst_assign_competence');
		$actions[] = $this->buildActionLink($href, $label);

		return implode('<br />', $actions);
	}
}