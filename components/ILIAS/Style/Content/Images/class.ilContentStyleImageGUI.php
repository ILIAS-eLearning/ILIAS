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

use ILIAS\Style\Content;
use ILIAS\Style\Content\Access;
use ILIAS\Repository\Form\FormAdapterGUI;

/**
 * @ilCtrl_Calls ilContentStyleImageGUI: ilRepoStandardUploadHandlerGUI
 */
class ilContentStyleImageGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected Content\InternalGUIService $gui;
    protected Content\StandardGUIRequest $request;
    protected ilObjStyleSheet $object;
    protected Content\ImageManager $manager;
    protected Access\StyleAccessManager $access_manager;
    protected ?string $current_image = null;
    /**
     * @var string[]
     */
    protected array $current_images = [];

    public function __construct(
        Content\InternalDomainService $domain_service,
        Content\InternalGUIService $gui_service,
        Access\StyleAccessManager $access_manager,
        Content\ImageManager $manager
    ) {
        $this->access_manager = $access_manager;

        $this->lng = $domain_service->lng();
        $this->lng->loadLanguageModule("content");

        $this->request = $gui_service
            ->standardRequest();
        $this->gui = $gui_service;

        $images = $this->request->getFiles();
        if (count($images) == 1 && $manager->filenameExists(current($images))) {
            $this->current_image = current($images);
            $this->current_images = $images;
        } else {
            $this->current_images = array_filter($images, function ($i) use ($manager) {
                return $manager->filenameExists($i);
            });
        }

        $this->manager = $manager;
        $this->tpl = $gui_service->mainTemplate();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listImages");

        switch ($next_class) {

            case strtolower(ilRepoStandardUploadHandlerGUI::class):
                $form = $this->getImageForm();
                $gui = $form->getRepoStandardUploadHandlerGUI("image");
                $ctrl->forwardCommand($gui);
                break;

            default:
                if (in_array($cmd, [
                    "listImages", "addImage", "cancelUpload", "uploadImage", "deleteImage",
                    "resizeImageForm", "resizeImage"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function listImages(): void
    {
        $tpl = $this->gui->mainTemplate();
        $ilToolbar = $this->gui->toolbar();
        $ilCtrl = $this->gui->ctrl();
        $lng = $this->lng;

        if ($this->access_manager->checkWrite()) {
            $ilToolbar->addButton(
                $lng->txt("sty_add_image"),
                $ilCtrl->getLinkTarget($this, "addImage")
            );
        }

        $table_gui = new ilStyleImageTableGUI(
            $this,
            "listImages",
            $this->access_manager,
            $this->manager
        );
        $tpl->setContent($table_gui->getHTML());
    }

    public function addImage(): void
    {
        $tpl = $this->gui->mainTemplate();

        $form = $this->getImageForm();
        $tpl->setContent($form->render());
    }

    public function cancelUpload(): void
    {
        $ilCtrl = $this->gui->ctrl();

        $ilCtrl->redirect($this, "listImages");
    }

    public function uploadImage(): void
    {
        $tpl = $this->gui->mainTemplate();
        $ilCtrl = $this->gui->ctrl();
        $ilCtrl->redirect($this, "listImages");
    }

    protected function getImageForm(): FormAdapterGUI
    {
        $form = $this->gui->form(self::class, "uploadImage")
                          ->section("image_section", $this->lng->txt("sty_add_image"))
                          ->file(
                              "image",
                              $this->lng->txt("sty_image_file"),
                              $this->handleImageUpload(...),  // Placeholder for upload handler
                              "image_id",
                              "",
                              1,  // Limit to 1 file upload
                              ["image/jpeg", "image/png", "image/gif", "image/svg+xml"]  // Accepted file types
                          );
        return $form;
    }

    public function handleImageUpload(
        \ILIAS\FileUpload\FileUpload $upload,
        \ILIAS\FileUpload\DTO\UploadResult $result
    ): \ILIAS\FileUpload\Handler\BasicHandlerResult {
        $this->manager->importFromUploadResult(
            $result
        );
        return new \ILIAS\FileUpload\Handler\BasicHandlerResult(
            '',
            \ILIAS\FileUpload\Handler\HandlerResult::STATUS_OK,
            "dummy",
            ''
        );
    }

    public function deleteImage(): void
    {
        $ilCtrl = $this->gui->ctrl();
        foreach ($this->current_images as $i) {
            $this->manager->deleteByFilename($i);
        }
        $ilCtrl->redirect($this, "listImages");
    }

    protected function resizeImageForm(): void
    {
        $this->tpl->setContent($this->getResizeImageForm()->getHTML());
    }

    public function getResizeImageForm(): ilPropertyFormGUI
    {
        $ctrl = $this->gui->ctrl();
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();

        $image = $this->manager->getByFilename($this->current_image);

        // width height
        $width_height = new ilWidthHeightInputGUI($lng->txt("cont_width") .
            " / " . $lng->txt("cont_height"), "width_height");
        $width_height->setConstrainProportions(true);
        $width_height->setHeight($image->getHeight());
        $width_height->setWidth($image->getWidth());
        $form->addItem($width_height);

        // file
        $hi = new ilHiddenInputGUI("file");
        $hi->setValue($this->current_image);
        $form->addItem($hi);

        $form->addCommandButton("resizeImage", $lng->txt("sty_resize"));

        $form->setTitle($lng->txt("sty_resize_image"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function resizeImage(): void
    {
        $ctrl = $this->gui->ctrl();
        $lng = $this->lng;
        $main_tpl = $this->gui->mainTemplate();

        $form = $this->getResizeImageForm();
        if ($form->checkInput()) {
            $wh = $form->getInput("width_height");

            $this->manager->resizeImage(
                $this->current_image,
                (int) ($wh["width"] ?? 0),
                (int) ($wh["height"] ?? 0),
                (bool) ($wh["const_prop"] ?? false)
            );

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "listImages");
        } else {
            $form->setValuesByPost();
            $main_tpl->setContent($form->getHTML());
        }
    }
}
