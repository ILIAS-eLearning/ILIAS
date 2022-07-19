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

require_once './Modules/TestQuestionPool/classes/class.ilSingleChoiceWizardInputGUI.php';
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
 * This class represents a single choice wizard property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilMatchingWizardInputGUI extends ilTextInputGUI
{
    protected $text_name = '';
    protected $image_name = '';
    protected $values = array();
    protected $allowMove = false;
    protected $qstObject = null;
    protected $suffixes = array();
    protected $hideImages = false;

    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        parent::__construct($a_title, $a_postvar);
        $this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
        $this->setSize('40');
        $this->setMaxLength(800);
        $this->text_name = $lng->txt('answer_text');
        $this->image_name = $lng->txt('answer_image');
    }
    
    /**
    * Set Accepted Suffixes.
    *
    * @param	array	$a_suffixes	Accepted Suffixes
    */
    public function setSuffixes($a_suffixes) : void
    {
        $this->suffixes = $a_suffixes;
    }

    /**
    * Get Accepted Suffixes.
    *
    * @return	array	Accepted Suffixes
    */
    public function getSuffixes() : array
    {
        return $this->suffixes;
    }
    
    /**
    * Set hide images.
    *
    * @param	bool	$a_hide	Hide images
    */
    public function setHideImages($a_hide) : void
    {
        $this->hideImages = $a_hide;
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values) : void
    {
        $this->values = $a_values;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues() : array
    {
        return $this->values;
    }

    public function setTextName($a_value) : void
    {
        $this->text_name = $a_value;
    }
    
    public function setImageName($a_value) : void
    {
        $this->image_name = $a_value;
    }
    
    /**
    * Set question object
    *
    * @param	object	$a_value	test object
    */
    public function setQuestionObject($a_value) : void
    {
        $this->qstObject = &$a_value;
    }

    /**
    * Get question object
    *
    * @return	object	Value
    */
    public function getQuestionObject() : ?object
    {
        return $this->qstObject;
    }

    /**
    * Set allow move
    *
    * @param	boolean	$a_allow_move Allow move
    */
    public function setAllowMove($a_allow_move) : void
    {
        $this->allowMove = $a_allow_move;
    }

    /**
    * Get allow move
    *
    * @return	boolean	Allow move
    */
    public function getAllowMove() : bool
    {
        return $this->allowMove;
    }

    public function setValue($a_value) : void
    {
        $this->values = array();
        if (is_array($a_value)) {
            if (is_array($a_value['answer'])) {
                foreach ($a_value['answer'] as $index => $value) {
                    $answer = new assAnswerMatchingTerm(
                        $value,
                        $a_value['imagename'][$index] ?? '',
                        $a_value['identifier'][$index] ?? ''
                    );
                    array_push($this->values, $answer);
                }
            }
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput() : bool
    {
        global $DIC;
        $lng = $DIC['lng'];
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
        if (is_array($_POST[$this->getPostVar()])) {
            $foundvalues = ilArrayUtil::stripSlashesRecursive(
                $_POST[$this->getPostVar()],
                true,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );
        } else {
            $foundvalues = $_POST[$this->getPostVar()];
        }
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if ($answervalue === '' && (
                        (!isset($foundvalues['imagename'][$aidx]) || $foundvalues['imagename'][$aidx] === '') &&
                        !isset($_FILES[$this->getPostVar()]['tmp_name']['image'][$aidx])
                    )) {
                        // If there is to text answer, no already staged image, and no uploaded image ...
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }

            if (!$this->hideImages) {
                if (is_array($_FILES[$this->getPostVar()]['error']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error) {
                        // error handling
                        if ($error > 0) {
                            switch ($error) {
                                case UPLOAD_ERR_FORM_SIZE:
                                case UPLOAD_ERR_INI_SIZE:
                                    $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_PARTIAL:
                                    $this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_NO_FILE:
                                    if ($this->getRequired()) {
                                        if ((!isset($foundvalues['imagename'][$index]) || $foundvalues['imagename'][$index] === '') &&
                                            (!isset($foundvalues['answer'][$index]) && $foundvalues['answer'][$index] === '')) {
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
                }

                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                        $filename_arr = pathinfo($filename);
                        $suffix = '';
                        if (isset($filename_arr['extension'])) {
                            $suffix = $filename_arr["extension"];
                        }

                        // check suffixes
                        if ($tmpname != '' && is_array($this->getSuffixes())) {
                            $vir = ilVirusScanner::virusHandling($tmpname, $filename);
                            if ($vir[0] == false) {
                                $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                                return false;
                            }
                            
                            if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                                $this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    * @return	void	Size
    */
    public function insert(ilTemplate $a_tpl) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $tpl = new ilTemplate("tpl.prop_matchingwizardinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if (!$this->hideImages) {
                if (strlen($value->picture)) {
                    $imagename = $this->qstObject->getImagePathWeb() . $value->picture;
                    if ($this->qstObject->getThumbSize()) {
                        if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->picture)) {
                            $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->picture;
                        }
                    }
                    $tpl->setCurrentBlock('image');
                    $tpl->setVariable('SRC_IMAGE', $imagename);
                    $tpl->setVariable('IMAGE_NAME', $value->picture);
                    $tpl->setVariable('ALT_IMAGE', ilLegacyFormElementsUtil::prepareFormOutput($value->text));
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
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->text));
                $tpl->parseCurrentBlock();
            }
            // this block does not exist in the template
            //			$tpl->setCurrentBlock('singleline');
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
            $tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
            }
            $tpl->parseCurrentBlock();
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
            $tpl->setVariable("ROW_NUMBER", $i + 1);
            $tpl->setVariable("ROW_IDENTIFIER", $value->identifier);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
            $i++;
        }

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
            $tpl->setVariable("ANSWER_IMAGE", $this->image_name);
            $tpl->setVariable("TXT_MAX_SIZE", ilFileUtils::getFileSizeInfo());
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $this->text_name);
        $tpl->setVariable("NUMBER_TEXT", $lng->txt('row'));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
