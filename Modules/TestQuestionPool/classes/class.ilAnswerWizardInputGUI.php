<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
* This class represents a single choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilAnswerWizardInputGUI extends ilTextInputGUI
{
    protected $values = array();
    protected $allowMove = false;
    protected $allowAddRemove = true;
    protected $singleline = true;
    protected $qstObject = null;
    protected $minvalue = false;
    protected $minvalueShouldBeGreater = false;

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setSize('25');
        $this->validationRegexp = "";
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->values = array();
        if (is_array($a_value)) {
            if (is_array($a_value['answer'])) {
                foreach ($a_value['answer'] as $index => $value) {
                    include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php";
                    $answer = new ASS_AnswerBinaryStateImage($value, $a_value['points'][$index], $index, 1, $a_value['imagename'][$index]);
                    array_push($this->values, $answer);
                }
            }
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
    * Set singleline
    *
    * @param	boolean	$a_value	Value
    */
    public function setSingleline($a_value)
    {
        $this->singleline = $a_value;
    }

    /**
    * Get singleline
    *
    * @return	boolean	Value
    */
    public function getSingleline()
    {
        return $this->singleline;
    }

    /**
    * Set question object
    *
    * @param	object	$a_value	test object
    */
    public function setQuestionObject($a_value)
    {
        $this->qstObject = &$a_value;
    }

    /**
    * Get question object
    *
    * @return	object	Value
    */
    public function getQuestionObject()
    {
        return $this->qstObject;
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

    /**
     * @return bool
     */
    public function isAddRemoveAllowed()
    {
        return $this->allowAddRemove;
    }

    /**
     * @param bool $allowAddRemove
     */
    public function setAllowAddRemove($allowAddRemove)
    {
        $this->allowAddRemove = $allowAddRemove;
    }

    /**
     * Set minvalueShouldBeGreater
     *
     * @param	boolean	$a_bool	true if the minimum value should be greater than minvalue
     */
    public function setMinvalueShouldBeGreater($a_bool)
    {
        $this->minvalueShouldBeGreater = $a_bool;
    }

    /**
     * Get minvalueShouldBeGreater
     *
     * @return	boolean	true if the minimum value should be greater than minvalue
     */
    public function minvalueShouldBeGreater()
    {
        return $this->minvalueShouldBeGreater;
    }
    /**
     * Set Minimum Value.
     *
     * @param	float	$a_minvalue	Minimum Value
     */
    public function setMinValue($a_minvalue)
    {
        $this->minvalue = $a_minvalue;
    }

    /**
     * Get Minimum Value.
     *
     * @return	float	Minimum Value
     */
    public function getMinValue()
    {
        return $this->minvalue;
    }
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->sanitizeSuperGlobalSubmitValue();
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if ((strlen($answervalue)) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
            // check points
            $max = 0;
            if (is_array($foundvalues['points'])) {
                foreach ($foundvalues['points'] as $points) {
                    if ($points > $max) {
                        $max = $points;
                    }
                    if (((strlen($points)) == 0) || (!is_numeric($points))) {
                        $this->setAlert($lng->txt("form_msg_numeric_value_required"));
                        return false;
                    }
                    if ($this->minvalueShouldBeGreater()) {
                        if (trim($points) != "" &&
                            $this->getMinValue() !== false &&
                            $points <= $this->getMinValue()) {
                            $this->setAlert($lng->txt("form_msg_value_too_low"));

                            return false;
                        }
                    } else {
                        if (trim($points) != "" &&
                            $this->getMinValue() !== false &&
                            $points < $this->getMinValue()) {
                            $this->setAlert($lng->txt("form_msg_value_too_low"));

                            return false;
                        }
                    }
                }
            }
            if ($max == 0) {
                $this->setAlert($lng->txt("enter_enough_positive_points"));
                return false;
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
        global $DIC;
        $lng = $DIC['lng'];
        
        $tpl = new ilTemplate($this->getTemplate(), true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('singleline');
                $tpl->setVariable("SIZE", $this->getSize());
                $tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
                $tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            } elseif (!$this->getSingleline()) {
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('multiline');
                $tpl->setVariable("PROPERTY_VALUE", $this->qstObject->prepareTextareaOutput($value->getAnswertext()));
                $tpl->setVariable("MULTILINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("MULTILINE_ROW_NUMBER", $i);
                $tpl->setVariable("MULTILINE_POST_VAR", $this->getPostVar());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_MULTILINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
            if ($this->isAddRemoveAllowed()) {
                $tpl->setVariable("ADD_REMOVE_ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            }
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
            }
            $tpl->parseCurrentBlock();
            $i++;
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $this->getTextInputLabel($lng));
        $tpl->setVariable("POINTS_TEXT", $this->getPointsInputLabel($lng));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/answerwizard.js");
    }

    /**
     * @param $lng
     * @return mixed
     */
    protected function getTextInputLabel($lng)
    {
        return $lng->txt('answer_text');
    }

    /**
     * @param $lng
     * @return mixed
     */
    protected function getPointsInputLabel($lng)
    {
        return $lng->txt('points');
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return "tpl.prop_answerwizardinput.html";
    }
    
    protected function sanitizeSuperGlobalSubmitValue()
    {
        if (isset($_POST[$this->getPostVar()]) && is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        }
    }
}
