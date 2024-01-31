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

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
abstract class ilMultipleImagesInputGUI extends ilIdentifiedMultiValuesInputGUI
{
    public const RENDERING_TEMPLATE = 'tpl.prop_multi_image_inp.html';

    public const ITERATOR_SUBFIELD_NAME = 'iteratorfield';
    public const STORED_IMAGE_SUBFIELD_NAME = 'storedimage';
    public const IMAGE_UPLOAD_SUBFIELD_NAME = 'imageupload';

    public const FILE_DATA_INDEX_DODGING_FILE = 'dodging_file';

    /**
     * @var bool
     */
    protected $editElementOccuranceEnabled = false;

    /**
     * @var bool
     */
    protected $editElementOrderEnabled = false;

    protected stdClass $dodging_files;

    /**
     * @var array
     */
    protected $suffixes = array();

    protected $imageRemovalCommand = 'removeImage';

    protected $imageUploadCommand = 'uploadImage';

    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected GlyphFactory $glyph_factory;
    protected Renderer $renderer;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        global $DIC;
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC->ui()->renderer();

        $this->setSuffixes(["jpg", "jpeg", "png", "gif"]);
        $this->setSize(25);
        $this->validationRegexp = "";

        $manipulator = new ilMultipleImagesAdditionalIndexLevelRemover();
        $manipulator->setPostVar($this->getPostVar());
        $this->addFormValuesManipulator($manipulator);

        $this->dodging_files = new stdClass();

        $manipulator = new ilMultipleImagesFileSubmissionDataCompletion($this->dodging_files);
        $this->addFormValuesManipulator($manipulator);

        $manipulator = new ilIdentifiedMultiFilesJsPositionIndexRemover();
        $manipulator->setPostVar($this->getPostVar());
        $this->addFormValuesManipulator($manipulator);

        $manipulator = new ilMultiFilesSubmitRecursiveSlashesStripper();
        $manipulator->setPostVar($this->getPostVar());
        $this->addFormValuesManipulator($manipulator);
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
     * @return string
     */
    public function getImageRemovalCommand(): string
    {
        return $this->imageRemovalCommand;
    }

    /**
     * @param string $imageRemovalCommand
     */
    public function setImageRemovalCommand($imageRemovalCommand): void
    {
        $this->imageRemovalCommand = $imageRemovalCommand;
    }

    /**
     * @return string
     */
    public function getImageUploadCommand(): string
    {
        return $this->imageUploadCommand;
    }

    /**
     * @param string $imageUploadCommand
     */
    public function setImageUploadCommand($imageUploadCommand): void
    {
        $this->imageUploadCommand = $imageUploadCommand;
    }

    /**
     * @return	boolean $editElementOccuranceEnabled
     */
    public function isEditElementOccuranceEnabled(): bool
    {
        return $this->editElementOccuranceEnabled;
    }

    /**
     * @param	boolean	$editElementOccuranceEnabled
     */
    public function setEditElementOccuranceEnabled($editElementOccuranceEnabled): void
    {
        $this->editElementOccuranceEnabled = $editElementOccuranceEnabled;
    }

    /**
     * @return boolean
     */
    public function isEditElementOrderEnabled(): bool
    {
        return $this->editElementOrderEnabled;
    }

