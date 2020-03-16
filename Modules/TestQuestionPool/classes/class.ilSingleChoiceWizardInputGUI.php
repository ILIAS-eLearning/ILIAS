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
class ilSingleChoiceWizardInputGUI extends ilTextInputGUI
{
    protected $values = array();
    protected $allowMove = false;
    protected $singleline = true;
    protected $qstObject = null;
    protected $suffixes = array();
    protected $showPoints = true;
    protected $hideImages = false;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
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
    * Set Accepted Suffixes.
    *
    * @param	array	$a_suffixes	Accepted Suffixes
    */
    public function setSuffixes($a_suffixes)
    {
        $this->suffixes = $a_suffixes;
    }

    /**
    * Set hide images.
    *
    * @param	array	$a_hide	Hide images
    */
    public function setHideImages($a_hide)
    {
        $this->hideImages = $a_hide;
    }

    /**
    * Get Accepted Suffixes.
    *
    * @return	array	Accepted Suffixes
    */
    public function getSuffixes()
    {
        return $this->suffixes;
    }
    
    public function setShowPoints($a_value)
    {
        $this->showPoints = $a_value;
    }
    
    public function getShowPoints()
    {
        return $this->showPoints;
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
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";

        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
        }
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && (strlen($foundvalues['imagename'][$aidx]) == 0)) {
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
                }
            }
            if ($max == 0) {
                $this->setAlert($lng->txt("enter_enough_positive_points"));
                return false;
            }

            if (is_array($_FILES) && count($_FILES) && $this->getSingleline() && (!$this->hideImages)) {
                if (is_array($_FILES[$this->getPostVar()]['error']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error) {
                        // error handling
                        if ($error > 0) {
                            switch ($error) {
                                case UPLOAD_ERR_INI_SIZE:
                                    $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_FORM_SIZE:
                                    $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_PARTIAL:
                                    $this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_NO_FILE:
                                    if ($this->getRequired()) {
                                        if ((!strlen($foundvalues['imagename'][$index])) && (!strlen($foundvalues['answer'][$index]))) {
                                            $this->setAlert($lng->txt("form_msg_file_no_upload"));
                                            return false;
                                        }
                                    }
                                    break;

                                case UPLOAD_ERR_NO_TMP_DIR:
                                    $this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_CANT_WRITE:
                                    $this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_EXTENSION:
                                    $this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
                                    return false;
                                    break;
                            }
                        }
                    }
                } else {
                    if ($this->getRequired()) {
                        $this->setAlert($lng->txt("form_msg_file_no_upload"));
                        return false;
                    }
                }

                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                        $filename_arr = pathinfo($filename);
                        $suffix = $filename_arr["extension"];
                        $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                        $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                        // check suffixes
                        if (strlen($tmpname) && is_array($this->getSuffixes())) {
                            if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                                $this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
                                return false;
                            }
                        }
                    }
                }

                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                        $filename_arr = pathinfo($filename);
                        $suffix = $filename_arr["extension"];
                        $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                        $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                        // virus handling
                        if (strlen($tmpname)) {
                            $vir = ilUtil::virusHandling($tmpname, $filename);
                            if ($vir[0] == false) {
                                $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                                return false;
                            }
                        }
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
     * @param $a_tpl ilTemplate
     */
    public function insert($a_tpl)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $tpl = new ilTemplate("tpl.prop_singlechoicewizardinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (!$this->hideImages) {
                    if (strlen($value->getImage())) {
                        $imagename = $this->qstObject->getImagePathWeb() . $value->getImage();
                        if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                            if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getImage())) {
                                $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getImage();
                            }
                        }
                        $tpl->setCurrentBlock('image');
                        $tpl->setVariable('SRC_IMAGE', $imagename);
                        $tpl->setVariable('IMAGE_NAME', $value->getImage());
                        $tpl->setVariable('ALT_IMAGE', ilUtil::prepareFormOutput($value->getAnswertext()));
                        $tpl->setVariable("TXT_DELETE_EXISTING", $lng->txt("delete_existing_file"));
                        $tpl->setVariable("IMAGE_ROW_NUMBER", $i);
                        $tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('addimage');
                    $tpl->setVariable("IMAGE_BROWSE", $lng->txt('select_file'));
                    $tpl->setVariable("IMAGE_ID", $this->getPostVar() . "[image][$i]");
                    $tpl->setVariable("IMAGE_SUBMIT", $lng->txt("upload"));
                    $tpl->setVariable("IMAGE_ROW_NUMBER", $i);
                    $tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
                    $tpl->parseCurrentBlock();
                }

                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
                    $tpl->parseCurrentBlock();
                    if ($this->getShowPoints()) {
                        $tpl->setCurrentBlock("prop_points_propval");
                        $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
                        $tpl->parseCurrentBlock();
                    }
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
                    if ($this->getShowPoints()) {
                        $tpl->setCurrentBlock("prop_points_propval");
                        $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
                        $tpl->parseCurrentBlock();
                    }
                }
                $tpl->setCurrentBlock('multiline');
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
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
            if ($this->getShowPoints()) {
                $tpl->setCurrentBlock("points");
                $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
                $tpl->setVariable("POINTS_POST_VAR", $this->getPostVar());
                $tpl->setVariable("POINTS_ROW_NUMBER", $i);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
            }
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
            $i++;
        }

        if ($this->getSingleline()) {
            if (!$this->hideImages) {
                if (is_array($this->getSuffixes())) {
                    $suff_str = $delim = "";
                    foreach ($this->getSuffixes() as $suffix) {
                        $suff_str .= $delim . "." . $suffix;
                        $delim = ", ";
                    }
                    $tpl->setCurrentBlock('allowed_image_suffixes');
                    $tpl->setVariable("TXT_ALLOWED_SUFFIXES", $lng->txt("file_allowed_suffixes") . " " . $suff_str);
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock("image_heading");
                $tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
                $tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->getShowPoints()) {
            $tpl->setCurrentBlock("points_heading");
            $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/singlechoicewizard.js");
    }
}
