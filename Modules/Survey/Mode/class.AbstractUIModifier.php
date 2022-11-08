<?php

declare(strict_types=1);

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

namespace ILIAS\Survey\Mode;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class AbstractUIModifier implements UIModifier
{
    protected ?InternalService $service = null;

    public function __construct()
    {
    }

    public function setInternalService(InternalService $internal_service): void
    {
        $this->service = $internal_service;
    }

    public function getInternalService(): InternalService
    {
        return $this->service;
    }

    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey
    ): array {
        return [];
    }

    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array {
        return [];
    }

    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalGUIService $ui_service
    ): array {
        return [];
    }

    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ): void {
    }

    public function setResultsOverviewToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void {
        $this->addApprSelectionToToolbar(
            $survey,
            $toolbar,
            $user_id
        );

        $this->addExportAndPrintButton(
            $survey,
            $toolbar,
            false
        );
    }

    public function setResultsCompetenceToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void {
        $this->addApprSelectionToToolbar(
            $survey,
            $toolbar,
            $user_id
        );
    }


    public function setResultsDetailToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void {
        $request = $this->service
            ->gui()
            ->evaluation($survey)
            ->request();

        $gui = $this->service->gui();
        $lng = $gui->lng();

        $this->addApprSelectionToToolbar(
            $survey,
            $toolbar,
            $user_id
        );

        $captions = new \ilSelectInputGUI($lng->txt("svy_eval_captions"), "cp");
        $captions->setOptions(array(
            "ap" => $lng->txt("svy_eval_captions_abs_perc"),
            "a" => $lng->txt("svy_eval_captions_abs"),
            "p" => $lng->txt("svy_eval_captions_perc")
        ));
        $captions->setValue($request->getCP());
        $toolbar->addInputItem($captions, true);

        $view = new \ilSelectInputGUI($lng->txt("svy_eval_view"), "vw");
        $view->setOptions(array(
            "tc" => $lng->txt("svy_eval_view_tables_charts"),
            "t" => $lng->txt("svy_eval_view_tables"),
            "c" => $lng->txt("svy_eval_view_charts")
        ));
        $view->setValue($request->getVW());
        $toolbar->addInputItem($view, true);

        $button = \ilSubmitButton::getInstance();
        $button->setCaption("ok");
        $button->setCommand("evaluationdetails");
        $button->setOmitPreventDoubleSubmission(true);
        $toolbar->addButtonInstance($button);

        $toolbar->addSeparator();

        $this->addExportAndPrintButton(
            $survey,
            $toolbar,
            true
        );
    }

    protected function addExportAndPrintButton(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        bool $details
    ): void {
        $modal_id = "svy_ev_exp";
        $modal = $this->buildExportModal($modal_id, $details
            ? 'exportDetailData'
            : 'exportData');

        $button = \ilLinkButton::getInstance();
        $button->setCaption("export");
        $button->setOnClick('$(\'#' . $modal_id . '\').modal(\'show\')');
        $toolbar->addButtonInstance($button);

        $toolbar->addSeparator();

        if ($details) {
            $pv = $this->service->gui()->print()->resultsDetails($survey->getRefId());
            $this->service->gui()->ctrl()->setParameterByClass(
                "ilSurveyEvaluationGUI",
                "vw",
                $this->service->gui()->evaluation($survey)->request()->getVW()
            );
            $this->service->gui()->ctrl()->setParameterByClass(
                "ilSurveyEvaluationGUI",
                "cp",
                $this->service->gui()->evaluation($survey)->request()->getCP()
            );
            $modal_elements = $pv->getModalElements(
                $this->service->gui()->ctrl()->getLinkTargetByClass(
                    "ilSurveyEvaluationGUI",
                    "printResultsDetailsSelection"
                )
            );
        } else {
            $pv = $this->service->gui()->print()->resultsOverview($survey->getRefId());
            $modal_elements = $pv->getModalElements(
                $this->service->gui()->ctrl()->getLinkTargetByClass(
                    "ilSurveyEvaluationGUI",
                    "printResultsOverviewSelection"
                )
            );
        }

        $toolbar->addComponent($modal_elements->button);
        $toolbar->addComponent($modal_elements->modal);

        /*
        $button = \ilLinkButton::getInstance();
        $button->setCaption("print");
        $button->setOnClick("if(il.Accordion) { il.Accordion.preparePrint(); } window.print(); return false;");
        $button->setOmitPreventDoubleSubmission(true);
        $toolbar->addButtonInstance($button);*/

        $toolbar->addText($modal);
    }

    protected function buildExportModal(
        string $a_id,
        string $a_cmd
    ): string {
        $tpl = $this->service->gui()->mainTemplate();
        $lng = $this->service->gui()->lng();
        $ctrl = $this->service->gui()->ctrl();

        $form_id = "svymdfrm";

        // hide modal on form submit
        $tpl->addOnLoadCode('$("#form_' . $form_id . '").submit(function() { $("#' . $a_id . '").modal("hide"); });');

        $modal = \ilModalGUI::getInstance();
        $modal->setId($a_id);
        $modal->setHeading(($lng->txt("svy_export_format")));

        $form = new \ilPropertyFormGUI();
        $form->setId($form_id);
        $form->setFormAction($ctrl->getFormActionByClass("ilsurveyevaluationgui", $a_cmd));

        $format = new \ilSelectInputGUI($lng->txt("filetype"), "export_format");
        $format->setOptions(array(
            \ilSurveyEvaluationGUI::TYPE_XLS => $lng->txt('exp_type_excel'),
            \ilSurveyEvaluationGUI::TYPE_SPSS => $lng->txt('exp_type_csv')
        ));
        $form->addItem($format);

        $label = new \ilSelectInputGUI($lng->txt("title"), "export_label");
        $label->setOptions(array(
            'label_only' => $lng->txt('export_label_only'),
            'title_only' => $lng->txt('export_title_only'),
            'title_label' => $lng->txt('export_title_label')
        ));
        $form->addItem($label);

        $form->addCommandButton($a_cmd, $lng->txt("export"));
        $form->setPreventDoubleSubmission(false);

        $modal->setBody($form->getHTML());

        return $modal->getHTML();
    }

    public function addApprSelectionToToolbar(
        \ilObjSurvey $survey,
        \ilToolbarGUI $toolbar,
        int $user_id
    ): void {
        $lng = $this->service->gui()->lng();
        $ctrl = $this->service->gui()->ctrl();
        $req = $this->service->gui()->evaluation($survey)->request();

        $evaluation_manager = $this->service->domain()->evaluation(
            $survey,
            $user_id,
            $req->getAppraiseeId(),
            $req->getRaterId()
        );

        if ($evaluation_manager->isMultiParticipantsView()) {
            $appr_id = $evaluation_manager->getCurrentAppraisee();
            $options = array();
            if (!$appr_id) {
                $options[""] = $lng->txt("please_select");
            }

            foreach ($evaluation_manager->getSelectableAppraisees() as $appraisee_usr_id) {
                $options[$appraisee_usr_id] = \ilUserUtil::getNamePresentation(
                    $appraisee_usr_id,
                    false,
                    false,
                    "",
                    true
                );
            }

            $appr = new \ilSelectInputGUI($lng->txt("survey_360_appraisee"), "appr_id");
            $appr->setOptions($options);
            $appr->setValue($appr_id);
            $toolbar->addInputItem($appr, true);

            $button = \ilSubmitButton::getInstance();
            $button->setCaption("survey_360_select_appraisee");
            $button->setCommand($ctrl->getCmd());
            $toolbar->addButtonInstance($button);

            if ($appr_id) {
                $toolbar->addSeparator();
            }
        }
    }


    public function getDetailPanels(
        array $participants,
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ): array {
        $a_results = $a_eval->getResults();
        $panels = [];
        $ui_factory = $this->service->gui()->ui()->factory();

        $a_tpl = new \ilTemplate("tpl.svy_results_details_panel.html", true, true, "Modules/Survey/Evaluation");

        $question_res = $a_results;
        $matrix = false;
        if (is_array($question_res)) {
            $question_res = $question_res[0][1];
            $matrix = true;
        }

        // see #28507 (matrix question without a row)
        if (!is_object($question_res)) {
            return [];
        }

        $question = $question_res->getQuestion();

        // question "overview"
        $qst_title = $question->getTitle();
        $svy_text = nl2br($question->getQuestiontext());

        // Question title anchor
        $anchor_id = "svyrdq" . $question->getId();
        $title = "<span id='$anchor_id'>$qst_title</span>";
        $panel_qst_card = $ui_factory->panel()->sub($title, $ui_factory->legacy($svy_text))
            ->withFurtherInformation($this->getPanelCard($question_res));

        $panels[] = $panel_qst_card;

        $a_tpl->setVariable("TABLE", $this->getPanelTable(
            $participants,
            $request,
            $a_eval
        ));

        $a_tpl->setVariable("TEXT", $this->getPanelText(
            $request,
            $a_eval,
            $question_res
        ));

        $a_tpl->setVariable("CHART", $this->getPanelChart(
            $request,
            $a_eval
        ));


        $panels[] = $ui_factory->panel()->sub("", $ui_factory->legacy($a_tpl->get()));
        return $panels;
    }

    protected function getPanelTable(
        array $participants,
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ): string {
        $a_results = $a_eval->getResults();

        $a_tpl = new \ilTemplate("tpl.svy_results_details_table.html", true, true, "Modules/Survey/Evaluation");

        // grid
        if ($request->getShowTable()) {
            $grid = $a_eval->getGrid(
                $a_results,
                $request->getShowAbsolute(),
                $request->getShowPercentage()
            );
            if ($grid) {
                foreach ($grid["cols"] as $col) {
                    $a_tpl->setCurrentBlock("grid_col_header_bl");
                    $a_tpl->setVariable("COL_HEADER", $col);
                    $a_tpl->parseCurrentBlock();
                }
                foreach ($grid["rows"] as $cols) {
                    foreach ($cols as $idx => $col) {
                        if ($idx > 0) {
                            $a_tpl->touchBlock("grid_col_nowrap_bl");
                        }

                        $a_tpl->setCurrentBlock("grid_col_bl");
                        $a_tpl->setVariable("COL_CAPTION", trim((string) $col));
                        $a_tpl->parseCurrentBlock();
                    }

                    $a_tpl->touchBlock("grid_row_bl");
                }
            }
        }
        return $a_tpl->get();
    }

    protected function getPanelChart(
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval
    ): string {
        $a_results = $a_eval->getResults();

        $a_tpl = new \ilTemplate("tpl.svy_results_details_chart.html", true, true, "Modules/Survey/Evaluation");
        // chart
        if ($request->getShowChart()) {
            $chart = $a_eval->getChart($a_results);
            if ($chart) {
                if (is_array($chart)) {
                    // legend
                    if (is_array($chart[1])) {
                        foreach ($chart[1] as $legend_item) {
                            $r = hexdec(substr($legend_item[1], 1, 2));
                            $g = hexdec(substr($legend_item[1], 3, 2));
                            $b = hexdec(substr($legend_item[1], 5, 2));

                            $a_tpl->setCurrentBlock("legend_bl");
                            $a_tpl->setVariable("LEGEND_CAPTION", $legend_item[0]);
                            $a_tpl->setVariable("LEGEND_COLOR", $legend_item[1]);
                            $a_tpl->setVariable("LEGEND_COLOR_SVG", $r . "," . $g . "," . $b);
                            $a_tpl->parseCurrentBlock();
                        }
                    }

                    $chart = $chart[0];
                }

                $a_tpl->setVariable("CHART", $chart);
            }
        }

        return $a_tpl->get();
    }

    protected function getPanelText(
        \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request,
        \SurveyQuestionEvaluation $a_eval,
        \ilSurveyEvaluationResults $question_res
    ): string {
        $a_results = $a_eval->getResults();
        $question = $question_res->getQuestion();
        $lng = $this->service->gui()->lng();

        $a_tpl = new \ilTemplate("tpl.svy_results_details_text.html", true, true, "Modules/Survey/Evaluation");

        // text answers
        $texts = $a_eval->getTextAnswers($a_results);
        if ($texts) {
            if (array_key_exists("", $texts)) {
                $a_tpl->setVariable("TEXT_HEADING", $lng->txt("given_answers"));
                foreach ($texts[""] as $item) {
                    $a_tpl->setCurrentBlock("text_direct_item_bl");
                    $a_tpl->setVariable("TEXT_DIRECT", nl2br(htmlentities($item)));
                    $a_tpl->parseCurrentBlock();
                }
            } else {
                $acc = new \ilAccordionGUI();
                $acc->setId("svyevaltxt" . $question->getId());

                $a_tpl->setVariable("TEXT_HEADING", $lng->txt("freetext_answers"));

                foreach ($texts as $var => $items) {
                    $list = array("<ul class=\"small\">");
                    foreach ($items as $item) {
                        $list[] = "<li>" . nl2br(htmlentities($item)) . "</li>";
                    }
                    $list[] = "</ul>";
                    $acc->addItem((string) $var, implode("\n", $list));
                }

                $a_tpl->setVariable("TEXT_ACC", $acc->getHTML());
            }
        }
        return $a_tpl->get();
    }

    // in fact we want a \ILIAS\UI\Component\Card\Standard
    // see #31743
    protected function getPanelCard(
        \ilSurveyEvaluationResults $question_res
    ): \ILIAS\UI\Component\Card\Card {
        $ui_factory = $this->service->gui()->ui()->factory();
        $lng = $this->service->gui()->lng();

        $question = $question_res->getQuestion();
        $kv = array();
        $kv["users_answered"] = $question_res->getUsersAnswered();
        $kv["users_skipped"] = $question_res->getUsersSkipped();

        $card_table_tpl = new \ilTemplate(
            "tpl.svy_results_details_card.html",
            true,
            true,
            "Modules/Survey/Evaluation"
        );

        if (true) {     // formerly check for matrix type, shouldnt be needed
            if ($question_res->getModeValue() !== null) {
                $kv["mode"] = wordwrap($question_res->getModeValueAsText(), 50, "<br />");
                $kv["mode_nr_of_selections"] = $question_res->getModeNrOfSelections();
            }
            if ($question_res->getMedian() !== null) {
                $kv["median"] = $question_res->getMedianAsText();
            }
            if ($question_res->getMean() !== null) {
                $kv["arithmetic_mean"] = $question_res->getMean();
            }
        }

        foreach ($kv as $key => $value) {
            $card_table_tpl->setCurrentBlock("question_statistics_card");
            $card_table_tpl->setVariable("QUESTION_STATISTIC_KEY", $lng->txt($key));
            $card_table_tpl->setVariable("QUESTION_STATISTIC_VALUE", $value);
            $card_table_tpl->parseCurrentBlock();
        }

        $svy_type_title = \SurveyQuestion::_getQuestionTypeName($question->getQuestionType());

        return $ui_factory->card()
                          ->standard($svy_type_title)
                          ->withSections(
                              array($ui_factory->legacy($card_table_tpl->get()))
                          );
    }
}