    /**
     * @param boolean $editElementOrderEnabled
     */
    public function setEditElementOrderEnabled($editElementOrderEnabled): void
    {
        $this->editElementOrderEnabled = $editElementOrderEnabled;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    abstract protected function isValidFilenameInput($filenameInput): bool;

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return	boolean	$validationSuccess
     */
    public function onCheckInput(): bool
    {
        $F = $_FILES[$this->getPostVar()];

        $submittedElements = $this->getInput();

        if ($F && ((array) $this->dodging_files) !== []) {
            $F = array_merge([self::FILE_DATA_INDEX_DODGING_FILE => (array) $this->dodging_files], $F);
        }

        if ($this->getRequired() && !is_array($F['error'])) {
            $this->setAlert($this->lng->txt("form_msg_file_no_upload"));
            return false;
        } else {
            foreach ($F['error'] as $index => $error) {
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
                            if (!$this->getRequired()) {
                                break;
                            } elseif (isset($F[self::FILE_DATA_INDEX_DODGING_FILE][$index]) && $F[self::FILE_DATA_INDEX_DODGING_FILE][$index] !== '') {
                                break;
                            }
                            $this->setAlert($this->lng->txt("form_msg_file_no_upload"));
                            return false;
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
        }

        if (is_array($F['tmp_name'])) {
            foreach ($F['tmp_name'] as $index => $tmpname) {
                $filename = $F['name'][$index];
                if (is_array($filename)) {
                    $filename = array_shift($filename);
                    $tmpname = array_shift($tmpname);
                }
                $filename_arr = pathinfo($filename);
                $suffix = $filename_arr["extension"] ?? '';
                $mimetype = $F['type'][$index];
                $size_bytes = $F['size'][$index];
                // check suffixes
                if (strlen($tmpname) && is_array($this->getSuffixes())) {
                    if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                        $this->setAlert($this->lng->txt("form_msg_file_wrong_file_type"));
                        return false;
                    }
                }
            }
        }

        foreach ($F['tmp_name'] as $index => $tmpname) {
            $filename = $F['name'][$index];
            if (is_array($filename)) {
                $filename = array_shift($filename);
                $tmpname = array_shift($tmpname);
            }
            $filename_arr = pathinfo($filename);
            $suffix = $filename_arr["extension"] ?? '';
            $mimetype = $F['type'][$index];
            $size_bytes = $F['size'][$index];
            // virus handling
            if (strlen($tmpname)) {
                $vir = ilVirusScanner::virusHandling($tmpname, $filename);
                if ($vir[0] == false) {
                    $this->setAlert($this->lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                    return false;
                }
            }
        }

        return $this->checkSubItemsInput();
    }

    /**
     * @param string $mode
     * @return string
     */
    public function render(string $a_mode = ""): string
    {
        $lng = $this->lng;

        $tpl = $this->getTemplate();
        $i = 0;
        foreach ($this->getIdentifiedMultiValues() as $identifier => $value) {
            if ($this->valueHasContentImageSource($value)) {
                $tpl->setCurrentBlock('image');

                $tpl->setVariable('STORED_IMAGE_SRC', $this->fetchContentImageSourceFromValue($value));
                $tpl->setVariable(
                    'STORED_IMAGE_ALT',
                    ilLegacyFormElementsUtil::prepareFormOutput($this->fetchContentImageTitleFromValue($value))
                );
                $tpl->setVariable('STORED_IMAGE_FILENAME', $this->fetchContentImageTitleFromValue($value));
                $tpl->setVariable("STORED_IMAGE_POST_VAR", $this->getMultiValuePostVarSubFieldPosIndexed($identifier, self::STORED_IMAGE_SUBFIELD_NAME, $i));

                $tpl->setVariable("TXT_DELETE_EXISTING", $lng->txt("delete_existing_file"));
                $tpl->setVariable("IMAGE_CMD_REMOVE", $this->buildMultiValueSubmitVar($identifier, $i, $this->getImageRemovalCommand()));

                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('addimage');

            $tpl->setVariable("IMAGE_BROWSE", $lng->txt('select_file'));
            $tpl->setVariable("IMAGE_ID", $this->getMultiValuePosIndexedSubFieldId($identifier, self::IMAGE_UPLOAD_SUBFIELD_NAME, $i));
            $tpl->setVariable("TXT_IMAGE_SUBMIT", $lng->txt("upload"));
            $tpl->setVariable("IMAGE_CMD_UPLOAD", $this->buildMultiValueSubmitVar($identifier, $i, $this->getImageUploadCommand()));
            $tpl->setVariable("UPLOAD_IMAGE_POST_VAR", $this->getMultiValuePostVarSubFieldPosIndexed($identifier, self::IMAGE_UPLOAD_SUBFIELD_NAME, $i));
            $tpl->setVariable("COUNT_POST_VAR", $this->getMultiValuePostVarSubFieldPosIndexed($identifier, self::ITERATOR_SUBFIELD_NAME, $i));

            $tpl->parseCurrentBlock();

            if ($this->isEditElementOrderEnabled()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("UP_BUTTON", $this->renderer->render(
                    $this->glyph_factory->up()->withAction('#')
                ));
                $tpl->setVariable("DOWN_BUTTON", $this->renderer->render(
                    $this->glyph_factory->down()->withAction('#')
                ));
                $tpl->parseCurrentBlock();
            }

            if ($this->isEditElementOccuranceEnabled()) {
                $tpl->setCurrentBlock("row");
                $tpl->setVariable("ID_ADD", $this->getMultiValuePosIndexedSubFieldId($identifier, 'add', $i));
                $tpl->setVariable("ID_REMOVE", $this->getMultiValuePosIndexedSubFieldId($identifier, 'remove', $i));
                $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                    $this->glyph_factory->add()->withAction('#')
                ));
                $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                    $this->glyph_factory->remove()->withAction('#')
                ));
                $tpl->parseCurrentBlock();
            }

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

        $tpl->setVariable("TXT_MAX_SIZE", ilFileUtils::getFileSizeInfo());
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));

        if (!$this->getDisabled()) {
            $iterator_subfield_name = self::ITERATOR_SUBFIELD_NAME;
            $image_upload_subfield_name = self::IMAGE_UPLOAD_SUBFIELD_NAME;

            $init_code = <<<JS
$.extend({}, AnswerWizardInput, IdentifiedWizardInput).init(
    {
        'fieldContainerSelector': '.ilWzdContainerImage',
        'reindexingRequiredElementsSelectors': [
            'input:hidden[name*="[{$iterator_subfield_name}]"]',
            'input:file[id*="__{$image_upload_subfield_name}__"]',
            'input:submit[name*="[{$this->getImageUploadCommand()}]"]',
            'input:submit[name*="[{$this->getImageRemovalCommand()}]"]',
            'button'
        ],
        'handleRowCleanUpCallback': function(rowElem) {
            $(rowElem).find('div.imagepresentation').remove();
            $(rowElem).find('input[type=text]').val('');
        }
    }
);
JS;

            $this->tpl->addJavascript("asserts/js/answerwizardinput.js");
            $this->tpl->addJavascript("asserts/js/identifiedwizardinput.js");
            $this->tpl->addOnLoadCode($init_code);
        }

        return $tpl->get();
    }

    /**
     * @param $value
     * @return bool
     */
    protected function valueHasContentImageSource($value): bool
    {
        return is_array($value)
            && array_key_exists('src', $value)
            && strlen($value['src']);
    }

    /**
     * @param $value
     * @return string
     */
    protected function fetchContentImageSourceFromValue($value): ?string
    {
        if ($this->valueHasContentImageSource($value)) {
            return $value['src'];
        }

        return null;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function valueHasContentImageTitle($value): bool
    {
        return isset($value['title']) && strlen($value['title']);
    }

    protected function fetchContentImageTitleFromValue($value): ?string
    {
        if ($this->valueHasContentImageTitle($value)) {
            return $value['title'];
        }

        return $this->fetchContentImageSourceFromValue($value);
    }

    /**
     * @return ilTemplate
     */
    protected function getTemplate(): ilTemplate
    {
        return new ilTemplate(self::RENDERING_TEMPLATE, true, true, "components/ILIAS/TestQuestionPool");
    }
}
