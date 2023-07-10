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
 * MultipleChoice survey question GUI representation
 * The SurveyMultipleChoiceQuestionGUI class encapsulates the GUI representation
 * for multiple choice survey question types.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyMultipleChoiceQuestionGUI extends SurveyQuestionGUI
{
    protected function initObject(): void
    {
        $this->object = new SurveyMultipleChoiceQuestion();
    }

    //
    // EDITOR
    //

    public function setQuestionTabs(): void
    {
        $this->setQuestionTabsForClass("surveymultiplechoicequestiongui");
    }

    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form): void
    {
        // orientation
        $orientation = new ilRadioGroupInputGUI($this->lng->txt("orientation"), "orientation");
        $orientation->setRequired(false);
        $orientation->addOption(new ilRadioOption($this->lng->txt('vertical'), 0));
        $orientation->addOption(new ilRadioOption($this->lng->txt('horizontal'), 1));
        $a_form->addItem($orientation);

        // minimum answers
        $minanswers = new ilCheckboxInputGUI($this->lng->txt("use_min_answers"), "use_min_answers");
        $minanswers->setValue(1);
        $minanswers->setOptionTitle($this->lng->txt("use_min_answers_option"));
        $minanswers->setRequired(false);

        $nranswers = new ilNumberInputGUI($this->lng->txt("nr_min_answers"), "nr_min_answers");
        $nranswers->setSize(5);
        $nranswers->setDecimals(0);
        $nranswers->setRequired(false);
        $nranswers->setMinValue(1);
        $minanswers->addSubItem($nranswers);

        $nrmaxanswers = new ilNumberInputGUI($this->lng->txt("nr_max_answers"), "nr_max_answers");
        $nrmaxanswers->setSize(5);
        $nrmaxanswers->setDecimals(0);
        $nrmaxanswers->setRequired(false);
        $nrmaxanswers->setMinValue(1);
        $minanswers->addSubItem($nrmaxanswers);

        $a_form->addItem($minanswers);

        // Answers
        $answers = new ilCategoryWizardInputGUI($this->lng->txt("answers"), "answers");
        $answers->setRequired(false);
        $answers->setAllowMove(true);
        $answers->setShowWizard(false);
        $answers->setShowSavePhrase(false);
        $answers->setUseOtherAnswer(true);
        $answers->setShowNeutralCategory(true);
        $answers->setNeutralCategoryTitle($this->lng->txt('svy_neutral_answer'));
        $answers->setDisabledScale(false);
        $a_form->addItem($answers);


        // values
        $orientation->setValue($this->object->getOrientation());
        $minanswers->setChecked((bool) $this->object->use_min_answers);
        $nranswers->setValue($this->object->nr_min_answers);
        $nrmaxanswers->setValue($this->object->nr_max_answers);
        if (!$this->object->getCategories()->getCategoryCount()) {
            $this->object->getCategories()->addCategory("");
        }
        $answers->setValues($this->object->getCategories());
    }

    protected function validateEditForm(ilPropertyFormGUI $a_form): bool
    {
        $errors = false;
        if ($a_form->getInput("use_min_answers")) {
            // #13927 - see importEditFormValues()
            $cnt_answers = 0;
            $answers = $this->request->getAnswers();
            foreach ($answers['answer'] as $key => $value) {
                if (strlen($value)) {
                    $cnt_answers++;
                }
            }
            if ($this->request->getNeutral() !== "") {
                $cnt_answers++;
            }
            /* this would be the DB-values
            $cnt_answers = $a_form->getItemByPostVar("answers");
            $cnt_answers = $cnt_answers->getCategoryCount();
            */
            $min_anwers = $a_form->getInput("nr_min_answers");
            $max_anwers = $a_form->getInput("nr_max_answers");

            if ($min_anwers &&
                $min_anwers > $cnt_answers) {
                $a_form->getItemByPostVar("nr_min_answers")->setAlert($this->lng->txt('err_minvalueganswers'));
                $errors = true;
            }
            if ($max_anwers > 0 &&
                ($max_anwers > $cnt_answers || $max_anwers < $min_anwers)) {
                $a_form->getItemByPostVar("nr_max_answers")->setAlert($this->lng->txt('err_maxvaluegeminvalue'));
                $errors = true;
            }
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        return !$errors;
    }

    protected function importEditFormValues(ilPropertyFormGUI $a_form): void
    {
        $this->object->setOrientation($a_form->getInput("orientation"));
        $this->object->use_other_answer = ($a_form->getInput('use_other_answer')) ? 1 : 0;
        $this->object->other_answer_label = $this->object->use_other_answer ? $a_form->getInput('other_answer_label') : "";
        $this->object->use_min_answers = (bool) $a_form->getInput('use_min_answers');
        $this->object->nr_min_answers = ($a_form->getInput('nr_min_answers') > 0) ? $a_form->getInput('nr_min_answers') : "";
        $this->object->nr_max_answers = ($a_form->getInput('nr_max_answers') > 0) ? $a_form->getInput('nr_max_answers') : "";
        $this->object->label = $a_form->getInput('label');

        $this->object->categories->flushCategories();

        $answers = $this->request->getAnswers();
        foreach ($answers['answer'] as $key => $value) {
            if (strlen($value)) {
                $this->object->getCategories()->addCategory($value, $answers['other'][$key] ?? 0, 0, null, $answers['scale'][$key]);
            }
        }
        if ($this->request->getNeutral() !== "") {
            $this->object->getCategories()->addCategory($this->request->getNeutral(), 0, 1, null, $this->request->getNeutralScale());
        }
    }

    public function getParsedAnswers(
        array $a_working_data = null,
        $a_only_user_anwers = false
    ): array {
        if (is_array($a_working_data)) {
            $user_answers = $a_working_data;
        }

        $options = array();
        for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
            $cat = $this->object->categories->getCategory($i);
            $value = ($cat->scale) ? ($cat->scale - 1) : $i;

            $checked = "unchecked";
            $text = null;
            if (is_array($a_working_data)) {
                foreach ($user_answers as $user_answer) {
                    if ($value == $user_answer["value"]) {
                        $checked = "checked";
                        if ($user_answer["textanswer"]) {
                            $text = $user_answer["textanswer"];
                        }
                        break;
                    }
                }
            }

            // "other" options have to be last or horizontal will be screwed
            $idx = $cat->other . "_" . $value;

            if (!$a_only_user_anwers || $checked === "checked") {
                $options[$idx] = array(
                "value" => $value
                ,"title" => trim($cat->title)
                ,"other" => (bool) $cat->other
                ,"checked" => $checked
                ,"textanswer" => $text
            );
            }

            ksort($options);
        }

        return array_values($options);
    }

    public function getPrintView(
        int $question_title = 1,
        bool $show_questiontext = true,
        ?int $survey_id = null,
        ?array $working_data = null
    ): string {
        $options = $this->getParsedAnswers($working_data);

        $template = new ilTemplate("tpl.il_svy_qpl_mc_printview.html", true, true, "Modules/SurveyQuestionPool");
        switch ($this->object->getOrientation()) {
            case 0:
                // vertical orientation
                foreach ($options as $option) {
                    if ($option["other"]) {
                        $template->setCurrentBlock("other_row");
                        $template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_" . $option["checked"] . ".png")));
                        $template->setVariable("ALT_CHECKBOX", $this->lng->txt($option["checked"]));
                        $template->setVariable("TITLE_CHECKBOX", $this->lng->txt($option["checked"]));
                        $template->setVariable(
                            "OTHER_LABEL",
                            ilLegacyFormElementsUtil::prepareFormOutput($option["title"])
                        );
                        $template->setVariable("OTHER_ANSWER", $option["textanswer"]
                            ? ilLegacyFormElementsUtil::prepareFormOutput($option["textanswer"])
                            : "&nbsp;");
                    } else {
                        $template->setCurrentBlock("mc_row");
                        $template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_" . $option["checked"] . ".png")));
                        $template->setVariable("ALT_CHECKBOX", $this->lng->txt($option["checked"]));
                        $template->setVariable("TITLE_CHECKBOX", $this->lng->txt($option["checked"]));
                        $template->setVariable("TEXT_MC", ilLegacyFormElementsUtil::prepareFormOutput($option["title"]));
                    }
                    $template->parseCurrentBlock();
                }
                break;
            case 1:
                // horizontal orientation
                foreach ($options as $option) {
                    $template->setCurrentBlock("checkbox_col");
                    $template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_" . $option["checked"] . ".png")));
                    $template->setVariable("ALT_CHECKBOX", $this->lng->txt($option["checked"]));
                    $template->setVariable("TITLE_CHECKBOX", $this->lng->txt($option["checked"]));
                    $template->parseCurrentBlock();
                }
                foreach ($options as $option) {
                    if ($option["other"]) {
                        $template->setCurrentBlock("other_text_col");
                        $template->setVariable(
                            "OTHER_LABEL",
                            ilLegacyFormElementsUtil::prepareFormOutput($option["title"])
                        );
                        $template->setVariable("OTHER_ANSWER", $option["textanswer"]
                            ? ilLegacyFormElementsUtil::prepareFormOutput($option["textanswer"])
                            : "&nbsp;");
                    } else {
                        $template->setCurrentBlock("text_col");
                        $template->setVariable("TEXT_MC", ilLegacyFormElementsUtil::prepareFormOutput($option["title"]));
                    }
                    $template->parseCurrentBlock();
                }
                break;
        }

        if ($this->object->use_min_answers) {
            $template->setCurrentBlock('min_max_msg');
            if ($this->object->nr_min_answers > 0 && $this->object->nr_max_answers > 0) {
                $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_nr_answers'), $this->object->nr_min_answers, $this->object->nr_max_answers));
            } elseif ($this->object->nr_min_answers > 0) {
                $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_nr_answers'), $this->object->nr_min_answers));
            } elseif ($this->object->nr_max_answers > 0) {
                $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_max_nr_answers'), $this->object->nr_max_answers));
            }
            $template->parseCurrentBlock();
        }
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->getPrintViewQuestionTitle($question_title));
        }
        $template->parseCurrentBlock();
        return $template->get();
    }


    //
    // EXECUTION
    //

    /**
     * Creates the question output form for the learner
     */
    public function getWorkingForm(
        array $working_data = null,
        int $question_title = 1,
        bool $show_questiontext = true,
        string $error_message = "",
        int $survey_id = null,
        bool $compress_view = false
    ): string {
        $template = new ilTemplate("tpl.il_svy_out_mc.html", true, true, "Modules/SurveyQuestionPool");
        $template->setCurrentBlock("material");
        $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
        $template->parseCurrentBlock();
        switch ($this->object->getOrientation()) {
            case 0:
                // vertical orientation
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);
                    if ($cat->other) {
                        $template->setCurrentBlock("other_row");
                        if (strlen($cat->title)) {
                            $template->setVariable("OTHER_LABEL", $cat->title);
                        }
                        $template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strlen($value["value"])) {
                                    if ($value["value"] == $cat->scale - 1) {
                                        $template->setVariable("OTHER_VALUE", ' value="' . ilLegacyFormElementsUtil::prepareFormOutput(
                                            $value['textanswer']
                                        ) . '"');
                                        if (!($value['uncheck'] ?? false)) {
                                            $template->setVariable("CHECKED_MC", " checked=\"checked\"");
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $template->setCurrentBlock("mc_row");
                        if ($cat->neutral) {
                            $template->setVariable('ROWCLASS', ' class="neutral"');
                        }
                        $template->setVariable("TEXT_MC", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                        $template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strlen($value["value"])) {
                                    if ($value["value"] == $cat->scale - 1) {
                                        if (!($value['uncheck'] ?? false)) {
                                            $template->setVariable("CHECKED_MC", " checked=\"checked\"");
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $template->parseCurrentBlock();
                    $template->touchBlock('outer_row');
                }
                break;
            case 1:
                // horizontal orientation

                // #15477 - reverting the categorizing of answers
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    // checkbox
                    $template->setCurrentBlock("checkbox_col");
                    if ($cat->neutral) {
                        $template->setVariable('COLCLASS', ' neutral');
                    }
                    $template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
                    $template->setVariable("QUESTION_ID", $this->object->getId());
                    if (is_array($working_data)) {
                        foreach ($working_data as $value) {
                            if (strlen($value["value"])) {
                                if ($value["value"] == $cat->scale - 1) {
                                    if (!($value['uncheck'] ?? false)) {
                                        $template->setVariable("CHECKED_MC", " checked=\"checked\"");
                                    }
                                }
                            }
                        }
                    }
                    $template->parseCurrentBlock();

                    // answer & input
                    if ($cat->other) {
                        $template->setCurrentBlock("text_other_col");
                        $template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (strlen($cat->title)) {
                            $template->setVariable("OTHER_LABEL", $cat->title);
                        }
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strlen($value["value"])) {
                                    if ($value["value"] == $cat->scale - 1) {
                                        $template->setVariable("OTHER_VALUE", ' value="' . ilLegacyFormElementsUtil::prepareFormOutput(
                                            $value['textanswer']
                                        ) . '"');
                                    }
                                }
                            }
                        }
                    }
                    // answer
                    else {
                        $template->setCurrentBlock("text_col");
                        if ($cat->neutral) {
                            $template->setVariable('COLCLASS', ' neutral');
                        }
                        $template->setVariable("VALUE_MC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("TEXT_MC", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                    }
                    $template->parseCurrentBlock();
                    $template->touchBlock('text_outer_col');
                }
                break;
        }

        $template->setCurrentBlock("question_data");
        if ($this->object->use_min_answers) {
            $template->setCurrentBlock('min_max_msg');
            if ($this->object->nr_min_answers > 0 && $this->object->nr_max_answers > 0) {
                if ($this->object->nr_min_answers == $this->object->nr_max_answers) {
                    $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_exact_answers'), $this->object->nr_min_answers));
                } else {
                    $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_nr_answers'), $this->object->nr_min_answers, $this->object->nr_max_answers));
                }
            } elseif ($this->object->nr_min_answers > 0) {
                $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_nr_answers'), $this->object->nr_min_answers));
            } elseif ($this->object->nr_max_answers > 0) {
                $template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_max_nr_answers'), $this->object->nr_max_answers));
            }
            $template->parseCurrentBlock();
        }
        if (strcmp($error_message, "") !== 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->setVariable("QUESTION_TITLE", $this->getQuestionTitle($question_title));
        $template->parseCurrentBlock();
        return $template->get();
    }
}
