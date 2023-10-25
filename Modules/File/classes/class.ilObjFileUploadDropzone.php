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

declare(strict_types=1);

use ILIAS\UI\Component\Dropzone\File\File as FileDropzone;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\DI\UIServices;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilObjFileUploadDropzone
{
    use ilObjFileCopyrightInput;
    use ilObjFileTransformation;

    protected ilObjectDefinition $definition;
    protected ilCtrlInterface $ctrl;
    protected UploadHandler $upload_handler;
    protected ilLanguage $language;
    protected ilAccess $access;
    protected UIServices $ui;
    protected \ILIAS\Refinery\Factory $refinery;

    protected int $target_ref_id;
    protected ?string $content;

    public function __construct(int $target_ref_id, string $content = null)
    {
        global $DIC;

        $this->definition = $DIC['objDefinition'];
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('file');
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->refinery = $DIC->refinery();

        $this->upload_handler = new ilObjFileUploadHandlerGUI();
        $this->target_ref_id = $target_ref_id;
        $this->content = $content;
    }

    public function getDropzone(): FileDropzone
    {
        $this->ctrl->setParameterByClass(
            ilObjFileGUI::class,
            'ref_id',
            $this->target_ref_id
        );
        $this->ctrl->setParameterByClass(
            ilObjFileGUI::class,
            'new_type',
            ilObjFile::OBJECT_TYPE
        );
        $this->ctrl->setParameterByClass(
            ilObjFileGUI::class,
            ilObjFileGUI::PARAM_UPLOAD_ORIGIN,
            ilObjFileGUI::UPLOAD_ORIGIN_DROPZONE
        );

        // Generate POST-URL
        $post_url = $this->ctrl->getFormActionByClass(
            [ilRepositoryGUI::class, ilObjFileGUI::class],
            ilObjFileGUI::CMD_UPLOAD_FILES
        );
        // reset new_type again
        $this->ctrl->clearParameterByClass(ilObjFileGUI::class, 'new_type');

        // add input for copyright selection if enabled in the metadata settings
        $additional_input = null;
        if (ilMDSettings::_getInstance()->isCopyrightSelectionActive()) {
            $additional_input = $this->getCopyrightSelectionInput('set_license_for_all_files');
        }

        /** @var $dropzone FileDropzone */
        $dropzone = $this->ui->factory()->dropzone()->file()->wrapper(
            $this->language->txt('upload_files'),
            $post_url,
            $this->ui->factory()->legacy($this->content ?? ''),
            $this->ui->factory()->input()->field()->file(
                $this->upload_handler,
                $this->language->txt('files'),
                null,
                $this->ui->factory()->input()->field()->group([
                    ilObjFileGUI::PARAM_TITLE => $this->ui->factory()->input()->field()->text(
                        $this->language->txt('title')
                    )->withAdditionalTransformation(
                        $this->getEmptyStringToNullTransformation()
                    ),
                    ilObjFileGUI::PARAM_DESCRIPTION => $this->ui->factory()->input()->field()->textarea(
                        $this->language->txt('description')
                    )->withAdditionalTransformation(
                        $this->getEmptyStringToNullTransformation()
                    ),
                ])
            )->withMaxFiles(
                ilObjFileGUI::UPLOAD_MAX_FILES
            ),
            $additional_input
        )->withSubmitLabel(
            $this->language->txt('upload_files')
        );

        return $dropzone;
    }

    public function isUploadAllowed(string $obj_type): bool
    {
        if ($this->definition->isContainer($obj_type) && $obj_type !== "orgu") {
            return $this->access->checkAccess('create_file', '', $this->target_ref_id, 'file');
        }

        return false;
    }

    public function getDropzoneHtml(): string
    {
        return $this->ui->renderer()->render($this->getDropzone());
    }

    protected function getUIFactory(): ILIAS\UI\Factory
    {
        return $this->ui->factory();
    }

    protected function getLanguage(): \ilLanguage
    {
        return $this->language;
    }

    protected function getRefinery(): \ILIAS\Refinery\Factory
    {
        return $this->refinery;
    }
}
