<?php

use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilFileVersionFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileVersionFormGUI extends ilPropertyFormGUI
{
    const MODE_ADD = 1;
    const MODE_REPLACE = 2;
    const F_TITLE = 'title';
    const F_DESCRIPTION = "description";
    const F_FILE = "file";
    const F_SAVE_MODE = 'save_mode';
    /**
     * @var
     */
    protected bool $dnd = true;
    private int $save_mode = self::MODE_ADD;

    private FileUpload $upload;
    private \ilObjFile $file;


    /**
     * ilFileVersionFormGUI constructor.
     *
     * @param ilFileVersionsGUI $file_version_gui
     */
    public function __construct(ilFileVersionsGUI $file_version_gui, $mode = self::MODE_ADD)
    {
        global $DIC;
        $this->file = $file_version_gui->getFile();
        $this->upload = $DIC->upload();
        $this->lng = $DIC->language();
        $this->save_mode = $mode;
        parent::__construct();
        $this->setFormAction($DIC->ctrl()->getFormAction($file_version_gui));
        $this->initForm();
        $this->setTarget('_top');
    }


    private function initForm(): void
    {
        // Buttons and Title
        $this->lng->loadLanguageModule('file');
        switch ($this->save_mode) {
            case self::MODE_REPLACE:
                ilUtil::sendInfo($this->lng->txt('replace_file_info'));
                $this->setTitle($this->lng->txt('replace_file'));
                $this->addCommandButton(ilFileVersionsGUI::CMD_CREATE_REPLACING_VERSION, $this->lng->txt('replace_file'));
                break;
            case self::MODE_ADD:
                ilUtil::sendInfo($this->lng->txt('file_new_version_info'));
                $this->setTitle($this->lng->txt('file_new_version'));
                $this->addCommandButton(ilFileVersionsGUI::CMD_CREATE_NEW_VERSION, $this->lng->txt('file_new_version'));
                break;
        }
        $this->addCommandButton(ilFileVersionsGUI::CMD_DEFAULT, $this->lng->txt('cancel'));

        // Title
        $title = new ilTextInputGUI($this->lng->txt(self::F_TITLE), self::F_TITLE);
        $title->setInfo($this->lng->txt("if_no_title_then_filename"));
        $title->setSize(min(40, ilObject::TITLE_LENGTH));

        $title->setMaxLength(ilObject::TITLE_LENGTH);

        $this->addItem($title);

        // File Description
        $description = new ilTextAreaInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($description);

        // File
        if ($this->dnd) {
            // File (D&D)
            $file = new ilFileStandardDropzoneInputGUI(
                ilFileVersionsGUI::CMD_DEFAULT,
                $this->lng->txt(self::F_FILE),
                self::F_FILE
            );
            $file->setRequired(true);
            $file->setUploadUrl($this->getFormAction());
            $file->setMaxFiles(1);
            $this->addItem($file);
        } else {
            // File (classical)
            $in_file = new ilFileInputGUI($this->lng->txt(self::F_FILE), self::F_FILE);
            $in_file->setRequired(true);
            $this->addItem($in_file);
        }
    }


    /**
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function saveObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }
        if (!$this->upload->hasUploads()) {
            return false;
        }

        // bugfix mantis 26271
        global $DIC;
        $DIC->upload()->register(new ilCountPDFPagesPreProcessors());

        $file = $this->getInput(self::F_FILE);
        $file_temp_name = $file['tmp_name'];

        $this->upload->process();

        /**
         * @var $result UploadResult
         */
        $result = $this->upload->getResults()[$file_temp_name];
        if ($result->getStatus() === ProcessingStatus::REJECTED) {
            return false;
        }
        $input_title = (string) ilUtil::stripSlashes($this->getInput(self::F_TITLE));
        if (strlen(trim($input_title)) === 0) {
            $input_title = ilUtil::stripSlashes($result->getName());
        }

        switch ($this->save_mode) {
            case self::MODE_ADD:
                $this->file->appendUpload($result, $input_title);
                break;
            case self::MODE_REPLACE:
                $this->file->replaceWithUpload($result, $input_title);
                break;
        }

        $this->file->setDescription($this->getInput((string) self::F_DESCRIPTION));
        $this->file->update();

        return true;
    }


    public function fillForm(): void
    {
        $values = [];
        $values[self::F_TITLE] = $this->file->getTitle();
        $values[self::F_DESCRIPTION] = $this->file->getDescription();

        $this->setValuesByArray($values);
    }
}
