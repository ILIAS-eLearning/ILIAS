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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilKprimChoiceWizardInputGUI extends ilSingleChoiceWizardInputGUI
{
    public const CUSTOM_UPLOAD_ERR = 99;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var assKprimChoice
     */
    protected $qstObject;

    private $files;

    private $ignoreMissingUploadsEnabled;

    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        global $DIC;
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $this->lng = $lng;
        $this->tpl = $tpl;

        $this->files = array();

        $this->ignoreMissingUploadsEnabled = false;
    }

    public function setFiles($files): void
    {
        $this->files = $files;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setIgnoreMissingUploadsEnabled($ignoreMissingUploadsEnabled): void
    {
        $this->ignoreMissingUploadsEnabled = $ignoreMissingUploadsEnabled;
    }

    public function isIgnoreMissingUploadsEnabled(): bool
    {
        return $this->ignoreMissingUploadsEnabled;
    }

    public function setValue($a_value): void
    {
        $this->values = array();

        if (is_array($a_value) && is_array($a_value['answer'])) {
            foreach ($a_value['answer'] as $index => $value) {
                $answer = new ilAssKprimChoiceAnswer();

                $answer->setPosition($index);
                $answer->setAnswertext($value);
                if (isset($a_value['imagename'])) {
                    $answer->setImageFile($a_value['imagename'][$index] ?? '');
                }

                if (isset($a_value['correctness']) && isset($a_value['correctness'][$index]) && strlen($a_value['correctness'][$index])) {
                    $answer->setCorrectness((bool) $a_value['correctness'][$index]);
                }

                $answer->setThumbPrefix($this->qstObject->getThumbPrefix());
                $answer->setImageFsDir($this->qstObject->getImagePath());
                $answer->setImageWebDir($this->qstObject->getImagePathWeb());

                $this->values[] = $answer;
            }
        }

        #vd($this->values);
    }

    public function checkInput(): bool
    {
        global $DIC;
        $lng = $DIC['lng'];

        if (is_array($_POST[$this->getPostVar()])) {
            $foundvalues = ilArrayUtil::stripSlashesRecursive(
                $_POST[$this->getPostVar()],
                false,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );
        } else {
            $foundvalues = $_POST[$this->getPostVar()];
        }

        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    $hasImage = isset($foundvalues['imagename']) ? true : false;
                    if (((strlen($answervalue)) == 0) && !$hasImage) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }

            // check correctness
            if (!isset($foundvalues['correctness']) || count($foundvalues['correctness']) < count($foundvalues['answer'])) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }

            if (!$this->checkUploads($foundvalues)) {
                return false;
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
    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate("tpl.prop_kprimchoicewizardinput.html", true, true, "Modules/TestQuestionPool");

        foreach ($this->values as $value) {
            /**
             * @var ilAssKprimChoiceAnswer $value
             */

            if ($this->getSingleline()) {
                if (!$this->hideImages) {
                    if (strlen($value->getImageFile())) {
                        $imagename = $value->getImageWebPath();

                        if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                            if (@file_exists($value->getThumbFsPath())) {
                                $imagename = $value->getThumbWebPath();
                            }
                        }

                        $tpl->setCurrentBlock('image');
                        $tpl->setVariable('SRC_IMAGE', $imagename);
                        $tpl->setVariable('IMAGE_NAME', $value->getImageFile());
                        $tpl->setVariable(
                            'ALT_IMAGE',
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                        );
                        $tpl->setVariable("TXT_DELETE_EXISTING", $this->lng->txt("delete_existing_file"));
                        $tpl->setVariable("IMAGE_ROW_NUMBER", $value->getPosition());
                        $tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('addimage');
                    $tpl->setVariable("IMAGE_BROWSE", $this->lng->txt('select_file'));
                    $tpl->setVariable("IMAGE_ID", $this->getPostVar() . "[image][{$value->getPosition()}]");
                    $tpl->setVariable("IMAGE_SUBMIT", $this->lng->txt("upload"));
                    $tpl->setVariable("IMAGE_ROW_NUMBER", $value->getPosition());
                    $tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable(
                    "PROPERTY_VALUE",
                    ilLegacyFormElementsUtil::prepareFormOutput(htmlspecialchars_decode((string) $value->getAnswertext()))
                );
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock('singleline');
                $tpl->setVariable("SIZE", $this->getSize());
                $tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][{$value->getPosition()}]");
                $tpl->setVariable("SINGLELINE_ROW_NUMBER", $value->getPosition());
                $tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            } elseif (!$this->getSingleline()) {
                $tpl->setCurrentBlock('multiline');
                $tpl->setVariable(
                    "PROPERTY_VALUE",
                    ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                );
                $tpl->setVariable("MULTILINE_ID", $this->getPostVar() . "[answer][{$value->getPosition()}]");
                $tpl->setVariable("MULTILINE_ROW_NUMBER", $value->getPosition());
                $tpl->setVariable("MULTILINE_POST_VAR", $this->getPostVar());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_MULTILINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][{$value->getPosition()}]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][{$value->getPosition()}]");
                $tpl->setVariable("UP_ID", "up_{$this->getPostVar()}[{$value->getPosition()}]");
                $tpl->setVariable("DOWN_ID", "down_{$this->getPostVar()}[{$value->getPosition()}]");
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("row");

            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $value->getPosition());
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][{$value->getPosition()}]");

            $tpl->setVariable(
                "CORRECTNESS_TRUE_ID",
                $this->getPostVar() . "[correctness][{$value->getPosition()}][true]"
            );
            $tpl->setVariable(
                "CORRECTNESS_FALSE_ID",
                $this->getPostVar() . "[correctness][{$value->getPosition()}][false]"
            );
            $tpl->setVariable("CORRECTNESS_TRUE_VALUE", 1);
            $tpl->setVariable("CORRECTNESS_FALSE_VALUE", 0);

            if ($value->getCorrectness() !== null) {
                if ($value->getCorrectness()) {
                    $tpl->setVariable('CORRECTNESS_TRUE_SELECTED', ' checked="checked"');
                } else {
                    $tpl->setVariable('CORRECTNESS_FALSE_SELECTED', ' checked="checked"');
                }
            }

            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_CORRECTNESS", " disabled=\"disabled\"");
            }

            $tpl->parseCurrentBlock();
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
                    $tpl->setVariable("TXT_ALLOWED_SUFFIXES", $this->lng->txt("file_allowed_suffixes") . " " . $suff_str);
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock("image_heading");
                $tpl->setVariable("ANSWER_IMAGE", $this->lng->txt('answer_image'));
                $tpl->setVariable("TXT_MAX_SIZE", ilFileUtils::getFileSizeInfo());
                $tpl->parseCurrentBlock();
            }
        }

        foreach ($this->qstObject->getValidOptionLabels() as $optionLabel) {
            if ($this->qstObject->isCustomOptionLabel($optionLabel)) {
                continue;
            }

            $tpl->setCurrentBlock('option_label_translations');
            $tpl->setVariable('OPTION_LABEL', $optionLabel);
            $tpl->setVariable('TRANSLATION_TRUE', $this->qstObject->getTrueOptionLabelTranslation($this->lng, $optionLabel));
            $tpl->setVariable('TRANSLATION_FALSE', $this->qstObject->getFalseOptionLabelTranslation($this->lng, $optionLabel));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("DELETE_IMAGE_HEADER", $this->lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $this->lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $this->lng->txt('answer_text'));

        $tpl->setVariable("OPTIONS_TEXT", $this->lng->txt('options'));

        // winzards input column label values will be updated on document ready js
        //$tpl->setVariable("TRUE_TEXT", $this->qstObject->getTrueOptionLabelTranslation($this->lng, $this->qstObject->getOptionLabel()));
        //$tpl->setVariable("FALSE_TEXT", $this->qstObject->getFalseOptionLabelTranslation($this->lng, $this->qstObject->getOptionLabel()));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        $this->tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/kprimchoicewizard.js");
        $this->tpl->addJavascript('Modules/TestQuestionPool/js/ilAssKprimChoice.js');
    }

    public function checkUploads($foundvalues): bool
    {
        if (is_array($_FILES) && count($_FILES) && $this->getSingleline()) {
            if (!$this->hideImages) {
                if (is_array($_FILES[$this->getPostVar()]['error']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error) {
                        // error handling
                        if ($error > 0) {
                            switch ($error) {
                                case UPLOAD_ERR_FORM_SIZE:
                                case UPLOAD_ERR_INI_SIZE:
                                    $this->setAlert($this->lng->txt("form_msg_file_size_exceeds"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_PARTIAL:
                                    $this->setAlert($this->lng->txt("form_msg_file_partially_uploaded"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_NO_FILE:
                                    if ($this->getRequired() && !$this->isIgnoreMissingUploadsEnabled()) {
                                        $has_image = isset($foundvalues['imagename'][$index]) ? true : false;
                                        if (!$has_image && (!strlen($foundvalues['answer'][$index]))) {
                                            $this->setAlert($this->lng->txt("form_msg_file_no_upload"));
                                            return false;
                                        }
                                    }
                                    break;

                                case UPLOAD_ERR_NO_TMP_DIR:
                                    $this->setAlert($this->lng->txt("form_msg_file_missing_tmp_dir"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_CANT_WRITE:
                                    $this->setAlert($this->lng->txt("form_msg_file_cannot_write_to_disk"));
                                    return false;
                                    break;

                                case UPLOAD_ERR_EXTENSION:
                                    $this->setAlert($this->lng->txt("form_msg_file_upload_stopped_ext"));
                                    return false;
                                    break;
                            }
                        }
                    }
                } else {
                    if ($this->getRequired()) {
                        $this->setAlert($this->lng->txt("form_msg_file_no_upload"));
                        return false;
                    }
                }

                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                        $filename_arr = pathinfo($filename);
                        if (isset($filename_arr["extension"])) {
                            $suffix = $filename_arr["extension"];
                            $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                            $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                            // check suffixes
                            if (strlen($tmpname) && is_array($this->getSuffixes())) {
                                if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                                    $this->setAlert($this->lng->txt("form_msg_file_wrong_file_type"));
                                    return false;
                                }
                            }
                        }
                    }
                }

                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        if ($_FILES[$this->getPostVar()]['error']['image'][$index] > 0) {
                            continue;
                        }

                        $mimetype = ilObjMediaObject::getMimeType($tmpname);

                        if (!preg_match("/^image/", $mimetype)) {
                            $_FILES[$this->getPostVar()]['error']['image'][$index] = self::CUSTOM_UPLOAD_ERR;
                            $this->setAlert($this->lng->txt("form_msg_file_wrong_mime_type"));
                            return false;
                        }
                    }
                }


                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                        $filename_arr = pathinfo($filename);
                        if (isset($filename_arr["extension"])) {
                            $suffix = $filename_arr["extension"];
                            $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                            $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                            // virus handling
                            if (strlen($tmpname)) {
                                $vir = ilVirusScanner::virusHandling($tmpname, $filename);
                                if ($vir[0] == false) {
                                    $_FILES[$this->getPostVar()]['error']['image'][$index] = self::CUSTOM_UPLOAD_ERR;
                                    $this->setAlert($this->lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function collectValidFiles(): void
    {
        foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $err) {
            if ($err > 0) {
                continue;
            }

            $this->files[$index] = array(
                'position' => $index,
                'tmp_name' => $_FILES[$this->getPostVar()]['tmp_name']['image'][$index],
                'name' => $_FILES[$this->getPostVar()]['name']['image'][$index],
                'type' => $_FILES[$this->getPostVar()]['type']['image'][$index],
                'size' => $_FILES[$this->getPostVar()]['size']['image'][$index]
            );
        }
    }
}
