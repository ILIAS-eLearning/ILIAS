<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilFileStandardDropzoneInputGUI
 *
 * A ilFileStandardDropzoneInputGUI is used in a (legacy) Form to upload Files using the Dropzone
 * of the UI-Framework introduced with ILIAS 5.3. In some cases this can be used as a
 * Drop-In-Replacement of the ilFileInputGUI, but check your usecase after. If you need an example
 * how to use it, see e.g. in UI/examples/Dropzone/File/Standard/with_usage_in_legacy_form.php
 *
 * Why make it a Drop-In-Replacement and not just replace ilFileInputGUI?
 * - There are a lot of different ways a form is handled in ILIAS, sometimes only checkInput is
 * called, sometime developers send their own error-messages and so on. The
 * ilFileStandardDropzoneInputGUI excepts some standard-behavior and would fail in some cases when
 * just replacing the ilFileInputGUI
 * - There are a lot of options in ilFileInputGUI which would be difficult to reimplement in
 * ilFileStandardDropzoneInputGUI without discussing them with all devs.
 * - Beside ilFileInputGUI there are many other File-InputGUIs with different functionality. We
 * should consolidate their use-cases first.
 *
 * Attention: This ilFileStandardDropzoneInputGUI changes the behaviour of your form when used: The
 * Form will be sent asynchronously due to limitations of dropped files (see
 * https://stackoverflow.com/questions/1017224/dynamically-set-value-of-a-file-input )
 *
 * Attention 2: Your form will be sent for every single file (if e.g. setMaxFiles > 1 is set).
 * This is due to the library used, but also because D&D uploads can only be handled asynchronously.
 * Therefore, the endpoint of the form must be able to handle this accordingly. If you have any
 * questions, please contact fs@studer-raimann.ch
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileStandardDropzoneInputGUI extends ilFileInputGUI implements ilToolbarItem
{
    public const ASYNC_FILEUPLOAD = "async_fileupload";

    /**
     * @var int if there are more than one  ilFileStandardDropzoneInputGUI in the same Form, this
     *      value will be incremented during rendering to make sure all Inputs will be handled
     *      correctly
     */
    protected static int $count = 0;
    /**
     * @var string Set it to the URL (using ilCtrl->getFormAction() ) to override the Endpoint the
     *      Form will be sent to. If not set, the ilFileStandardDropzoneInputGUI will get the
     *      Form-Action of it's nearest form
     */
    protected string $upload_url = '';
    /**
     * @var int The amount of files which can be uploaded. Standard is 1 since the old
     *      ilFileInputGUI in most cases allows one.
     */
    protected int $max_files = 1;
    /**
     * @var \ILIAS\Data\DataSize only files beneath this size will be accepted to upload. Currently
     *      this uses the defined valued of the php.ini
     */
    protected ?\ILIAS\Data\DataSize $max_file_size = null;
    /**
     * @var string The message which will be rendered within the dropzone.
     */
    protected string $dropzone_message = '';

    /**
     * @var string Bugfix mantis 0025881: used to determine the cancel-button to prevent file upload validation when cancelling
     */
    protected string $cancel_command = "";

    /**
     * ilFileStandardDropzoneInputGUI constructor.
     * @param string $cancel_command required as a bugfix for mantis 0025881
     * @param string $a_title
     * @param string $a_postvar
     */
    public function __construct(
        string $cancel_command,
        string $a_title = "",
        string $a_postvar = ""
    ) {
        $this->cancel_command = $cancel_command;
        parent::__construct($a_title, $a_postvar);
    }

    /**
     * @return string the URL where the form will be sent to.
     */
    public function getUploadUrl() : string
    {
        return $this->upload_url;
    }


    /**
     * Set the URL (using ilCtrl->getFormAction() ) to override the Endpoint the
     *      Form will be sent to. If not set, the ilFileStandardDropzoneInputGUI will get the
     *      Form-Action of it's nearest form
     *
     * @param string $upload_url
     *
     * @return $this
     */
    public function setUploadUrl(string $upload_url) : self
    {
        $this->upload_url = $upload_url;

        return $this;
    }


    /**
     * @return int Amount of allowed files in this input
     */
    public function getMaxFiles() : int
    {
        return $this->max_files;
    }


    /**
     * @param int $max_files The amount of files which can be uploaded. Standard is 1 since the old
     *                       ilFileInputGUI in most cases allows one.
     */
    public function setMaxFiles(int $max_files) : void
    {
        $this->max_files = $max_files;
    }


    /**
     * @return \ILIAS\Data\DataSize allowed size of files which can be uploaded
     */
    public function getMaxFilesize() : ?\ILIAS\Data\DataSize
    {
        return $this->max_file_size;
    }


    /**
     * @param \ILIAS\Data\DataSize $max_file_size only files beneath this size will be accepted to
     *                                            upload. Currently this uses the defined valued of
     *                                            the php.ini
     */
    public function setMaxFilesize(\ILIAS\Data\DataSize $max_file_size) : void
    {
        $this->max_file_size = $max_file_size;
    }


    /**
     * @return string The message which will be rendered within the dropzone.
     */
    public function getDropzoneMessage() : string
    {
        return $this->dropzone_message;
    }


    /**
     * @param string $dropzone_message The message which will be rendered within the dropzone.
     */
    public function setDropzoneMessage(string $dropzone_message) : void
    {
        $this->dropzone_message = $dropzone_message;
    }

    public function render($a_mode = "") : string
    {
        $this->handleUploadURL();
        $this->handleSuffixes();

        $f = $this->ui()->factory();
        $r = $this->ui()->renderer();

        $this->initDropzoneMessage();

        $dropzone = $f->dropzone()
                      ->file()
                      ->standard($this->getUploadUrl())
                      ->withParameterName($this->getPostVar())
                      ->withMaxFiles($this->getMaxFiles())
                      ->withMessage($this->getDropzoneMessage())
                      ->withAllowedFileTypes($this->getSuffixes());
        $dropzone = $this->handleMaxFileSize($dropzone);
        if ($this->isFileNameSelectionEnabled()) {
            $dropzone = $dropzone->withUserDefinedFileNamesEnabled(true);
        }

        $render = $r->render($dropzone);

        $n = ++self::$count;
        $out = "<div id='ilFileStandardDropzoneInputGUIWrapper$n'>" . $render . '</div>';
        // We need some javascript magic

        $this->ui()->mainTemplate()->addJavaScript('./Services/Form/js/ilFileStandardDropzoneInputGUI.js');
        $this->ui()->mainTemplate()->addOnLoadCode("ilFileStandardDropzoneInputGUI.init('ilFileStandardDropzoneInputGUIWrapper$n', '$this->cancel_command');");

        return $out;
    }


    /**
     * @inheritdoc
     */
    public function checkInput() : bool
    {
        $hasUploads = $this->dic()->upload()->hasUploads();
        if ($this->getRequired() && !$hasUploads) {
            return false; // No file uploaded but is was required
        }

        if ($hasUploads) {
            try {
                $a = $_FILES[$this->getPostVar()];
            } catch (Exception $e) {
                return false;
            }

            return true;
        }

        return true;
    }

    public function getInput() : array
    {
        return $_FILES[$this->getPostVar()];
    }

    protected function handleUploadURL() : void
    {
        if (!$this->getUploadUrl()) {
            $parentWrapper = $this;
            while (!$parentWrapper instanceof ilPropertyFormGUI && $parentWrapper !== null) {
                $parentWrapper = $parentWrapper->getParent();
            }

            $str_replace = str_replace("&amp;", "&", $parentWrapper->getFormAction());
            $this->setUploadUrl($str_replace . "&" . self::ASYNC_FILEUPLOAD . "=true");
        }
    }


    protected function handleSuffixes() : void
    {
        if (!is_array($this->getSuffixes())) {
            $this->setSuffixes(array());
        }
    }


    /**
     * @param ILIAS\UI\Component\Dropzone\File\Standard $dropzone
     *
     * @return ILIAS\UI\Component\Dropzone\File\Standard
     */
    protected function handleMaxFileSize(
        ILIAS\UI\Component\Dropzone\File\Standard $dropzone
    ) : ILIAS\UI\Component\Dropzone\File\Standard {
        if ($this->getMaxFilesize()) {
            $dropzone = $dropzone->withFileSizeLimit($this->getMaxFilesize());
        }

        return $dropzone;
    }


    protected function initDropzoneMessage() : void
    {
        if (!$this->getDropzoneMessage()) {
            if ($this->getMaxFiles() === 1) {
                $this->setDropzoneMessage($this->lng()->txt('drag_file_here'));
            } else {
                $this->setDropzoneMessage($this->lng()->txt('drag_files_here'));
            }
        }
    }
}
