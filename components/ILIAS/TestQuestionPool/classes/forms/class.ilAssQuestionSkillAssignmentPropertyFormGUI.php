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

declare(strict_types=1);

use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * User interface form for configuring under which conditions a competence is
 * awarded when a test question has been solved. Refers to one specific
 * question from a test question pool (or directly from a test).
 *
 * @author  Björn Heyser <bheyser@databay.de>
 * @package components\ILIAS/Test
 */
class ilAssQuestionSkillAssignmentPropertyFormGUI extends ilPropertyFormGUI
{
    private ?assQuestion $question = null;
    private ?ilAssQuestionSkillAssignment $assignment = null;
    private bool $manipulation_enabled = false;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private ?Modal $legend_modal = null;

    public function __construct(
        private readonly ilAssQuestionSkillAssignmentsGUI $parent_gui
    ) {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        parent::__construct();
    }

    public function getQuestion(): ?assQuestion
    {
        return $this->question;
    }

    public function setQuestion(assQuestion $question): void
    {
        $this->question = $question;
    }

    public function getAssignment(): ?ilAssQuestionSkillAssignment
    {
        return $this->assignment;
    }

    public function setAssignment(ilAssQuestionSkillAssignment $assignment): void
    {
        $this->assignment = $assignment;
    }

    public function isManipulationEnabled(): bool
    {
        return $this->manipulation_enabled;
    }

    public function setManipulationEnabled(bool $manipulation_enabled): void
    {
        $this->manipulation_enabled = $manipulation_enabled;
    }

    public function build(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

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

    public function getHTML(): string
    {
        if ($this->legend_modal === null) {
            return parent::getHTML();
        }
        return parent::getHTML() . $this->ui_renderer->render($this->legend_modal);
    }

    private function populateFullProperties(): void
    {
        $evaluation_mode = new ilRadioGroupInputGUI($this->lng->txt('condition'), 'eval_mode');
        $eval_option_reached_pointsoints = new ilRadioOption(
            $this->lng->txt('qpl_skill_point_eval_by_quest_result'),
            'result'
        );
        $evaluation_mode->addOption($eval_option_reached_pointsoints);
        $eval_option_logical_answer_compare = new ilRadioOption(
            $this->lng->txt('qpl_skill_point_eval_by_solution_compare'),
            'solution'
        );
        $evaluation_mode->addOption($eval_option_logical_answer_compare);
        $evaluation_mode->setRequired(true);
        $evaluation_mode->setValue($this->assignment->getEvalMode());
        if (!$this->isManipulationEnabled()) {
            $evaluation_mode->setDisabled(true);
        }
        $this->addItem($evaluation_mode);

        $quest_solution_compare_expressions = new ilLogicalAnswerComparisonExpressionInputGUI(
            $this->lng->txt('tst_solution_compare_cfg'),
            'solution_compare_expressions'
        );
        $quest_solution_compare_expressions->setRequired(true);
        $quest_solution_compare_expressions->setAllowMove($this->isManipulationEnabled());
        $quest_solution_compare_expressions->setAllowAddRemove($this->isManipulationEnabled());
        $quest_solution_compare_expressions->setQuestionObject($this->question);
        $quest_solution_compare_expressions->setValues($this->assignment->getSolutionComparisonExpressionList()->get());
        $quest_solution_compare_expressions->setMinvalueShouldBeGreater(false);
        $quest_solution_compare_expressions->setMinValue(1);

        if ($this->isManipulationEnabled()) {
            if ($this->getQuestion() instanceof iQuestionCondition) {
                // #19192
                $legend_gui = new ilAssLacLegendGUI($this->global_tpl, $this->lng, $this->ui_factory);
                $legend_gui->setQuestionOBJ($this->getQuestion());
                $this->legend_modal = $legend_gui->get();

                $legend_show_button = $this->ui_factory
                    ->button()
                    ->shy($this->lng->txt('ass_lac_show_legend_btn'), '#')
                    ->withOnClick($this->legend_modal->getShowSignal());

                $quest_solution_compare_expressions->setInfo(
                    $this->ui_renderer->render($legend_show_button)
                );
            }
        } else {
            $quest_solution_compare_expressions->setDisabled(true);
        }
        $eval_option_logical_answer_compare->addSubItem($quest_solution_compare_expressions);

        $eval_option_reached_pointsoints->addSubItem(
            $this->buildResultSkillPointsInputField()
        );
    }

    private function populateLimitedProperties(): void
    {
        $evaluationMode = new ilNonEditableValueGUI($this->lng->txt('condition'));
        $evaluationMode->setValue($this->lng->txt('qpl_skill_point_eval_by_quest_result'));
        $this->addItem($evaluationMode);

        $questResultSkillPoints = $this->buildResultSkillPointsInputField();
        $evaluationMode->addSubItem($questResultSkillPoints);
    }

    private function buildResultSkillPointsInputField(): ilNumberInputGUI
    {
        $questResultSkillPoints = new ilNumberInputGUI($this->lng->txt('tst_comp_points'), 'q_res_skill_points');
        $questResultSkillPoints->setRequired(true);
        $questResultSkillPoints->setSize(4);
        $questResultSkillPoints->setMinvalueShouldBeGreater(false);
        $questResultSkillPoints->setMinValue(1);
        $questResultSkillPoints->allowDecimals(false);
        $questResultSkillPoints->setValue((string) $this->assignment->getSkillPoints());
        if (!$this->isManipulationEnabled()) {
            $questResultSkillPoints->setDisabled(true);
        }

        return $questResultSkillPoints;
    }

    private function questionSupportsSolutionCompare(): bool
    {
        return (
            $this->question instanceof iQuestionCondition
        );
    }
}
