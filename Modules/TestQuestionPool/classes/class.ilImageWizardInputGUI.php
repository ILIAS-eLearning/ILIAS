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
* This class represents a single choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilImageWizardInputGUI extends ilTextInputGUI
{
    protected $values = array();
    protected $allowMove = false;
    protected $qstObject = null;
    protected $suffixes = array();

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setSuffixes(assQuestion::getAllowedImageMaterialFileExtensions());
        $this->setSize('25');
        $this->validationRegexp = "";
    }

    public function setValue($a_value): void
    {
        $this->values = array();
        if (is_array($a_value)) {
            if (is_array($a_value['count'])) {
                foreach ($a_value['count'] as $index => $value) {
                    array_push($this->values, $a_value['imagename'][$index]);
                }
            }
        }
    }

    /**
    * Set Accepted Suffixes.
    *
    * @param	array	$a_suffixes	Accepted Suffixes
    */
    public function setSuffixes($a_suffixes): void
    {
        $this->suffixes = $a_suffixes;
    }

    /**
    * Get Accepted Suffixes.
    *
    * @return	array	Accepted Suffixes
    */
    public function getSuffixes(): array
    {
        return $this->suffixes;
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values): void
    {
        $this->values = $a_values;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
    * Set question object
    *
    * @param	object	$a_value	test object
    */
    public function setQuestionObject($a_value): void
    {
        $this->qstObject = &$a_value;
    }

    /**
    * Get question object
    *
    * @return	object	Value
    */
    public function getQuestionObject(): ?object
    {
        return $this->qstObject;
    }

    /**
    * Set allow move
    *
    * @param	boolean	$a_allow_move Allow move
    */
    public function setAllowMove($a_allow_move): void
    {
        $this->allowMove = $a_allow_move;
    }

    /**
    * Get allow move
    *
    * @return	boolean	Allow move
    */
    public function getAllowMove(): bool
    {
        return $this->allowMove;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        global $DIC;
        $lng = $DIC['lng'];

        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilArrayUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        }
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
                                if (!strlen($_POST[$this->getPostVar()]['imagename'][$index])) {
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
                    $vir = ilVirusScanner::virusHandling($tmpname, $filename);
                    if ($vir[0] == false) {
                        $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                        return false;
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
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.prop_imagewizardinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if (strlen($value)) {
                $imagename = $this->qstObject->getImagePathWeb() . $value;
                if ($this->qstObject->getThumbSize()) {
                    if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value)) {
                        $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value;
                    }
                }
                $tpl->setCurrentBlock('image');
                $tpl->setVariable('SRC_IMAGE', $imagename);
                $tpl->setVariable('IMAGE_NAME', $value);
                $tpl->setVariable('ALT_IMAGE', ilLegacyFormElementsUtil::prepareFormOutput($value));
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
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
            $i++;
        }

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
        /*
        $tpl->setCurrentBlock("image_heading");
        $tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
        $tpl->parseCurrentBlock();
        */

        $tpl->setVariable("TXT_MAX_SIZE", ilFileUtils::getFileSizeInfo());
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
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/imagewizard.js");
    }
}
