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

namespace ILIAS\Survey\PrintView;

use ILIAS\Export;
use ilPropertyFormGUI;
use ILIAS\Survey\Page\PageRenderer;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ResultsDetailsPrintViewProviderGUI extends Export\AbstractPrintViewProvider
{
    protected \ILIAS\Survey\InternalGUIService $gui;
    protected \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request;
    protected \ILIAS\Survey\Mode\UIModifier $ui_modifier;
    protected \ILIAS\Survey\Evaluation\EvaluationManager $evaluation_manager;
    protected \ilObjSurvey $survey;
    protected int $ref_id;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct(
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        int $ref_id
    ) {
        global $DIC;

        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->ref_id = $ref_id;
        $this->survey = new \ilObjSurvey($this->ref_id);
        $this->request = $DIC->survey()
                             ->internal()
                             ->gui()
                             ->evaluation($this->survey)
                             ->request();
        $this->evaluation_manager = $DIC->survey()
            ->internal()
            ->domain()
            ->evaluation(
                $this->survey,
                $DIC->user()->getId(),
                $this->request->getAppraiseeId(),
                $this->request->getRaterId()
            );
        $this->ui_modifier = $DIC->survey()
                                 ->internal()
                                 ->gui()
                                 ->modeUIModifier($this->survey->getMode());
        $this->gui = $DIC->survey()
            ->internal()
            ->gui();
    }

    public function getTemplateInjectors(): array
    {
        return [
            static function (\ilGlobalTemplate $tpl): void {
                //$tpl add js/css
            }
        ];
    }

    public function getSelectionForm(): ?ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new \ilPropertyFormGUI();

        $radg = new \ilRadioGroupInputGUI($lng->txt("svy_selection"), "print_selection");
        $radg->setValue("all");
        $op1 = new \ilRadioOption($lng->txt("svy_all_questions"), "all");
        $radg->addOption($op1);
        $op2 = new \ilRadioOption($lng->txt("svy_selected_questions"), "selected");
        $radg->addOption($op2);

        $nl = new \ilNestedListInputGUI("", "qids");
        $op2->addSubItem($nl);

        foreach ($this->survey->getSurveyQuestions() as $qdata) {
            $nl->addListNode(
                $qdata["question_id"],
                $qdata["title"],
                0,
                false,
                false
            );
        }

        $form->addItem($radg);


        $form->addCommandButton("printResultsDetails", $lng->txt("print_view"));

        $form->setTitle($lng->txt("svy_print_selection"));
        $form->setFormAction($ilCtrl->getFormActionByClass(
            "ilSurveyEvaluationGUI",
            "printResultsDetails"
        ));

        return $form;
    }

    public function getPages(): array
    {
        $print_pages = [];

        $finished_ids = $this->evaluation_manager->getFilteredFinishedIds();

        $selection = $this->request->getPrintSelection();
        $qids = $this->request->getQuestionIds();

        foreach ($this->survey->getSurveyQuestions() as $qdata) {
            $q_eval = \SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $finished_ids);

            if ($selection !== "all" && !in_array($qdata["question_id"], $qids)) {
                continue;
            }

            $panels = $this->ui_modifier->getDetailPanels(
                $this->survey->getSurveyParticipants(),
                $this->request,
                $q_eval
            );
            $panel_report = $this->gui->ui()->factory()->panel()->report("", $panels);
            $print_pages[] = $this->gui->ui()->renderer()->render($panel_report);
            //$print_pages[] = $this->gui->ui()->renderer()->render($panels);
        }
        return $print_pages;
    }

    public function autoPageBreak(): bool
    {
        return false;
    }
}
