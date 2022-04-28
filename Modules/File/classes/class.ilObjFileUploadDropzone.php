<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Dropzone\File\File as FileDropzone;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\DI\UIServices;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilObjFileUploadDropzone
{
    protected ilObjectDefinition $definition;
    protected ilCtrlInterface $ctrl;
    protected UploadHandler $upload_handler;
    protected ilLanguage $language;
    protected ilAccess $access;
    protected UIServices $ui;

    protected int $target_ref_id;
    protected ?string $content;

    public function __construct(int $target_ref_id, string $content = null)
    {
        global $DIC;

        $this->definition = $DIC['objDefinition'];
        $this->language = $DIC->language();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        $this->upload_handler = new ilObjFileUploadHandlerGUI();
        $this->target_ref_id = $target_ref_id;
        $this->content = $content;
    }

    public function getDropzone() : FileDropzone
    {
        $this->ctrl->setParameterByClass(ilObjFileGUI::class, 'ref_id', $this->target_ref_id);
        $this->ctrl->setParameterByClass(ilObjFileGUI::class, 'new_type', ilObjFile::OBJECT_TYPE);
        $this->ctrl->setParameterByClass(
            ilObjFileGUI::class,
            ilObjFileGUI::PARAM_UPLOAD_ORIGIN,
            ilObjFileGUI::UPLOAD_ORIGIN_DROPZONE
        );

        /** @var $dropzone FileDropzone */
        $dropzone = $this->ui->factory()->dropzone()->file()->wrapper(
            $this->upload_handler,
            $this->ctrl->getFormActionByClass(ilObjFileGUI::class, ilObjFileGUI::CMD_UPLOAD_FILES),
            $this->ui->factory()->legacy($this->content ?? ''),
            $this->ui->factory()->input()->field()->group([
                ilObjFileProcessorInterface::OPTION_FILENAME => $this->ui->factory()->input()->field()->text($this->language->txt('title')),
                ilObjFileProcessorInterface::OPTION_DESCRIPTION => $this->ui->factory()->input()->field()->textarea($this->language->txt('description')),
            ])
        )->withMaxFiles(
            ilObjFileGUI::UPLOAD_MAX_FILES
        )->withMaxFileSize(
            ilObjFileGUI::UPLOAD_MAX_FILE_SIZE
        )->withTitle(
            $this->language->txt('upload_files')
        );

        return $dropzone;
    }

    public function isUploadAllowed(string $obj_type) : bool
    {
        if ($this->definition->isContainer($obj_type)) {
            return $this->access->checkAccess('create_file', '', $this->target_ref_id, 'file');
        }

        return false;
    }

    public function getDropzoneHtml() : string
    {
        return $this->ui->renderer()->render($this->getDropzone());
    }
}
