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

namespace ILIAS\Survey\Page;

/**
 * Survey page renderer
 * @author Alexander Killing <killing@leifos.de>
 */
class PageRenderer
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected array $page_data;
    protected array $working_data = [];
    protected array $errors = [];
    protected \ilObjSurvey $survey;

    /**
     * @param array $page_data as returned by ilObjSurvey->getNextPage()
     * @todo use data objects
     */
    public function __construct(
        \ilObjSurvey $survey,
        array $page_data,
        array $working_data = [],
        array $errors = []
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->survey = $survey;
        $this->page_data = $page_data;
        $this->working_data = $working_data;
        $this->errors = $errors;
    }

    public function render(): string
    {
        $page = $this->page_data;

        $required = false;
        $stpl = new \ilTemplate("tpl.page.html", true, true, "Modules/Survey/Page");

        // question block title
        if (count($page) > 1 && $page[0]["questionblock_show_blocktitle"]) {
            $stpl->setCurrentBlock("questionblock_title");
            $stpl->setVariable("TEXT_QUESTIONBLOCK_TITLE", $page[0]["questionblock_title"]);
            $stpl->parseCurrentBlock();
        }

        // dealing with compressed view
        $compress_view = false;
        if (count($page) > 1) {
            $compress_view = $page[0]["questionblock_compress_view"];
        }
        $previous_page = null;
        $previous_key = null;
        foreach ($page as $k => $data) {
            $page[$k]["compressed"] = false;
            $page[$k]["compressed_first"] = false;
            if ($compress_view && $this->compressQuestion($previous_page, $data)) {
                $page[$k]["compressed"] = true;
                if ($previous_key !== null && $page[$previous_key]["compressed"] == false) {
                    $page[$previous_key]["compressed_first"] = true;
                }
            }
            $previous_key = $k;
            $previous_page = $data;
        }

        // questions
        foreach ($page as $data) {

            // question heading
            if ($data["heading"]) {
                $stpl->setCurrentBlock("heading");
                $stpl->setVariable("QUESTION_HEADING", $data["heading"]);
                $stpl->parseCurrentBlock();
            }
            $stpl->setCurrentBlock("survey_content");
            // get question gui
            $question_gui = $this->survey->getQuestionGUI($data["type_tag"], $data["question_id"]);

            // set obligatory flag
            $question_gui->object->setObligatory($data["obligatory"]);

            // get show questiontext flag
            $show_questiontext = ($data["questionblock_show_questiontext"]) ? 1 : 0;

            // get show title flag
            $show_title = ($this->survey->getShowQuestionTitles() && !$data["compressed_first"]);

            $working_data = $this->working_data[$data["question_id"]] ?? null;
            $error = $this->errors[$data["question_id"]] ?? "";

            // get question output
            // getWorkingData($qid)
            // showQuestionTitle()
            $question_output = $question_gui->getWorkingForm(
                $working_data,
                $show_title,
                $show_questiontext,
                $error,
                $this->survey->getSurveyId(),
                $compress_view
            );

            // tweak compressed view
            if ($data["compressed"]) {
                //$question_output = '<div class="il-svy-qst-compressed">' . $question_output . '</div>';

                $stpl->setVariable("CMPR_CLASS", "il-svy-qst-compressed");
            }
            $stpl->setVariable("QUESTION_OUTPUT", $question_output);

            // update qid ctrl parameter
            $this->ctrl->setParameter($this, "qid", $data["question_id"]);

            if ($data["obligatory"]) {
                $required = true;
            }
            $stpl->parseCurrentBlock();
        }

        // required text action
        if ($required) {
            $stpl->setCurrentBlock("required");
            $stpl->setVariable("TEXT_REQUIRED", $this->lng->txt("required_field"));
            $stpl->parseCurrentBlock();
        }

        return $stpl->get();
    }

    protected function compressQuestion(
        ?array $previous_page,
        array $page
    ): bool {
        if (is_null($previous_page)) {
            return false;
        }

        if ($previous_page["type_tag"] === $page["type_tag"] &&
            $page["type_tag"] === "SurveySingleChoiceQuestion") {
            if (\SurveySingleChoiceQuestion::compressable($previous_page["question_id"], $page["question_id"])) {
                return true;
            }
        }

        return false;
    }
}
