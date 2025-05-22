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
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup components\ILIASTestQuestionPool
 */
class ilMatchingWizardInputGUI extends ilTextInputGUI
{
    private string $pending;
    protected $text_name = '';
    protected $image_name = '';
    protected $values = [];
    protected $qstObject = null;
    protected $suffixes = [];
    protected $hideImages = false;

    protected ilTestLegacyFormsHelper $forms_helper;
    protected GlyphFactory $glyph_factory;
    protected Renderer $renderer;
    protected UploadLimitResolver $upload_limit;

    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        global $DIC;

        $this->forms_helper = new ilTestLegacyFormsHelper();
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC->ui()->renderer();
        $this->upload_limit = $DIC['ui.upload_limit_resolver'];

        $this->setSuffixes(["jpg", "jpeg", "png", "gif"]);
        $this->setSize('40');
        $this->setMaxLength(800);

        $lng = $DIC['lng'];
        $this->text_name = $lng->txt('answer_text');
        $this->image_name = $lng->txt('answer_image');
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
    * Set hide images.
    *
    * @param	bool	$a_hide	Hide images
    */
    public function setHideImages($a_hide): void
    {
        $this->hideImages = $a_hide;
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

    public function setTextName($a_value): void
    {
        $this->text_name = $a_value;
    }

    public function setImageName($a_value): void
    {
        $this->image_name = $a_value;
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

    public function setValue($a_value): void
    {
        $this->values = [];

        $answers = $this->forms_helper->transformArray($a_value, 'answer', $this->refinery->kindlyTo()->string());
        $imagename = $this->forms_helper->transformArray($a_value, 'imagename', $this->refinery->kindlyTo()->string());
        $identifier = $this->forms_helper->transformArray($a_value, 'identifier', $this->refinery->kindlyTo()->int());

        foreach ($answers as $index => $value) {
            $this->values[] = new assAnswerMatchingTerm(
                $value,
                $imagename[$index] ?? '',
                $identifier[$index] ?? 0
            );
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        $data = $this->raw($this->getPostVar());

        if (!is_array($data)) {
            $this->setAlert($this->lng->txt('msg_input_is_required'));
            return false;
        }

        // check answers
        $answers = $this->forms_helper->transformArray($data, 'answer', $this->refinery->kindlyTo()->string());
        $images = $this->forms_helper->transformArray($data, 'imagename', $this->refinery->kindlyTo()->string());
        foreach ($answers as $index => $value) {
            if (
                $value === ''
                && !$this->forms_helper->inArray($images, $index)
                && !isset($_FILES[$this->getPostVar()]['tmp_name']['image'][$index])
            ) {
                $this->setAlert($this->lng->txt('msg_input_is_required'));
                return false;
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
                                $this->setAlert($this->lng->txt('form_msg_file_size_exceeds'));
                                return false;
                                break;

                            case UPLOAD_ERR_PARTIAL:
                                $this->setAlert($this->lng->txt('form_msg_file_partially_uploaded'));
                                return false;
                                break;

                            case UPLOAD_ERR_NO_FILE:
                                if (
                                    !$this->forms_helper->inArray($images, $index)
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
            }

            if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                    $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                    $filename_arr = pathinfo($filename);
                    $suffix = $filename_arr['extension'] ?? '';

                    // check suffixes
                    if ($tmpname !== '' && is_array($this->getSuffixes())) {
                        $vir = ilVirusScanner::virusHandling($tmpname, $filename);
                        if ($vir[0] == false) {
                            $this->setAlert($this->lng->txt('form_msg_file_virus_found') . '<br />' . $vir[1]);
                            return false;
                        }

                        if (!in_array(strtolower($suffix), $this->getSuffixes(), true)) {
                            $this->setAlert($this->lng->txt('form_msg_file_wrong_file_type'));
                            return false;
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
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $global_tpl = $DIC['tpl'];
        $global_tpl->addJavascript('assets/js/matchinginput.js');
        $global_tpl->addOnLoadCode('il.test.matchingquestion.init();');

        $tpl = new ilTemplate("tpl.prop_matchingwizardinput.html", true, true, "components/ILIAS/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if (!$this->hideImages) {
                if ($value->getPicture() &&
                    file_exists($this->qstObject->getImagePath() . $value->getPicture())
                ) {
                    $imagename = $this->qstObject->getImagePathWeb() . $value->getPicture();
                    if ($this->qstObject->getThumbSize()) {
                        if (file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getPicture())) {
                            $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getPicture();
                        }
                    }

                    $tpl->setCurrentBlock('image');
                    $tpl->setVariable('SRC_IMAGE', $imagename);
                    $tpl->setVariable('IMAGE_NAME', $value->getPicture());
                    $tpl->setVariable('ALT_IMAGE', ilLegacyFormElementsUtil::prepareFormOutput($value->getText()));
                    $tpl->setVariable("TXT_DELETE_EXISTING", $lng->txt("delete_existing_file"));
                    $tpl->setVariable("IMAGE_ROW_NUMBER", $i);
                    $tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('addimage');
                $tpl->setVariable("IMAGE_BROWSE", $lng->txt('select_file'));
                $tpl->setVariable("IMAGE_ID", $this->getPostVar() . "[image][$i]");
                $tpl->setVariable('MAX_SIZE_WARNING', $this->lng->txt('form_msg_file_size_exceeds'));
                $tpl->setVariable('MAX_SIZE', $this->upload_limit->getPhpUploadLimitInBytes());
                $tpl->setVariable("IMAGE_SUBMIT", $lng->txt("upload"));
                $tpl->setVariable("IMAGE_ROW_NUMBER", $i);
                $tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
                $tpl->parseCurrentBlock();
            }

            if (is_object($value)) {
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->getText()));
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
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i + 1);
            $tpl->setVariable("ROW_IDENTIFIER", $value->getIdentifier());
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                $this->glyph_factory->add()->withAction('#')
            ));
            $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                $this->glyph_factory->remove()->withAction('#')
            ));
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

    public function setPending(string $a_val): void
    {
        $this->pending = $a_val;
    }

    public function getPending(): string
    {
        return $this->pending;
    }
}
