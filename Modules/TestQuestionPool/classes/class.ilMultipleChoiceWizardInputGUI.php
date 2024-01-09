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
* This class represents a multiple choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMultipleChoiceWizardInputGUI extends ilSingleChoiceWizardInputGUI
{
    private string $pending;

    public function setValue($a_value): void
    {
        $this->values = [];
        if (is_array($a_value) && isset($a_value['answer']) && is_array($a_value['answer'])) {
            foreach ($a_value['answer'] as $index => $value) {
                $answer = new ASS_AnswerMultipleResponseImage($value, (float) ($a_value['points'][$index] ?? 0), $index);
                $answer->setPointsChecked((float) ($a_value['points'][$index] ?? 0));
                $answer->setPointsUnchecked((float) ($a_value['points_unchecked'][$index] ?? 0));
                if (isset($a_value['imagename'][$index])) {
                    $answer->setImage($a_value['imagename'][$index]);
                }
                $this->values[] = $answer;
            }
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        global $DIC;
        $lng = $DIC['lng'];

        $post_var = $this->getPostVar();
        if (!$this->post_wrapper->has($post_var)) {
            return false;
        }

        $values = $this->post_wrapper->retrieve($post_var, $this->refinery->custom()->transformation(fn($v) => $v));

        $values = ilArrayUtil::stripSlashesRecursive( //TODO: move into transform
            $values,
            false,
            ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
        );

        $foundvalues = $values;

        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && (!isset($foundvalues['imagename']) || !isset($foundvalues['imagename'][$aidx]) || strlen($foundvalues['imagename'][$aidx]) == 0)) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
            // check points
            $max = 0;
            if (is_array($foundvalues['points'])) {
                foreach ($foundvalues['points'] as $points) {
                    $points = str_replace(',', '.', $points);
                    if ($points > $max) {
                        $max = $points;
                    }
                    if (((strlen($points)) == 0) || (!is_numeric($points))) {
                        $this->setAlert($lng->txt("form_msg_numeric_value_required"));
                        return false;
                    }
                }
                foreach ($foundvalues['points_unchecked'] as $points) {
                    $points = str_replace(',', '.', $points);
                    if (((strlen($points)) == 0) || (!is_numeric($points))) {
                        $this->setAlert($lng->txt("form_msg_numeric_value_required"));
                        return false;
                    }
                }
            }
            if ($max == 0) {
                $this->setAlert($lng->txt("enter_enough_positive_points_checked"));
                return false;
            }

            if (is_array($_FILES) && count($_FILES) && $this->getSingleline()) {
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
                                            if (isset($a_value['imagename']) && (!strlen($foundvalues['imagename'][$index])) && (!strlen($foundvalues['answer'][$index]))) {
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
                            if (isset($filename_arr["extension"])) {
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
                                        $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                                        return false;
                                    }
                                }
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
     * Insert property html
     * @param $a_tpl ilTemplate
     */
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.prop_multiplechoicewizardinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (!$this->hideImages) {
                    if ($value->hasImage()) {
                        $imagename = $this->qstObject->getImagePathWeb() . $value->getImage();
                        if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                            if (file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getImage())) {
                                $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getImage();
                            }
                        }
                        $tpl->setCurrentBlock('image');
                        $tpl->setVariable('SRC_IMAGE', $imagename);
                        $tpl->setVariable('IMAGE_NAME', $value->getImage());
                        $tpl->setVariable(
                            'ALT_IMAGE',
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                        );
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
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                    );
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPointsChecked())
                    );
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_unchecked_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPointsUnchecked())
                    );
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_answer_id_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->getId()));
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
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                    );
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_unchecked_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPointsUnchecked())
                    );
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_answer_id_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->getId()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('multiline');
                $tpl->setVariable(
                    "PROPERTY_VALUE",
                    ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                );
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
                $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", $this->renderer->render(
                    $this->glyph_factory->up()->withAction('#')
                ));
                $tpl->setVariable("DOWN_BUTTON", $this->renderer->render(
                    $this->glyph_factory->down()->withAction('#')
                ));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
            $tpl->setVariable("POINTS_UNCHECKED_ID", $this->getPostVar() . "[points_unchecked][$i]");
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
            }
            $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                $this->glyph_factory->add()->withAction('#')
            ));
            $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                $this->glyph_factory->remove()->withAction('#')
            ));
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
                $tpl->setVariable("TXT_MAX_SIZE", ilFileUtils::getFileSizeInfo());
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));
        $tpl->setVariable("POINTS_CHECKED_TEXT", $lng->txt('checkbox_checked'));
        $tpl->setVariable("POINTS_UNCHECKED_TEXT", $lng->txt('checkbox_unchecked'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/answerwizardinput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/multiplechoicewizard.js");
    }

    public function setPending(string $a_val): void
    {
        $this->pending = $a_val;
    }

    public function getPending(): string
    {
        return $this->pending;
    }
}
