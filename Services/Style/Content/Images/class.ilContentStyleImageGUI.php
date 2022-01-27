<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use \ILIAS\UI\Component\Input\Container\Form;
use \Psr\Http\Message;
use \ILIAS\Style\Content;
use \ILIAS\Style\Content\Access;

/**
 * Conent style images UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContentStyleImageGUI
{
    use Content\UI;

    /**
     * @var ilObjStyleSheet
     */
    protected $object;

    /**
     * @var Content\ImageManager
     */
    protected $manager;

    /**
     * @var Access\StyleAccessManager
     */
    protected $access_manager;

    /**
     * @var string
     */
    protected $current_image = null;

    /**
     * @var string[]
     */
    protected $current_images = [];

    /**
     * ilContentStyleImageGUI constructor.
     * @param Content\UIFactory             $ui_factory
     * @param Access\StyleAccessManager     $access_manager
     * @param Content\ImageManager          $manager
     */
    public function __construct(
        Content\UIFactory $ui_factory,
        Access\StyleAccessManager $access_manager,
        Content\ImageManager $manager
    ) {
        $this->initUI($ui_factory);

        $this->access_manager = $access_manager;

        $this->lng->loadLanguageModule("content");

        $params = $this->request->getQueryParams();
        $body = $this->request->getParsedBody();

        $image = (string) ($params["file"] ?? "");
        if ($image == "") {
            $image = ($body["file"] ?? "");
        }
        if (is_string($image) && $manager->filenameExists($image)) {
            $this->current_image = $image;
        } elseif (is_array($image)) {
            $this->current_images = array_filter($image, function ($i) use ($manager) {
                return $manager->filenameExists($i);
            });
        }

        $this->manager = $manager;
    }

    /**
     * Execute command
     */
    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listImages");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "listImages", "addImage", "cancelUpload", "uploadImage", "deleteImage",
                    "resizeImageForm", "resizeImage"
                ])) {
                    $this->$cmd();
                }
        }
    }

    /**
     * List images of style
     */
    public function listImages() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
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

    /**
     * Add an image
     */
    public function addImage() : void
    {
        $tpl = $this->tpl;

        $form = $this->getImageForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Cancel Upload
     */
    public function cancelUpload() : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "listImages");
    }

    /**
     * Upload image
     */
    public function uploadImage() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $form = $this->getImageForm();

        if ($form->checkInput()) {
            $this->manager->uploadImage();
            $ilCtrl->redirect($this, "listImages");
        } else {
            //$this->form_gui->setImageFormValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Init image form
     */
    protected function getImageForm() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form_gui = new ilPropertyFormGUI();

        $form_gui->setTitle($lng->txt("sty_add_image"));

        $file_input = new ilImageFileInputGUI($lng->txt("sty_image_file"), "image_file");
        $file_input->setSuffixes(["jpg","jpeg","png","gif","svg"]);
        $file_input->setRequired(true);
        $form_gui->addItem($file_input);

        $form_gui->addCommandButton("uploadImage", $lng->txt("upload"));
        $form_gui->addCommandButton("cancelUpload", $lng->txt("cancel"));
        $form_gui->setFormAction($ilCtrl->getFormAction($this));

        return $form_gui;
    }

    /**
     * Delete images
     */
    public function deleteImage()
    {
        $ilCtrl = $this->ctrl;

        foreach ($this->current_images as $i) {
            $this->manager->deleteByFilename($i);
        }
        $ilCtrl->redirect($this, "listImages");
    }

    /**
     *
     * @param
     * @return
     */
    protected function resizeImageForm()
    {
        $this->tpl->setContent($this->getResizeImageForm()->getHTML());
    }

    /**
     * Get resize image form.
     * @return ilPropertyFormGUI
     */
    public function getResizeImageForm() : ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
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
        $hi->setValue($_GET["file"]);
        $form->addItem($hi);

        $form->addCommandButton("resizeImage", $lng->txt("sty_resize"));

        $form->setTitle($lng->txt("sty_resize_image"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Save  form
     */
    public function resizeImage()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;

        $form = $this->getResizeImageForm();
        if ($form->checkInput()) {
            $wh = $form->getInput("width_height");

            $this->manager->resizeImage(
                $this->current_image,
                (int) $wh["width"],
                (int) $wh["height"],
                (bool) $wh["const_prop"]
            );

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "listImages");
        } else {
            $form->setValuesByPost();
            $main_tpl->setContent($form->getHtml());
        }
    }
}
