<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* This class represents a survey question category wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCategoryWizardInputGUI extends ilTextInputGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $values = array();
    protected $allowMove = false;
    protected $disabled_scale = true;
    protected $show_wizard = false;
    protected $show_save_phrase = false;
    protected $categorytext;
    protected $show_neutral_category = false;
    protected $neutral_category_title;
    protected $use_other_answer;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();
        
        parent::__construct($a_title, $a_postvar);
                
        $this->show_wizard = false;
        $this->show_save_phrase = false;
        $this->categorytext = $lng->txt('answer');
        $this->use_other_answer = false;
        
        $this->setMaxLength(1000); // #6218
    }
    
    public function getUseOtherAnswer()
    {
        return $this->use_other_answer;
    }
    
    public function setUseOtherAnswer($a_value)
    {
        $this->use_other_answer = ($a_value) ? true : false;
    }
    
    public function getCategoryCount()
    {
        if (!is_object($this->values)) {
            return 0;
        }
        return $this->values->getCategoryCount();
    }
    
    protected function calcNeutralCategoryScale()
    {
        if (is_object($this->values)) {
            $scale = 0;
            for ($i = 0; $i < $this->values->getCategoryCount(); $i++) {
                $cat = $this->values->getCategory($i);
                if ($cat->neutral == 0) {
                    $scale += 1;
                }
            }
            return $scale + 1;
        } else {
            return 99;
        }
    }
    
    public function setShowNeutralCategory($a_value)
    {
        $this->show_neutral_category = $a_value;
    }
    
    public function getShowNeutralCategory()
    {
        return $this->show_neutral_category;
    }
    
    public function setNeutralCategoryTitle($a_title)
    {
        $this->neutral_category_title = $a_title;
    }
    
    public function getNeutralCategoryTitle()
    {
        return $this->neutral_category_title;
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
        $this->values = new SurveyCategories();
        if (is_array($a_value)) {
            if (is_array($a_value['answer'])) {
                foreach ($a_value['answer'] as $index => $value) {
                    $this->values->addCategory($value, $a_value['other'][$index], null, null, $a_value['scale'][$index]);
                }
            }
        }
        if (array_key_exists('neutral', $a_value)) {
            $this->values->addCategory($a_value['neutral'], 0, 1, null, $_POST[$this->postvar . '_neutral_scale']);
        }
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values)
    {
        $this->values = $a_values;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues()
    {
        return $this->values;
    }

    /**
    * Set allow move
    *
    * @param	boolean	$a_allow_move Allow move
    */
    public function setAllowMove($a_allow_move)
    {
        $this->allowMove = $a_allow_move;
    }

    /**
    * Get allow move
    *
    * @return	boolean	Allow move
    */
    public function getAllowMove()
    {
        return $this->allowMove;
    }
    
    public function setShowWizard($a_value)
    {
        $this->show_wizard = $a_value;
    }
    
    public function getShowWizard()
    {
        return $this->show_wizard;
    }
    
    public function setCategoryText($a_text)
    {
        $this->categorytext = $a_text;
    }
    
    public function getCategoryText()
    {
        return $this->categorytext;
    }
    
    public function setShowSavePhrase($a_value)
    {
        $this->show_save_phrase = $a_value;
    }
    
    public function getShowSavePhrase()
    {
        return $this->show_save_phrase;
    }
    
    public function getDisabledScale()
    {
        return $this->disabled_scale;
    }
    
    public function setDisabledScale($a_value)
    {
        $this->disabled_scale = $a_value;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        }
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $idx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && ($this->getRequired() && (!$foundvalues['other'][$idx]))) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
            // check neutral column
            if (array_key_exists('neutral', $foundvalues)) {
                if ((strlen($foundvalues['neutral']) == 0) && ($this->getRequired)) {
                    $this->setAlert($lng->txt("msg_input_is_required"));
                    return false;
                }
            }
            // check scales
            if (is_array($foundvalues['scale'])) {
                foreach ($foundvalues['scale'] as $scale) {
                    //scales required
                    if ((strlen($scale)) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                    //scales positive number
                    if (!ctype_digit($scale) || $scale <= 0) {
                        $this->setAlert($lng->txt("msg_input_only_positive_numbers"));
                        return false;
                    }
                }
                //scales no duplicates.
                if (count(array_unique($foundvalues['scale'])) != count($foundvalues['scale'])) {
                    $this->setAlert($lng->txt("msg_duplicate_scale"));
                    return false;
                }
            }

            // check neutral column scale
            if (strlen($_POST[$this->postvar . '_neutral_scale'])) {
                if (is_array($foundvalues['scale'])) {
                    if (in_array($_POST[$this->postvar . '_neutral_scale'], $foundvalues['scale'])) {
                        $this->setAlert($lng->txt("msg_duplicate_scale"));
                        return false;
                    }
                }
            }
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $lng = $this->lng;
        
        $neutral_category = null;
        $tpl = new ilTemplate("tpl.prop_categorywizardinput.html", true, true, "Modules/SurveyQuestionPool");
        $i = 0;
        if (is_object($this->values)) {
            for ($i = 0; $i < $this->values->getCategoryCount(); $i++) {
                $cat = $this->values->getCategory($i);
                if (!$cat->neutral) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($cat->title));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_scale_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->values->getScale($i)));
                    $tpl->parseCurrentBlock();

                    if ($this->getUseOtherAnswer()) {
                        $tpl->setCurrentBlock("other_answer_checkbox");
                        $tpl->setVariable("POST_VAR", $this->getPostVar());
                        $tpl->setVariable("OTHER_ID", $this->getPostVar() . "[other][$i]");
                        $tpl->setVariable("ROW_NUMBER", $i);
                        if ($cat->other) {
                            $tpl->setVariable("CHECKED_OTHER", ' checked="checked"');
                        }
                        $tpl->parseCurrentBlock();
                    }

                    if ($this->getAllowMove()) {
                        $tpl->setCurrentBlock("move");
                        $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                        $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                        $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
                        $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                        $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                        $tpl->parseCurrentBlock();
                    }
                    
                    $tpl->setCurrentBlock("row");
                    $tpl->setVariable("POST_VAR", $this->getPostVar());
                    $tpl->setVariable("ROW_NUMBER", $i);
                    $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
                    $tpl->setVariable("SIZE", $this->getSize());
                    $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                    if ($this->getDisabled()) {
                        $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
                    }

                    $tpl->setVariable("SCALE_ID", $this->getPostVar() . "[scale][$i]");
                    if ($this->getDisabledScale()) {
                        $tpl->setVariable("DISABLED_SCALE", " disabled=\"disabled\"");
                    }

                    $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
                    $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                    include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
                    $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                    $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
                    $tpl->parseCurrentBlock();
                } else {
                    $neutral_category = $cat;
                }
            }
        }

        if ($this->getShowWizard()) {
            $tpl->setCurrentBlock("wizard");
            $tpl->setVariable("CMD_WIZARD", 'cmd[addPhrase]');
            $tpl->setVariable("WIZARD_BUTTON", ilUtil::getImagePath('wizard.svg'));
            $tpl->setVariable("WIZARD_TEXT", $lng->txt('add_phrase'));
            $tpl->parseCurrentBlock();
        }
        
        if ($this->getShowSavePhrase()) {
            $tpl->setCurrentBlock('savephrase');
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("VALUE_SAVE_PHRASE", $lng->txt('save_phrase'));
            $tpl->parseCurrentBlock();
        }
        
        if ($this->getShowNeutralCategory()) {
            if (is_object($neutral_category) && strlen($neutral_category->title)) {
                $tpl->setCurrentBlock("prop_text_neutral_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($neutral_category->title));
                $tpl->parseCurrentBlock();
            }
            if (strlen($this->getNeutralCategoryTitle())) {
                $tpl->setCurrentBlock("neutral_category_title");
                $tpl->setVariable("NEUTRAL_COLS", ($this->getUseOtherAnswer()) ? 4 : 3);
                $tpl->setVariable("CATEGORY_TITLE", ilUtil::prepareFormOutput($this->getNeutralCategoryTitle()));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("prop_scale_neutral_propval");
            $scale = ($neutral_category->scale > 0) ? $neutral_category->scale : $this->values->getNewScale();
            $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($scale));
            $tpl->parseCurrentBlock();

            if ($this->getUseOtherAnswer()) {
                $tpl->touchBlock('other_answer_neutral');
            }

            $tpl->setCurrentBlock('neutral_row');
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ID", $this->getPostVar() . "_neutral");
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
            }
            $tpl->setVariable("SCALE_ID", $this->getPostVar() . "_neutral_scale");
            if ($this->getDisabledScale()) {
                $tpl->setVariable("DISABLED_SCALE", " disabled=\"disabled\"");
            }
            $tpl->parseCurrentBlock();
        }

        if ($this->getUseOtherAnswer()) {
            $tpl->setCurrentBlock('other_answer_title');
            $tpl->setVariable("OTHER_TEXT", $lng->txt('use_other_answer'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $this->getCategoryText());
        $tpl->setVariable("SCALE_TEXT", $lng->txt('scale'));
        $tpl->setVariable("ACTIONS_TEXT", $lng->txt('actions'));
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        $tpl = $this->tpl;
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/SurveyQuestionPool/templates/default/categorywizard.js");
    }
}
