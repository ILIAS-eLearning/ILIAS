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
 * Metric survey question GUI representation
 * The SurveyMetricQuestionGUI class encapsulates the GUI representation
 * for metric survey question types.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyMetricQuestionGUI extends SurveyQuestionGUI
{
    protected function initObject() : void
    {
        $this->object = new SurveyMetricQuestion();
    }

    
    //
    // EDITOR
    //
    
    public function setQuestionTabs() : void
    {
        $this->setQuestionTabsForClass("surveymetricquestiongui");
    }
    
    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form) : void
    {
        // subtype
        $subtype = new ilRadioGroupInputGUI($this->lng->txt("subtype"), "type");
        $subtype->setRequired(true);
        $a_form->addItem($subtype);
                
        // #10652
        $opt = new ilRadioOption($this->lng->txt('non_ratio'), SurveyMetricQuestion::SUBTYPE_NON_RATIO, $this->lng->txt("metric_subtype_description_interval"));
        $subtype->addOption($opt);
        
        // minimum value
        $minimum1 = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum3");
        $minimum1->setRequired(false);
        $minimum1->setSize(6);
        $opt->addSubItem($minimum1);
        
        // maximum value
        $maximum1 = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum3");
        $maximum1->setRequired(false);
        $maximum1->setSize(6);
        $opt->addSubItem($maximum1);
        
        $opt = new ilRadioOption($this->lng->txt('ratio_non_absolute'), SurveyMetricQuestion::SUBTYPE_RATIO_NON_ABSOLUTE, $this->lng->txt("metric_subtype_description_rationonabsolute"));
        $subtype->addOption($opt);
        
        // minimum value
        $minimum2 = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum4");
        $minimum2->setRequired(false);
        $minimum2->setSize(6);
        $minimum2->setMinValue(0);
        $opt->addSubItem($minimum2);
        
        // maximum value
        $maximum2 = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum4");
        $maximum2->setRequired(false);
        $maximum2->setSize(6);
        $opt->addSubItem($maximum2);
        
        $opt = new ilRadioOption($this->lng->txt('ratio_absolute'), SurveyMetricQuestion::SUBTYPE_RATIO_ABSOLUTE, $this->lng->txt("metric_subtype_description_ratioabsolute"));
        $subtype->addOption($opt);
        
        // minimum value
        $minimum3 = new ilNumberInputGUI($this->lng->txt("minimum"), "minimum5");
        $minimum3->setRequired(false);
        $minimum3->setSize(6);
        $minimum3->setMinValue(0);
        $minimum3->setDecimals(0);
        $opt->addSubItem($minimum3);
        
        // maximum value
        $maximum3 = new ilNumberInputGUI($this->lng->txt("maximum"), "maximum5");
        $maximum3->setDecimals(0);
        $maximum3->setRequired(false);
        $maximum3->setSize(6);
        $opt->addSubItem($maximum3);
        
        
        // values
        $subtype->setValue($this->object->getSubtype());
        
        switch ($this->object->getSubtype()) {
            case SurveyMetricQuestion::SUBTYPE_NON_RATIO:
                $minimum1->setValue($this->object->getMinimum());
                $maximum1->setValue($this->object->getMaximum());
                break;
            
            case SurveyMetricQuestion::SUBTYPE_RATIO_NON_ABSOLUTE:
                $minimum2->setValue($this->object->getMinimum());
                $maximum2->setValue($this->object->getMaximum());
                break;
            
            case SurveyMetricQuestion::SUBTYPE_RATIO_ABSOLUTE:
                $minimum3->setValue($this->object->getMinimum());
                $maximum3->setValue($this->object->getMaximum());
                break;
        }
    }
    
    protected function importEditFormValues(ilPropertyFormGUI $a_form) : void
    {
        $type = (int) $a_form->getInput("type");
        $this->object->setOrientation((int) $a_form->getInput("orientation"));
        $this->object->setSubtype((int) $type);
        $min = ($a_form->getInput("minimum" . $type) != "")
            ? (float) $a_form->getInput("minimum" . $type)
            : null;
        $max = ($a_form->getInput("maximum" . $type) != "")
            ? (float) $a_form->getInput("maximum" . $type)
            : null;
        $this->object->setMinimum($min);
        $this->object->setMaximum($max);
    }
    
    public function getParsedAnswers(
        array $a_working_data = null,
        $a_only_user_anwers = false
    ) : array {
        $res = array();
        
        if (is_array($a_working_data)) {
            $res[] = array("value" => $a_working_data[0]["value"]);
        }
        
        return $res;
    }
    
    /**
    * Creates a HTML representation of the question
    * Creates a HTML representation of the question
    * @param array|null $working_data * @param int|null $survey_id* @access private
    */
    public function getPrintView(
        int $question_title = 1,
        bool $show_questiontext = true,
        ?int $survey_id = null,
        ?array $working_data = null
    ) : string {
        $user_answer = null;
        if ($working_data) {
            $user_answer = $this->getParsedAnswers($working_data);
            $user_answer = $user_answer[0]["value"];
        }
        $template = new ilTemplate("tpl.il_svy_qpl_metric_printview.html", true, true, "Modules/SurveyQuestionPool");
        $template->setVariable("MIN_MAX", $this->object->getMinMaxText());

        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->getPrintViewQuestionTitle($question_title));
        }
        $template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $template->setVariable("QUESTION_ID", $this->object->getId());

        if (!is_array($working_data) || !trim($user_answer)) {
            $len = 10;
            $solution_text = str_repeat("&#160;", 10);
        } else {
            $solution_text = $user_answer;
        }
        $template->setVariable("TEXT_SOLUTION", $solution_text);

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
    ) : string {
        $template = new ilTemplate("tpl.il_svy_out_metric.html", true, true, "Modules/SurveyQuestionPool");
        $template->setCurrentBlock("material_metric");
        $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
        $template->parseCurrentBlock();
        $template->setVariable("MIN_MAX", $this->object->getMinMaxText());

        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->object->getTitle());
        }
        $template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $template->setVariable("QUESTION_ID", $this->object->getId());
        if (is_array($working_data)) {
            $template->setVariable("VALUE_METRIC", $working_data[0]["value"] ?? "");
        }

        $template->setVariable("INPUT_SIZE", 10);

        if (strcmp($error_message, "") !== 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        $template->parseCurrentBlock();
        return $template->get();
    }
}
