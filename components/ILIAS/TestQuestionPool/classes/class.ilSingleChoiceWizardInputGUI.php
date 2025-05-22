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

use ILIAS\TestQuestionPool\ilTestLegacyFormsHelper;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

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

    protected ilTestLegacyFormsHelper $forms_helper;
    protected GlyphFactory $glyph_factory;
    protected Renderer $renderer;
    protected UploadLimitResolver $upload_limit;

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
        $this->setMaxLength(1000);
        $this->validationRegexp = '';

        global $DIC;
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC->ui()->renderer();
        $this->upload_limit = $DIC['ui.upload_limit_resolver'];
        $this->forms_helper = new ilTestLegacyFormsHelper();
    }

    public function setValue($a_value): void
    {
        $this->values = [];

        $answers = $this->forms_helper->transformArray($a_value, 'answer', $this->refinery->kindlyTo()->string());
        $points = $this->forms_helper->transformPoints($a_value);

        foreach ($answers as $index => $value) {
            $answer = new ASS_AnswerBinaryStateImage(
                $value,
                $points[$index] ?? 0.0,
                (int) $index,
                true,
                null,
                (int) $a_value['answer_id'][$index] ?? 0
            );
            if (isset($a_value['imagename'][$index])) {
                $answer->setImage($a_value['imagename'][$index]);
            }
            $this->values[] = $answer;
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
     * Checks the input of the answers and returns the answers as an array if the input is valid or a string with the
     * error message if the input is invalid. The input is invalid if an answer is empty and no imagename was provided.
     *
     * @return string[]|string
     */
    protected function checkAnswersInput(array $data): array|string
    {
        $to_string = $this->refinery->kindlyTo()->string();
        $answers = $this->forms_helper->transformArray($data, 'answer', $to_string);
        $image_names = $this->forms_helper->transformArray($data, 'imagename', $to_string);
        foreach ($answers as $index => $value) {
            if ($value === '' && !$this->forms_helper->inArray($image_names, $index)) {
                return 'msg_input_is_required';
            }

            if (mb_strlen($value) > $this->getMaxLength()) {
                return 'msg_input_char_limit_max';
            }
        }

        return $answers;
    }

    public function checkInput(): bool
    {
        $data = $this->raw($this->getPostVar());

        if (!is_array($data)) {
            $this->setAlert($this->lng->txt('msg_input_is_required'));
            return false;
        }

        // check points
        $points = $this->forms_helper->checkPointsInputEnoughPositive($data, true);
        if (!is_array($points)) {
            $this->setAlert($this->lng->txt($points));
            return false;
        }

        // check answers
        $answers = $this->checkAnswersInput($data);
        if (!is_array($answers)) {
            $this->setAlert($this->lng->txt($answers));
            return false;
        }
        $image_names = $this->forms_helper->transformArray($data, 'imagename', $this->refinery->kindlyTo()->string());

        if (is_array($_FILES) && count($_FILES) && $this->getSingleline() && (!$this->hideImages)) {
            if (is_array($_FILES[$this->getPostVar()]['error']['image'])) {
                foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error) {
                    // error handling
                    if ($error > 0) {
                        switch ($error) {
                            case UPLOAD_ERR_FORM_SIZE:
                            case UPLOAD_ERR_INI_SIZE:
                                $this->setAlert($this->lng->txt('form_msg_file_size_exceeds'));
                                return false;
                                break;

                            case UPLOAD_ERR_PARTIAL:
                                $this->setAlert($this->lng->txt('form_msg_file_partially_uploaded'));
                                return false;
                                break;

                            case UPLOAD_ERR_NO_FILE:
                                if (
                                    !$this->forms_helper->inArray($image_names, $index)
                                    && !$this->forms_helper->inArray($answers, $index)
                                    && $this->getRequired()
                                ) {
                                    $this->setAlert($this->lng->txt('form_msg_file_no_upload'));
                                    return false;
                                }
                                break;

                            case UPLOAD_ERR_NO_TMP_DIR:
                                $this->setAlert($this->lng->txt('form_msg_file_missing_tmp_dir'));
                                return false;
                                break;

                            case UPLOAD_ERR_CANT_WRITE:
                                $this->setAlert($this->lng->txt('form_msg_file_cannot_write_to_disk'));
                                return false;
                                break;

                            case UPLOAD_ERR_EXTENSION:
                                $this->setAlert($this->lng->txt('form_msg_file_upload_stopped_ext'));
                                return false;
                                break;
                        }
                    }
                }
            } elseif ($this->getRequired()) {
                $this->setAlert($this->lng->txt('form_msg_file_no_upload'));
                return false;
            }

            if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                    $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                    if ($filename !== '') {
                        $filename_arr = pathinfo($filename);
                        $suffix = $filename_arr['extension'];
                        $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                        $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                        // check suffixes
                        if (
                            $tmpname !== ''
                            && is_array($this->getSuffixes())
                            && !in_array(strtolower($suffix), $this->getSuffixes(), true)
                        ) {
                            $this->setAlert($this->lng->txt('form_msg_file_wrong_file_type'));
                            return false;
                        }
                    }
                }
            }

            if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                    $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                    if ($filename !== '') {
                        $filename_arr = pathinfo($filename);
                        $suffix = $filename_arr['extension'];
                        $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                        $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                        // virus handling
                        if ($tmpname !== '') {
                            $vir = ilVirusScanner::virusHandling($tmpname, $filename);
                            if ($vir[0] == false) {
                                $this->setAlert($this->lng->txt('form_msg_file_virus_found') . '<br />' . $vir[1]);
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return $this->checkSubItemsInput();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tpl.prop_singlechoicewizardinput.html', true, true, 'components/ILIAS/TestQuestionPool');
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
                        $tpl->setVariable('TXT_DELETE_EXISTING', $this->lng->txt('delete_existing_file'));
                        $tpl->setVariable('IMAGE_ROW_NUMBER', $i);
                        $tpl->setVariable('IMAGE_POST_VAR', $this->getPostVar());
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('addimage');
                    $tpl->setVariable('IMAGE_BROWSE', $this->lng->txt('select_file'));
                    $tpl->setVariable('IMAGE_ID', $this->getPostVar() . "[image][$i]");
                    $tpl->setVariable('MAX_SIZE_WARNING', $this->lng->txt('form_msg_file_size_exceeds'));
                    $tpl->setVariable('MAX_SIZE', $this->upload_limit->getPhpUploadLimitInBytes());
                    $tpl->setVariable('IMAGE_SUBMIT', $this->lng->txt('upload'));
                    $tpl->setVariable('IMAGE_ROW_NUMBER', $i);
                    $tpl->setVariable('IMAGE_POST_VAR', $this->getPostVar());
                    $tpl->parseCurrentBlock();
                }

                if (is_object($value)) {
                    $tpl->setCurrentBlock('prop_text_propval');
                    $tpl->setVariable(
                        'PROPERTY_VALUE',
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                    );
                    $tpl->parseCurrentBlock();
                    if ($this->getShowPoints()) {
                        $tpl->setCurrentBlock('prop_points_propval');
                        $tpl->setVariable(
                            'PROPERTY_VALUE',
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                        );
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('prop_answer_id_propval');
                    $tpl->setVariable('PROPERTY_VALUE', ilLegacyFormElementsUtil::prepareFormOutput($value->getId()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('singleline');
                $tpl->setVariable('SIZE', $this->getSize());
                $tpl->setVariable('SINGLELINE_ID', $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable('SINGLELINE_ROW_NUMBER', $i);
                $tpl->setVariable('SINGLELINE_POST_VAR', $this->getPostVar());
                $tpl->setVariable('MAXLENGTH', $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable('DISABLED_SINGLELINE', ' disabled="disabled"');
                }
                $tpl->parseCurrentBlock();
            } elseif (!$this->getSingleline()) {
                if (is_object($value)) {
                    if ($this->getShowPoints()) {
                        $tpl->setCurrentBlock('prop_points_propval');
                        $tpl->setVariable(
                            'PROPERTY_VALUE',
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                        );
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('prop_answer_id_propval');
                    $tpl->setVariable('PROPERTY_VALUE', ilLegacyFormElementsUtil::prepareFormOutput($value->getId()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('multiline');
                $tpl->setVariable(
                    'PROPERTY_VALUE',
                    ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                );
                $tpl->setVariable('MULTILINE_ID', $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable('MULTILINE_ROW_NUMBER', $i);
                $tpl->setVariable('MULTILINE_POST_VAR', $this->getPostVar());
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable('DISABLED_MULTILINE', ' disabled="disabled"');
                }
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock('move');
                $tpl->setVariable('ID', $this->getPostVar() . "[$i]");
                $tpl->setVariable('UP_BUTTON', $this->renderer->render(
                    $this->glyph_factory->up()->withAction('#')
                ));
                $tpl->setVariable('DOWN_BUTTON', $this->renderer->render(
                    $this->glyph_factory->down()->withAction('#')
                ));
                $tpl->parseCurrentBlock();
            }
            if ($this->getShowPoints()) {
                $tpl->setCurrentBlock('points');
                $tpl->setVariable('POINTS_ID', $this->getPostVar() . "[points][$i]");
                $tpl->setVariable('POINTS_POST_VAR', $this->getPostVar());
                $tpl->setVariable('POINTS_ROW_NUMBER', $i);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('row');
            $tpl->setVariable('POST_VAR', $this->getPostVar());
            $tpl->setVariable('ROW_NUMBER', $i);
            $tpl->setVariable('ID', $this->getPostVar() . "[answer][$i]");
            if ($this->getDisabled()) {
                $tpl->setVariable('DISABLED_POINTS', ' disabled="disabled"');
            }
            $tpl->setVariable('ADD_BUTTON', $this->renderer->render(
                $this->glyph_factory->add()->withAction('#')
            ));
            $tpl->setVariable('REMOVE_BUTTON', $this->renderer->render(
                $this->glyph_factory->remove()->withAction('#')
            ));
            $tpl->parseCurrentBlock();
            $i++;
        }

        if ($this->getSingleline()) {
            if (!$this->hideImages) {
                if (is_array($this->getSuffixes())) {
                    $suff_str = $delim = '';
                    foreach ($this->getSuffixes() as $suffix) {
                        $suff_str .= $delim . '.' . $suffix;
                        $delim = ', ';
                    }
                    $tpl->setCurrentBlock('allowed_image_suffixes');
                    $tpl->setVariable('TXT_ALLOWED_SUFFIXES', $this->lng->txt('file_allowed_suffixes') . ' ' . $suff_str);
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('image_heading');
                $tpl->setVariable('ANSWER_IMAGE', $this->lng->txt('answer_image'));
                $tpl->setVariable('TXT_MAX_SIZE', ilFileUtils::getFileSizeInfo());
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->getShowPoints()) {
            $tpl->setCurrentBlock('points_heading');
            $tpl->setVariable('POINTS_TEXT', $this->lng->txt('points'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('ELEMENT_ID', $this->getPostVar());
        $tpl->setVariable('TEXT_YES', $this->lng->txt('yes'));
        $tpl->setVariable('TEXT_NO', $this->lng->txt('no'));
        $tpl->setVariable('DELETE_IMAGE_HEADER', $this->lng->txt('delete_image_header'));
        $tpl->setVariable('DELETE_IMAGE_QUESTION', $this->lng->txt('delete_image_question'));
        $tpl->setVariable('ANSWER_TEXT', $this->lng->txt('answer_text'));
        $tpl->setVariable('COMMANDS_TEXT', $this->lng->txt('actions'));

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript('assets/js/answerwizardinput.js');
        $tpl->addJavascript('assets/js/singlechoicewizard.js');
    }
}
