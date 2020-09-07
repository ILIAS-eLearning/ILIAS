<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

require_once 'Modules/TestQuestionPool/classes/class.ilLogicalAnswerComparisonExpressionInputGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSolutionComparisonExpressionList.php';

require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';
require_once 'Services/Form/classes/class.ilRadioGroupInputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillAssignmentPropertyFormGUI extends ilPropertyFormGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    
    /**
     * @var ilLanguage
     */
    protected $lng;
    
    /**
     * @var ilAssQuestionSkillAssignmentsGUI
     */
    protected $parentGUI;
    
    /**
     * @var assQuestion
     */
    private $question = null;

    /**
     * @var ilAssQuestionSkillAssignment
     */
    private $assignment = null;
    
    /**
     * @var bool
     */
    private $manipulationEnabled = false;

    
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilAssQuestionSkillAssignmentsGUI $parentGUI)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->parentGUI = $parentGUI;
        
        parent::__construct();
    }
    
    /**
     * @return assQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param assQuestion $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return ilAssQuestionSkillAssignment
     */
    public function getAssignment()
    {
        return $this->assignment;
    }

    /**
     * @param ilAssQuestionSkillAssignment $assignment
     */
    public function setAssignment($assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * @return boolean
     */
    public function isManipulationEnabled()
    {
        return $this->manipulationEnabled;
    }

    /**
     * @param boolean $manipulationEnabled
     */
    public function setManipulationEnabled($manipulationEnabled)
    {
        $this->manipulationEnabled = $manipulationEnabled;
    }

    public function build()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parentGUI));

        if ($this->isManipulationEnabled()) {
            $this->addCommandButton(
                ilAssQuestionSkillAssignmentsGUI::CMD_SAVE_SKILL_QUEST_ASSIGN_PROPERTIES_FORM,
                $this->lng->txt('save')
            );

            $this->addCommandButton(
                ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS,
                $this->lng->txt('cancel')
            );
        } else {
            $this->addCommandButton(
                ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS,
                $this->lng->txt('back')
            );
        }

        $this->setTitle($this->assignment->getSkillTitle());

        $questionTitle = new ilNonEditableValueGUI($this->lng->txt('question'));
        $questionTitle->setValue($this->question->getTitle());
        $this->addItem($questionTitle);

        $questionDesc = new ilNonEditableValueGUI($this->lng->txt('description'));
        $questionDesc->setValue($this->question->getComment());
        $this->addItem($questionDesc);

        if ($this->questionSupportsSolutionCompare()) {
            $this->populateFullProperties();
        } else {
            $this->populateLimitedProperties();
        }
    }

    private function buildLacLegendToggleButton()
    {
        if ($this->assignment->hasEvalModeBySolution()) {
            $langVar = 'ass_lac_hide_legend_btn';
        } else {
            $langVar = 'ass_lac_show_legend_btn';
        }

        return '<a id="lac_legend_toggle_btn" href="#">' . $this->lng->txt($langVar) . '</a>';
    }
    
    private function populateFullProperties()
    {
        $evaluationMode = new ilRadioGroupInputGUI($this->lng->txt('condition'), 'eval_mode');
        $evalOptionReachedQuestionPoints = new ilRadioOption(
            $this->lng->txt('qpl_skill_point_eval_by_quest_result'),
            'result'
        );
        $evaluationMode->addOption($evalOptionReachedQuestionPoints);
        $evalOptionLogicalAnswerCompare = new ilRadioOption(
            $this->lng->txt('qpl_skill_point_eval_by_solution_compare'),
            'solution'
        );
        $evaluationMode->addOption($evalOptionLogicalAnswerCompare);
        $evaluationMode->setRequired(true);
        $evaluationMode->setValue($this->assignment->getEvalMode());
        if (!$this->isManipulationEnabled()) {
            $evaluationMode->setDisabled(true);
        }
        $this->addItem($evaluationMode);

        $questSolutionCompareExpressions = new ilLogicalAnswerComparisonExpressionInputGUI(
            $this->lng->txt('tst_solution_compare_cfg'),
            'solution_compare_expressions'
        );
        $questSolutionCompareExpressions->setRequired(true);
        $questSolutionCompareExpressions->setAllowMove($this->isManipulationEnabled());
        $questSolutionCompareExpressions->setAllowAddRemove($this->isManipulationEnabled());
        $questSolutionCompareExpressions->setQuestionObject($this->question);
        $questSolutionCompareExpressions->setValues($this->assignment->getSolutionComparisonExpressionList()->get());
        $questSolutionCompareExpressions->setMinvalueShouldBeGreater(false);
        $questSolutionCompareExpressions->setMinValue(1);
        if (!$this->isManipulationEnabled()) {
            $questSolutionCompareExpressions->setDisabled(true);
        } else {
            // #19192
            $questSolutionCompareExpressions->setInfo($this->buildLacLegendToggleButton());
        }
        $evalOptionLogicalAnswerCompare->addSubItem($questSolutionCompareExpressions);

        $questResultSkillPoints = $this->buildResultSkillPointsInputField();
        $evalOptionReachedQuestionPoints->addSubItem($questResultSkillPoints);
    }
    
    private function populateLimitedProperties()
    {
        $evaluationMode = new ilNonEditableValueGUI($this->lng->txt('condition'));
        $evaluationMode->setValue($this->lng->txt('qpl_skill_point_eval_by_quest_result'));
        $this->addItem($evaluationMode);

        $questResultSkillPoints = $this->buildResultSkillPointsInputField();
        $evaluationMode->addSubItem($questResultSkillPoints);
    }
    
    private function buildResultSkillPointsInputField()
    {
        $questResultSkillPoints = new ilNumberInputGUI($this->lng->txt('tst_comp_points'), 'q_res_skill_points');
        $questResultSkillPoints->setRequired(true);
        $questResultSkillPoints->setSize(4);
        $questResultSkillPoints->setMinvalueShouldBeGreater(false);
        $questResultSkillPoints->setMinValue(1);
        $questResultSkillPoints->allowDecimals(false);
        $questResultSkillPoints->setValue($this->assignment->getSkillPoints());
        if (!$this->isManipulationEnabled()) {
            $questResultSkillPoints->setDisabled(true);
        }
        
        return $questResultSkillPoints;
    }
    
    private function questionSupportsSolutionCompare()
    {
        return (
            $this->question instanceof iQuestionCondition
        );
    }
}
