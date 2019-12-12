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
* Text survey question GUI representation
*
* The SurveyTextQuestionGUI class encapsulates the GUI representation
* for text survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestionGUI
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyTextQuestionGUI extends SurveyQuestionGUI
{
    protected function initObject()
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyTextQuestion.php";
        $this->object = new SurveyTextQuestion();
    }
    
    
    //
    // EDITOR
    //
    
    public function setQuestionTabs()
    {
        $this->setQuestionTabsForClass("surveytextquestiongui");
    }

    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form)
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
    
    protected function importEditFormValues(ilPropertyFormGUI $a_form)
    {
        $max = $a_form->getInput("maxchars");
        $this->object->setMaxChars(strlen($max) ? $max : null);
        $this->object->setTextWidth($a_form->getInput("textwidth"));
        $this->object->setTextHeight($a_form->getInput("textheight"));
    }
    
    public function getParsedAnswers(array $a_working_data = null, $a_only_user_anwers = false)
    {
        $res = array();
        
        if (is_array($a_working_data)) {
            $res[] = array("textanswer"=>trim($a_working_data[0]["textanswer"]));
        }
        
        return $res;
    }
    
    public function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = null, array $a_working_data = null)
    {
        $user_answer = null;
        if ($a_working_data) {
            $user_answer = $this->getParsedAnswers($a_working_data);
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
        if (is_array($a_working_data) && trim($user_answer)) {
            $template->setVariable("TEXT", nl2br($user_answer));
        } else {
            $template->setVariable("TEXTBOX_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("textbox.png")));
            $template->setVariable("TEXTBOX", $this->lng->txt("textbox"));
            $template->setVariable("TEXTBOX_WIDTH", $this->object->getTextWidth()*16);
            $template->setVariable("TEXTBOX_HEIGHT", $this->object->getTextHeight()*16);
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
    public function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null)
    {
        $template = new ilTemplate("tpl.il_svy_out_text.html", true, true, "Modules/SurveyQuestionPool");
        $template->setCurrentBlock("material_text");
        $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
        $template->parseCurrentBlock();

        if ($this->object->getTextHeight() == 1) {
            $template->setCurrentBlock("textinput");
            if (is_array($working_data)) {
                if (strlen($working_data[0]["textanswer"])) {
                    $template->setVariable("VALUE_ANSWER", " value=\"" . ilUtil::prepareFormOutput($working_data[0]["textanswer"]) . "\"");
                }
            }
            $template->setVariable("QUESTION_ID", $this->object->getId());
            $template->setVariable("WIDTH", $this->object->getTextWidth());
            if ($this->object->getMaxChars()) {
                $template->setVariable("MAXLENGTH", " maxlength=\"" . $this->object->getMaxChars() . "\"");
            }
            $template->parseCurrentBlock();
        } else {
            $template->setCurrentBlock("textarea");
            if (is_array($working_data)) {
                $template->setVariable("VALUE_ANSWER", ilUtil::prepareFormOutput($working_data[0]["textanswer"]));
            }
            $template->setVariable("QUESTION_ID", $this->object->getId());
            $template->setVariable("WIDTH", $this->object->getTextWidth());
            $template->setVariable("HEIGHT", $this->object->getTextHeight());
            $template->parseCurrentBlock();
        }
        $template->setCurrentBlock("question_data_text");
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->object->getTitle());
        }
        $template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $template->setVariable("LABEL_QUESTION_ID", $this->object->getId());
        if (strcmp($error_message, "") != 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        if ($this->object->getMaxChars()) {
            $template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
        }
        $template->parseCurrentBlock();
        return $template->get();
    }
}
