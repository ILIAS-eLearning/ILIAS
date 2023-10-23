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

use Psr\Http\Message\RequestInterface;
use ILIAS\Data\DataSize;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

/**
 * Class ilFileVersionFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileVersionFormGUI
{
    public const MODE_ADD = 1;
    public const MODE_REPLACE = 2;
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = "description";
    public const F_FILE = "file";
    public const F_SAVE_MODE = 'save_mode';

    private \ilObjFile $file;
    private ?\ILIAS\UI\Component\Input\Container\Form\Standard $form;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private RequestInterface $request;
    private ilGlobalTemplateInterface $global_tpl;
    private ilLanguage $lng;
    private \ILIAS\ResourceStorage\Services $resource_services;
    private string $post_url;
    private UploadLimitResolver $upload_limit;

    /**
     * ilFileVersionFormGUI constructor.
     */
    public function __construct(ilFileVersionsGUI $file_version_gui, private int $save_mode = self::MODE_ADD)
    {
        global $DIC;
        $this->file = $file_version_gui->getFile();
        ;
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->global_tpl = $DIC->ui()->mainTemplate();
        $this->resource_services = $DIC->resourceStorage();
        $this->post_url = $DIC->ctrl()->getFormAction(
            $file_version_gui,
            $this->resolveParentCommand($save_mode)
        );
        $this->lng->loadLanguageModule('file');
        $this->upload_limit = $DIC['ui.upload_limit_resolver'];
        $this->initForm();
    }

    private function initForm(): void
    {
        switch ($this->save_mode) {
            case self::MODE_REPLACE:
                $this->global_tpl->setOnScreenMessage('info', $this->lng->txt('replace_file_info'));
                $group_title = $this->lng->txt('replace_file');
                break;
            case self::MODE_ADD:
            default:
                $this->global_tpl->setOnScreenMessage('info', $this->lng->txt('file_new_version_info'));
                $group_title = $this->lng->txt('file_new_version');
                break;
        }

        $upload_handler = new ilFileVersionsUploadHandlerGUI(
            $this->file,
            $this->save_mode === self::MODE_REPLACE
                ? ilFileVersionsUploadHandlerGUI::MODE_REPLACE
                : ilFileVersionsUploadHandlerGUI::MODE_APPEND
        );

        $size = new DataSize(
            $this->upload_limit->getBestPossibleUploadLimitInBytes($upload_handler),
            DataSize::MB
        );

        $inputs = [
            self::F_TITLE => $this->ui_factory->input()->field()->text(
                $this->lng->txt(self::F_TITLE),
                $this->lng->txt("if_no_title_then_filename")
            )->withRequired(false)
             ->withMaxLength(ilObject::TITLE_LENGTH),
            self::F_DESCRIPTION => $this->ui_factory->input()->field()->textarea(
                $this->lng->txt(self::F_DESCRIPTION)
            ),
            self::F_FILE => $this->ui_factory->input()->field()->file(
                $upload_handler,
                $this->lng->txt(self::F_FILE),
                sprintf(
                    $this->lng->txt('upload_files_limit'),
                    (string) $size
                ),
            )->withMaxFiles(1)
             ->withRequired(true),
        ];

        $group = $this->ui_factory->input()->field()->group($inputs, $group_title);

        $this->form = $this->ui_factory->input()->container()->form()->standard($this->post_url, [$group]);
    }

    public function saveObject(): bool
    {
        $this->form = $this->form->withRequest($this->request);
        $data = $this->form->getData()[0];

        $title = $data[self::F_TITLE] !== '' ? $data[self::F_TITLE] : $this->file->getTitle();
        $description = $data[self::F_DESCRIPTION] !== '' ? $data[self::F_DESCRIPTION] : $this->file->getDescription();
        $revision_number = (int) $data[self::F_FILE][0];

        // Title differs, update Revision and Object
        $rid = $this->resource_services->manage()->find($this->file->getResourceId());
        $resource = $this->resource_services->manage()->getResource($rid);
        $new_revision = $resource->getSpecificRevision($revision_number);
        $new_revision->setTitle($title);
        $this->resource_services->manage()->updateRevision($new_revision);

        $this->file->setDescription($description);
        $this->file->updateObjectFromCurrentRevision();

        return true;
    }

    public function getHTML(): string
    {
        return $this->ui_renderer->render($this->form);
    }

    private function resolveParentCommand(int $mode): string
    {
        return match ($mode) {
            self::MODE_REPLACE => ilFileVersionsGUI::CMD_CREATE_REPLACING_VERSION,
            default => ilFileVersionsGUI::CMD_CREATE_NEW_VERSION,
        };
    }
}
