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

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper as ArrayBasedRequestWrapper;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;

/**
* This class represents a single choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSingleChoiceWizardInputGUI extends ilTextInputGUI
{
    protected $values = [];
    protected $allowMove = false;
    protected $singleline = true;
    protected $qstObject = null;
    protected $suffixes = [];
    protected $showPoints = true;
    protected $hideImages = false;

    protected ArrayBasedRequestWrapper $post_wrapper;
    protected GlyphFactory $glyph_factory;
    protected Renderer $renderer;

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = '', $a_postvar = '')
    {
        parent::__construct($a_title, $a_postvar);
        $this->setSuffixes(['jpg', 'jpeg', 'png', 'gif']);
        $this->setSize('25');
        $this->validationRegexp = '';

        global $DIC;
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC->ui()->renderer();
    }

    public function setValue($a_value): void
    {
        $this->values = array();
        if (is_array($a_value)) {
            if (is_array($a_value['answer'])) {
                foreach ($a_value['answer'] as $index => $value) {
                    $points = (float) str_replace(",", ".", $a_value['points'][$index]);
                    $answer = new ASS_AnswerBinaryStateImage(
                        (string) $value,
                        $points,
                        (int) $index,
                        true,
                        null,
                        (int) $a_value['answer_id'][$index]
                    );
                    if (isset($a_value['imagename'][$index])) {
                        $answer->setImage($a_value['imagename'][$index]);
                    }
                    $this->values[] = $answer;
                }
            }
        }
    }


    public function setValueByArray(array $a_values): void
    {
        if (isset($a_values[$this->getPostVar()])) {
            $this->setValue($a_values[$this->getPostVar()] ?? []);
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
    * Set hide images.
    *
    * @param	array	$a_hide	Hide images
    */
    public function setHideImages($a_hide): void
    {
        $this->hideImages = $a_hide;
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

    public function setShowPoints($a_value): void
    {
        $this->showPoints = $a_value;
    }

    public function getShowPoints(): bool
    {
        return $this->showPoints;
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
    * Set singleline
    *
    * @param	boolean	$a_value	Value
    */
    public function setSingleline($a_value): void
    {
        $this->singleline = $a_value;
    }

    /**
    * Get singleline
    *
    * @return	boolean	Value
    */
    public function getSingleline(): bool
    {
        return $this->singleline;
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


    // Set pending filename value
    public function setPending(string $val): void
    {
        /**
         * 2023-07-05 sk: This is not how it should be, but there is no got way
         * around it right now. We need KS-Forms. Now!
         */
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
            $foundvalues = ilArrayUtil::stripSlashesRecursive(
                $_POST[$this->getPostVar()],
                true,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );
        }
        //$foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && (!isset($foundvalues['imagename'][$aidx]) || strlen($foundvalues['imagename'][$aidx]) == 0)) {
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
                                        if ((!isset($foundvalues['imagename'][$index])) && (!strlen($foundvalues['answer'][$index]))) {
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
                        if ($filename != '') {
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
                }

                if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                    foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                        $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                        if ($filename != '') {
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
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.prop_singlechoicewizardinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (!$this->hideImages) {
                    if ($value->getImage()) {
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
                    if ($this->getShowPoints()) {
                        $tpl->setCurrentBlock("prop_points_propval");
                        $tpl->setVariable(
                            "PROPERTY_VALUE",
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                        );
                        $tpl->parseCurrentBlock();
                    }
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
                    if ($this->getShowPoints()) {
                        $tpl->setCurrentBlock("prop_points_propval");
                        $tpl->setVariable(
                            "PROPERTY_VALUE",
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                        );
                        $tpl->parseCurrentBlock();
                    }
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
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/answerwizardinput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/singlechoicewizard.js");
    }
}
