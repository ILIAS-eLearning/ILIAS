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
 * SingleChoice survey question GUI representation
 *
 * The SurveySingleChoiceQuestionGUI class encapsulates the GUI representation
 * for single choice survey question types.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveySingleChoiceQuestionGUI extends SurveyQuestionGUI
{
    protected function initObject(): void
    {
        $this->object = new SurveySingleChoiceQuestion();
    }

    //
    // EDITOR
    //

    public function setQuestionTabs(): void
    {
        $this->setQuestionTabsForClass("surveysinglechoicequestiongui");
    }

    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form): void
    {
        // orientation
        $orientation = new ilRadioGroupInputGUI($this->lng->txt("orientation"), "orientation");
        $orientation->setRequired(false);
        $orientation->addOption(new ilRadioOption($this->lng->txt('vertical'), 0));
        $orientation->addOption(new ilRadioOption($this->lng->txt('horizontal'), 1));
        $orientation->addOption(new ilRadioOption($this->lng->txt('combobox'), 2));
        $a_form->addItem($orientation);

        // Answers
        $answers = new ilCategoryWizardInputGUI($this->lng->txt("answers"), "answers");
        $answers->setRequired(false);
        $answers->setAllowMove(true);
        $answers->setShowWizard(true);
        $answers->setShowSavePhrase(true);
        $answers->setUseOtherAnswer(true);
        $answers->setShowNeutralCategory(true);
        $answers->setNeutralCategoryTitle($this->lng->txt('svy_neutral_answer'));
        $answers->setDisabledScale(false);
        $a_form->addItem($answers);

        // values
        $orientation->setValue($this->object->getOrientation());
        if (!$this->object->getCategories()->getCategoryCount()) {
            $this->object->getCategories()->addCategory("");
        }
        $answers->setValues($this->object->getCategories());
    }

    protected function importEditFormValues(ilPropertyFormGUI $a_form): void
    {
        $this->log->debug("importing edit values");

        $this->object->setOrientation($a_form->getInput("orientation"));

        $this->object->categories->flushCategories();
        $answers = $this->request->getAnswers();
        foreach ($answers['answer'] as $key => $value) {
            if (strlen($value)) {
                $this->object->getCategories()->addCategory(
                    $value,
                    $answers['other'][$key] ?? 0,
                    0,
                    null,
                    $answers['scale'][$key] ?? null
                );
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
            $user_answer = $a_working_data[0];
        }

        $options = array();
        for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
            $cat = $this->object->categories->getCategory($i);
            $value = ($cat->scale) ? ($cat->scale - 1) : $i;

            $checked = "unchecked";
            $text = null;
            if (is_array($a_working_data) &&
                is_array($user_answer)) {
                if ($value == $user_answer["value"]) {
                    $checked = "checked";
                    if ($user_answer["textanswer"]) {
                        $text = $user_answer["textanswer"];
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

        // rendering

        $template = new ilTemplate("tpl.il_svy_qpl_sc_printview.html", true, true, "Modules/SurveyQuestionPool");
        switch ($this->object->orientation) {
            case 0:
                // vertical orientation
                foreach ($options as $option) {
                    if ($option["other"]) {
                        $template->setCurrentBlock("other_row");
                        $template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_" . $option["checked"] . ".png")));
                        $template->setVariable("ALT_RADIO", $this->lng->txt($option["checked"]));
                        $template->setVariable("TITLE_RADIO", $this->lng->txt($option["checked"]));
                        $template->setVariable(
                            "OTHER_LABEL",
                            ilLegacyFormElementsUtil::prepareFormOutput($option["title"])
                        );
                        $template->setVariable("OTHER_ANSWER", $option["textanswer"]
                            ? ilLegacyFormElementsUtil::prepareFormOutput($option["textanswer"])
                            : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
                    } else {
                        $template->setCurrentBlock("row");
                        $template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_" . $option["checked"] . ".png")));
                        $template->setVariable("ALT_RADIO", $this->lng->txt($option["checked"]));
                        $template->setVariable("TITLE_RADIO", $this->lng->txt($option["checked"]));
                        $template->setVariable("TEXT_SC", ilLegacyFormElementsUtil::prepareFormOutput($option["title"]));
                    }
                    $template->parseCurrentBlock();
                }
                break;
            case 1:
                // horizontal orientation
                foreach ($options as $option) {
                    $template->setCurrentBlock("radio_col");
                    $template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_" . $option["checked"] . ".png")));
                    $template->setVariable("ALT_RADIO", $this->lng->txt($option["checked"]));
                    $template->setVariable("TITLE_RADIO", $this->lng->txt($option["checked"]));
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
                            : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
                    } else {
                        $template->setCurrentBlock("text_col");
                        $template->setVariable("TEXT_SC", ilLegacyFormElementsUtil::prepareFormOutput($option["title"]));
                    }
                    $template->parseCurrentBlock();
                }
                break;
            case 2:
                foreach ($options as $option) {
                    $template->setCurrentBlock("comborow");
                    $template->setVariable("TEXT_SC", ilLegacyFormElementsUtil::prepareFormOutput($option["title"]));
                    $template->setVariable("VALUE_SC", $option["value"]);
                    if ($option["checked"] === "checked") {
                        $template->setVariable("SELECTED_SC", ' selected="selected"');
                    }
                    $template->parseCurrentBlock();
                }
                $template->setCurrentBlock("combooutput");
                $template->setVariable("QUESTION_ID", $this->object->getId());
                $template->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
                $template->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
                $template->parseCurrentBlock();
                break;
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->getPrintViewQuestionTitle($question_title));
        }
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->parseCurrentBlock();
        return $template->get();
    }


    //
    // EXECUTION
    //

    public function getWorkingForm(
        array $working_data = null,
        int $question_title = 1,
        bool $show_questiontext = true,
        string $error_message = "",
        int $survey_id = null,
        bool $compress_view = false
    ): string {
        $orientation = $this->object->orientation;
        $template_file = "tpl.il_svy_out_sc.html";
        if ($compress_view && $orientation === 1) {
            $template_file = "tpl.il_svy_out_sc_comp.html";
            $orientation = 3;
        }
        $template = new ilTemplate($template_file, true, true, "Modules/SurveyQuestionPool");
        if ($this->getMaterialOutput() !== "") {
            $template->setCurrentBlock("material");
            $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
            $template->parseCurrentBlock();
        }
        switch ($orientation) {
            case 0:
                // vertical orientation
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    $debug_scale = ($cat->scale) ? ($cat->scale - 1) : $i;
                    $this->log->debug("Vertical orientation - Original scale = " . $cat->scale . " If(scale) scale -1 else i. The new scale value is = " . $debug_scale);

                    if ($cat->other) {
                        $template->setCurrentBlock("other_row");
                        if (strlen($cat->title)) {
                            $template->setVariable("OTHER_LABEL", $cat->title);
                        }
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strlen($value["value"])) {
                                    if ($value["value"] == $cat->scale - 1) {
                                        if (strlen($value['textanswer'])) {
                                            $template->setVariable("OTHER_VALUE", ' value="' . ilLegacyFormElementsUtil::prepareFormOutput(
                                                $value['textanswer']
                                            ) . '"');
                                        }
                                        if (!($value['uncheck'] ?? false)) {
                                            $template->setVariable("CHECKED_SC", " checked=\"checked\"");
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $template->setCurrentBlock("row");
                        if ($cat->neutral) {
                            $template->setVariable('ROWCLASS', ' class="neutral"');
                        }
                        $template->setVariable("TEXT_SC", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strcmp($value["value"], "") !== 0) {
                                    if ($value["value"] == $cat->scale - 1) {
                                        if (!($value['uncheck'] ?? false)) {
                                            $template->setVariable("CHECKED_SC", " checked=\"checked\"");
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
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    $debug_scale = ($cat->scale) ? ($cat->scale - 1) : $i;
                    $this->log->debug("Horizontal orientation - Original NEUTRAL scale = " . $cat->scale . " If(scale) scale -1 else i. The new scale value is = " . $debug_scale);

                    $template->setCurrentBlock("radio_col");
                    if ($cat->neutral) {
                        $template->setVariable('COLCLASS', ' neutral');
                    }
                    $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                    $template->setVariable("QUESTION_ID", $this->object->getId());
                    if (is_array($working_data)) {
                        foreach ($working_data as $value) {
                            if (strcmp($value["value"], "") !== 0) {
                                if ($value["value"] == $cat->scale - 1) {
                                    if (!($value['uncheck'] ?? false)) {
                                        $template->setVariable("CHECKED_SC", " checked=\"checked\"");
                                    }
                                }
                            }
                        }
                    }
                    $template->parseCurrentBlock();
                }
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    $debug_scale = ($cat->scale) ? ($cat->scale - 1) : $i;
                    $this->log->debug("Horizontal orientation - Original scale = " . $cat->scale . " If(scale) scale -1 else i. The new scale value is = " . $debug_scale);

                    if ($cat->other) {
                        $template->setCurrentBlock("text_other_col");
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (strlen($cat->title)) {
                            $template->setVariable("OTHER_LABEL", $cat->title);
                        }
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strlen($value["value"])) {
                                    if ($value["value"] == $cat->scale - 1 && strlen($value['textanswer'])) {
                                        $template->setVariable("OTHER_VALUE", ' value="' . ilLegacyFormElementsUtil::prepareFormOutput(
                                            $value['textanswer']
                                        ) . '"');
                                    }
                                }
                            }
                        }
                    } else {
                        $template->setCurrentBlock("text_col");
                        if ($cat->neutral) {
                            $template->setVariable('COLCLASS', ' neutral');
                        }
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("TEXT_SC", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                    }
                    $template->parseCurrentBlock();
                    $template->touchBlock('text_outer_col');
                }
                break;
            case 2:
                // combobox output
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    $debug_scale = ($cat->scale) ? ($cat->scale - 1) : $i;
                    $this->log->debug("Combobox - Original scale = " . $cat->scale . " If(scale) scale -1 else i. The new scale value is = " . $debug_scale);

                    $template->setCurrentBlock("comborow");
                    $template->setVariable("TEXT_SC", $cat->title);
                    $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                    if (is_array($working_data)) {
                        if (strcmp($working_data[0]["value"] ?? "", "") !== 0) {
                            if ($working_data[0]["value"] == $cat->scale - 1) {
                                $template->setVariable("SELECTED_SC", " selected=\"selected\"");
                            }
                        }
                    }
                    $template->parseCurrentBlock();
                }
                $template->setCurrentBlock("combooutput");
                $template->setVariable("QUESTION_ID", $this->object->getId());
                $template->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
                $template->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
                $template->parseCurrentBlock();
                break;
            case 3:
                // horizontal orientation, compressed
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    $debug_scale = ($cat->scale) ? ($cat->scale - 1) : $i;
                    $this->log->debug("Horizontal orientation (compressed) - Original NEUTRAL scale = " . $cat->scale . " If(scale) scale -1 else i. The new scale value is = " . $debug_scale);

                    if ($cat->other) {
                        $template->setCurrentBlock("other");
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("OTHER_Q_ID", $this->object->getId());
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strlen($value["value"])) {
                                    if ($value["value"] == $cat->scale - 1 && strlen($value['textanswer'])) {
                                        $template->setVariable("OTHER_VALUE", ' value="' . ilLegacyFormElementsUtil::prepareFormOutput(
                                            $value['textanswer']
                                        ) . '"');
                                    }
                                }
                            }
                        }
                        $template->parseCurrentBlock();
                    }


                    $template->setCurrentBlock("radio_col");
                    if ($cat->neutral) {
                        $template->setVariable('COLCLASS', ' neutral');
                    }
                    $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                    $template->setVariable("QUESTION_ID", $this->object->getId());
                    if (is_array($working_data)) {
                        foreach ($working_data as $value) {
                            if (strcmp($value["value"], "") !== 0) {
                                if ($value["value"] == $cat->scale - 1) {
                                    if (!($value['uncheck'] ?? false)) {
                                        $template->setVariable("CHECKED_SC", " checked=\"checked\"");
                                    }
                                }
                            }
                        }
                    }
                    $template->parseCurrentBlock();
                }
                $perc = round(70 / $this->object->categories->getCategoryCount(), 2);
                for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i++) {
                    $cat = $this->object->categories->getCategory($i);

                    $debug_scale = ($cat->scale) ? ($cat->scale - 1) : $i;
                    $this->log->debug("Horizontal orientation - Original scale = " . $cat->scale . " If(scale) scale -1 else i. The new scale value is = " . $debug_scale);

                    $template->setCurrentBlock("text_col");
                    if ($cat->neutral) {
                        $template->setVariable('COLCLASS', ' neutral');
                    }
                    $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                    $template->setVariable("TEXT_SC", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                    $template->setVariable("PERC", $perc);
                    $template->setVariable("QUESTION_ID", $this->object->getId());
                    $template->parseCurrentBlock();
                }
                break;
        }
        $template->setVariable("QUESTION_TITLE", $this->getQuestionTitle($question_title));
        $template->setCurrentBlock("question_data");
        if (strcmp($error_message, "") !== 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->parseCurrentBlock();
        return $template->get();
    }
}
