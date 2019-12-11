<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";

/**
* SingleChoice survey question GUI representation
*
* The SurveySingleChoiceQuestionGUI class encapsulates the GUI representation
* for single choice survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveySingleChoiceQuestionGUI extends SurveyQuestionGUI
{
    protected function initObject()
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveySingleChoiceQuestion.php";
        $this->object = new SurveySingleChoiceQuestion();
    }
    
    
    //
    // EDITOR
    //
    
    public function setQuestionTabs()
    {
        $this->setQuestionTabsForClass("surveysinglechoicequestiongui");
    }
    
    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form)
    {
        // orientation
        $orientation = new ilRadioGroupInputGUI($this->lng->txt("orientation"), "orientation");
        $orientation->setRequired(false);
        $orientation->addOption(new ilRadioOption($this->lng->txt('vertical'), 0));
        $orientation->addOption(new ilRadioOption($this->lng->txt('horizontal'), 1));
        $orientation->addOption(new ilRadioOption($this->lng->txt('combobox'), 2));
        $a_form->addItem($orientation);

        // Answers
        include_once "./Modules/SurveyQuestionPool/classes/class.ilCategoryWizardInputGUI.php";
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
    
    protected function importEditFormValues(ilPropertyFormGUI $a_form)
    {
        $this->log->debug("importing edit values");

        $this->object->setOrientation($a_form->getInput("orientation"));
        
        $this->object->categories->flushCategories();
        foreach ($_POST['answers']['answer'] as $key => $value) {
            if (strlen($value)) {
                $this->object->getCategories()->addCategory($value, $_POST['answers']['other'][$key], 0, null, $_POST['answers']['scale'][$key]);
            }
        }
        if (strlen($_POST['answers']['neutral'])) {
            $this->object->getCategories()->addCategory($_POST['answers']['neutral'], 0, 1, null, $_POST['answers_neutral_scale']);
        }
    }
    
    public function getParsedAnswers(array $a_working_data = null, $a_only_user_anwers = false)
    {
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
            
            if (!$a_only_user_anwers || $checked == "checked") {
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
    
    /**
    * Creates a HTML representation of the question
    *
    * @access private
    */
    public function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null, array $a_working_data = null)
    {
        $options = $this->getParsedAnswers($a_working_data);
        
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
                        $template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($option["title"]));
                        $template->setVariable("OTHER_ANSWER", $option["textanswer"]
                            ? ilUtil::prepareFormOutput($option["textanswer"])
                            : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
                        $template->parseCurrentBlock();
                    } else {
                        $template->setCurrentBlock("row");
                        $template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_" . $option["checked"] . ".png")));
                        $template->setVariable("ALT_RADIO", $this->lng->txt($option["checked"]));
                        $template->setVariable("TITLE_RADIO", $this->lng->txt($option["checked"]));
                        $template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($option["title"]));
                        $template->parseCurrentBlock();
                    }
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
                        $template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($option["title"]));
                        $template->setVariable("OTHER_ANSWER", $option["textanswer"]
                            ? ilUtil::prepareFormOutput($option["textanswer"])
                            : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
                        $template->parseCurrentBlock();
                    } else {
                        $template->setCurrentBlock("text_col");
                        $template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($option["title"]));
                        $template->parseCurrentBlock();
                    }
                }
                break;
            case 2:
                foreach ($options as $option) {
                    $template->setCurrentBlock("comborow");
                    $template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($option["title"]));
                    $template->setVariable("VALUE_SC", $option["value"]);
                    if ($option["checked"] == "checked") {
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

    /**
    * Creates the question output form for the learner
    *
    * @access public
    */
    public function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null)
    {
        $template = new ilTemplate("tpl.il_svy_out_sc.html", true, true, "Modules/SurveyQuestionPool");
        $template->setCurrentBlock("material");
        $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
        $template->parseCurrentBlock();
        switch ($this->object->orientation) {
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
                                    if ($value["value"] == $cat->scale-1) {
                                        if (strlen($value['textanswer'])) {
                                            $template->setVariable("OTHER_VALUE", ' value="' . ilUtil::prepareFormOutput($value['textanswer']) . '"');
                                        }
                                        if (!$value['uncheck']) {
                                            $template->setVariable("CHECKED_SC", " checked=\"checked\"");
                                        }
                                    }
                                }
                            }
                        }
                        $template->parseCurrentBlock();
                    } else {
                        $template->setCurrentBlock("row");
                        if ($cat->neutral) {
                            $template->setVariable('ROWCLASS', ' class="neutral"');
                        }
                        $template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($cat->title));
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        if (is_array($working_data)) {
                            foreach ($working_data as $value) {
                                if (strcmp($value["value"], "") != 0) {
                                    if ($value["value"] == $cat->scale-1) {
                                        if (!$value['uncheck']) {
                                            $template->setVariable("CHECKED_SC", " checked=\"checked\"");
                                        }
                                    }
                                }
                            }
                        }
                        $template->parseCurrentBlock();
                    }
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
                            if (strcmp($value["value"], "") != 0) {
                                if ($value["value"] == $cat->scale-1) {
                                    if (!$value['uncheck']) {
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
                                    if ($value["value"] == $cat->scale-1 && strlen($value['textanswer'])) {
                                        $template->setVariable("OTHER_VALUE", ' value="' . ilUtil::prepareFormOutput($value['textanswer']) . '"');
                                    }
                                }
                            }
                        }
                        $template->parseCurrentBlock();
                    } else {
                        $template->setCurrentBlock("text_col");
                        if ($cat->neutral) {
                            $template->setVariable('COLCLASS', ' neutral');
                        }
                        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
                        $template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($cat->title));
                        $template->setVariable("QUESTION_ID", $this->object->getId());
                        $template->parseCurrentBlock();
                    }
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
                        if (strcmp($working_data[0]["value"], "") != 0) {
                            if ($working_data[0]["value"] == $cat->scale-1) {
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
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->object->getTitle());
        }
        $template->setCurrentBlock("question_data");
        if (strcmp($error_message, "") != 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->parseCurrentBlock();
        return $template->get();
    }
}
