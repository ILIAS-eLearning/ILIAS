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
 * Text survey question GUI representation
 * The SurveyTextQuestionGUI class encapsulates the GUI representation
 * for text survey question types.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyTextQuestionGUI extends SurveyQuestionGUI
{
    protected function initObject(): void
    {
        $this->object = new SurveyTextQuestion();
    }


    //
    // EDITOR
    //

    public function setQuestionTabs(): void
    {
        $this->setQuestionTabsForClass("surveytextquestiongui");
    }

    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form): void
    {
        // maximum number of characters
        $maxchars = new ilNumberInputGUI($this->lng->txt("maxchars"), "maxchars");
        $maxchars->setRequired(false);
        $maxchars->setSize(5);
        $maxchars->setDecimals(0);
        $a_form->addItem($maxchars);

        // textwidth
        $textwidth = new ilNumberInputGUI($this->lng->txt("width"), "textwidth");
        $textwidth->setRequired(true);
        $textwidth->setSize(3);
        $textwidth->setDecimals(0);
        $textwidth->setMinValue(10, true);
        $a_form->addItem($textwidth);

        // textheight
        $textheight = new ilNumberInputGUI($this->lng->txt("height"), "textheight");
        $textheight->setRequired(true);
        $textheight->setSize(3);

        $textheight->setDecimals(0);
        $textheight->setMinValue(1);
        $a_form->addItem($textheight);

        // values
        if ($this->object->getMaxChars() > 0) {
            $maxchars->setValue($this->object->getMaxChars());
        }
        $textwidth->setValue($this->object->getTextWidth());
        $textheight->setValue($this->object->getTextHeight());
    }

    protected function importEditFormValues(ilPropertyFormGUI $a_form): void
    {
        $max = $a_form->getInput("maxchars");
        $this->object->setMaxChars((int) $max);
        $this->object->setTextWidth($a_form->getInput("textwidth"));
        $this->object->setTextHeight($a_form->getInput("textheight"));
    }

    public function getParsedAnswers(
        array $a_working_data = null,
        $a_only_user_anwers = false
    ): array {
        $res = array();

        if (is_array($a_working_data)) {
            $res[] = array("textanswer" => trim($a_working_data[0]["textanswer"]));
        }

        return $res;
    }

    public function getPrintView(
        int $question_title = 1,
        bool $show_questiontext = true,
        ?int $survey_id = null,
        ?array $working_data = null
    ): string {
        $user_answer = null;
        if ($working_data) {
            $user_answer = $this->getParsedAnswers($working_data);
            $user_answer = $user_answer[0]["textanswer"];
        }

        $template = new ilTemplate("tpl.il_svy_qpl_text_printview.html", true, true, "Modules/SurveyQuestionPool");
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->getPrintViewQuestionTitle($question_title));
        }
        $template->setVariable("QUESTION_ID", $this->object->getId());
        $template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        if (is_array($working_data) && trim($user_answer)) {
            $template->setVariable("TEXT", nl2br($user_answer));
        } else {
            $template->setVariable("TEXTBOX_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("textbox.png")));
            $template->setVariable("TEXTBOX", $this->lng->txt("textbox"));
            $template->setVariable("TEXTBOX_WIDTH", $this->object->getTextWidth() * 16);
            $template->setVariable("TEXTBOX_HEIGHT", $this->object->getTextHeight() * 16);
        }
        if ($this->object->getMaxChars()) {
            $template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
        }
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
        $template = new ilTemplate("tpl.il_svy_out_text.html", true, true, "Modules/SurveyQuestionPool");
        $template->setCurrentBlock("material_text");
        $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
        $template->parseCurrentBlock();

        if ($this->object->getTextHeight() === 1) {
            $template->setCurrentBlock("textinput");
            if (is_array($working_data)) {
                if (isset($working_data[0]["textanswer"])) {
                    $template->setVariable("VALUE_ANSWER", " value=\"" . ilLegacyFormElementsUtil::prepareFormOutput($working_data[0]["textanswer"]) . "\"");
                }
            }
            $template->setVariable("QUESTION_ID", $this->object->getId());
            $template->setVariable("WIDTH", $this->object->getTextWidth());
            if ($this->object->getMaxChars()) {
                $template->setVariable("MAXLENGTH", " maxlength=\"" . $this->object->getMaxChars() . "\"");
            }
        } else {
            $template->setCurrentBlock("textarea");
            if (is_array($working_data) && isset($working_data[0]["textanswer"])) {
                $template->setVariable("VALUE_ANSWER", ilLegacyFormElementsUtil::prepareFormOutput($working_data[0]["textanswer"]));
            }
            $template->setVariable("QUESTION_ID", $this->object->getId());
            $template->setVariable("WIDTH", $this->object->getTextWidth());
            $template->setVariable("HEIGHT", $this->object->getTextHeight());
        }
        $template->parseCurrentBlock();
        $template->setCurrentBlock("question_data_text");
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->setVariable("QUESTION_TITLE", $this->getQuestionTitle($question_title));
        $template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $template->setVariable("LABEL_QUESTION_ID", $this->object->getId());
        if (strcmp($error_message, "") !== 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        if ($this->object->getMaxChars()) {
            $template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
        }
        $template->parseCurrentBlock();
        return $template->get();
    }
}
