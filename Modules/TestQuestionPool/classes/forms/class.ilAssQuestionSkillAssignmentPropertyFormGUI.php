<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Björn Heyser <bheyser@databay.de>
 * @package Modules/Test
 */
class ilAssQuestionSkillAssignmentPropertyFormGUI extends ilPropertyFormGUI
{
    /** @var ilGlobalTemplateInterface */
    private $pageTemplate;
    /** @var ilAssQuestionSkillAssignmentsGUI */
    private $parentGUI;
    /** @var assQuestion */
    private $question = null;
    /** @var ilAssQuestionSkillAssignment */
    private $assignment = null;
    /** @var bool */
    private $manipulationEnabled = false;
    /** @var \ILIAS\UI\Factory */
    private $uiFactory;
    /** @var \ILIAS\UI\Renderer */
    private $uiRenderer;

    public function __construct(
        ilGlobalTemplateInterface $pageTemplate,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilAssQuestionSkillAssignmentsGUI $parentGUI
    ) {
        global $DIC;

        $this->pageTemplate = $pageTemplate;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->parentGUI = $parentGUI;
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();

        parent::__construct();
    }
    
    /**
     * @return assQuestion
     */
    public function getQuestion() : ?assQuestion
    {
        return $this->question;
    }

    /**
     * @param assQuestion $question
     */
    public function setQuestion($question) : void
    {
        $this->question = $question;
    }

    /**
     * @return ilAssQuestionSkillAssignment
     */
    public function getAssignment() : ?ilAssQuestionSkillAssignment
    {
        return $this->assignment;
    }

    /**
     * @param ilAssQuestionSkillAssignment $assignment
     */
    public function setAssignment($assignment) : void
    {
        $this->assignment = $assignment;
    }

    /**
     * @return boolean
     */
    public function isManipulationEnabled() : bool
    {
        return $this->manipulationEnabled;
    }

    /**
     * @param boolean $manipulationEnabled
     */
    public function setManipulationEnabled($manipulationEnabled) : void
    {
        $this->manipulationEnabled = $manipulationEnabled;
    }

    public function build() : void
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

    private function populateFullProperties() : void
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
        if ($this->isManipulationEnabled()) {
            if ($this->getQuestion() instanceof iQuestionCondition) {
                // #19192
                $legendGUI = new ilAssLacLegendGUI($this->pageTemplate, $this->lng, $this->uiFactory);
                $legendGUI->setQuestionOBJ($this->getQuestion());
                $legenModal = $legendGUI->get();

                $legendToggleButton = $this->uiFactory
                    ->button()
                    ->shy($this->lng->txt('ass_lac_show_legend_btn'), '#')
                    ->withOnClick($legenModal->getShowSignal());

                $questSolutionCompareExpressions->setInfo($this->uiRenderer->render([
                    $legendToggleButton,
                    $legenModal
                ]));
            }
        } else {
            $questSolutionCompareExpressions->setDisabled(true);
        }
        $evalOptionLogicalAnswerCompare->addSubItem($questSolutionCompareExpressions);

        $questResultSkillPoints = $this->buildResultSkillPointsInputField();
        $evalOptionReachedQuestionPoints->addSubItem($questResultSkillPoints);
    }
    
    private function populateLimitedProperties() : void
    {
        $evaluationMode = new ilNonEditableValueGUI($this->lng->txt('condition'));
        $evaluationMode->setValue($this->lng->txt('qpl_skill_point_eval_by_quest_result'));
        $this->addItem($evaluationMode);

        $questResultSkillPoints = $this->buildResultSkillPointsInputField();
        $evaluationMode->addSubItem($questResultSkillPoints);
    }
    
    private function buildResultSkillPointsInputField() : ilNumberInputGUI
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
    
    private function questionSupportsSolutionCompare() : bool
    {
        return (
            $this->question instanceof iQuestionCondition
        );
    }
}
