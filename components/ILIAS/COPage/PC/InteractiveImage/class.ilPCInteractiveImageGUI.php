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

use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\HandlerResult;

/**
 * User interface class for interactive images
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCInteractiveImageGUI: ilPCIIMTriggerEditorGUI, ilRepoStandardUploadHandlerGUI
 */
class ilPCInteractiveImageGUI extends ilPageContentGUI
{
    protected \ILIAS\COPage\PC\InteractiveImage\IIMManager $iim_manager;
    protected \ILIAS\COPage\PC\InteractiveImage\GUIService $iim_gui;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->iim_gui = $DIC->copage()->internal()->gui()->pc()->interactiveImage();
        $this->iim_manager = $DIC->copage()->internal()->domain()->pc()->interactiveImage();
    }

    public function executeCommand(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        if (is_object($this->content_obj)) {
            $tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_mob.svg"));
            $this->getTabs();
        }

        switch ($next_class) {
            case strtolower(ilRepoStandardUploadHandlerGUI::class):
                $this->forwardFormToUploadHandler();
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    protected function forwardFormToUploadHandler(): void
    {
        switch ($this->request->getString("mode")) {
            case "overlayUpload":
                $form = $this->getOverlayUploadFormAdapter();
                $gui = $form->getRepoStandardUploadHandlerGUI("overlay_file");
                break;

            case "backgroundUpdate":
                $form = $this->getBackgroundPropertiesFormAdapter();
                $gui = $form->getRepoStandardUploadHandlerGUI("input_file");
                break;

            default:
                $form = $this->getImportFormAdapter();
                $gui = $form->getRepoStandardUploadHandlerGUI("input_file");
                break;
        }
        $this->ctrl->forwardCommand($gui);
    }

    /**
     * Add tabs to ilTabsGUI object
     */
    public function getTabs(
        bool $a_create = false,
        bool $a_change_obj_ref = false
    ): void {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        if (!$a_create) {
            $ilTabs->setBackTarget(
                $lng->txt("pg"),
                (string) $ilCtrl->getParentReturn($this)
            );
        }
    }

    /**
     * Insert new media object form
     */
    public function insert(
        string $a_post_cmd = "edpost",
        string $a_submit_cmd = "",
        bool $a_input_error = false
    ): void {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->tpl->setOnScreenMessage('info', $lng->txt("cont_iim_create_info"));

        $form = $this->initForm("create");
        $form->setFormAction($ilCtrl->getFormAction($this));

        $this->displayValidationError();

        $tpl->setContent($form->getHTML());
    }

    public function edit(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "editor");
    }

    public function editBaseImage(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilTabs->activateTab("edit_base_image");
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }


    public function initForm(string $a_mode = "edit"): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ti = null;

        $form = new ilPropertyFormGUI();

        // image file
        $fi = new ilImageFileInputGUI($lng->txt("cont_file"), "image_file");
        $fi->setAllowDeletion(false);
        if ($a_mode == "edit") {
            $fi->setImage($this->content_obj->getBaseThumbnailTarget());
        } else {
            $fi->setRequired(true);
        }
        $form->addItem($fi);

        if ($a_mode == "edit") {
            // caption
            $ti = new ilTextInputGUI($this->lng->txt("cont_caption"), "caption");
            $ti->setMaxLength(200);
            $ti->setSize(50);
            $form->addItem($ti);
        }

        // save and cancel commands
        if ($a_mode == "create") {
            $form->setTitle($lng->txt("cont_ed_insert_iim"));
            $form->addCommandButton("create_iim", $lng->txt("save"));
            $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            // get caption
            $std_alias_item = new ilMediaAliasItem(
                $this->content_obj->getDomDoc(),
                $this->getHierId(),
                "Standard",
                $this->content_obj->getPCId(),
                "InteractiveImage"
            );
            $ti->setValue($std_alias_item->getCaption());

            $form->setTitle($lng->txt("cont_edit_base_image"));
            $form->addCommandButton("update", $lng->txt("save"));
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }


    /**
     * Update (base image)
     */
    public function update(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $form = $this->initForm("edit");
        if ($form->checkInput()) {
            $mob = $this->content_obj->getMediaObject();
            $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
            $std_item = $mob->getMediaItem("Standard");
            $location = $_FILES['image_file']['name'];

            if ($location != "" && is_file($_FILES['image_file']['tmp_name'])) {
                $file = $mob_dir . "/" . $_FILES['image_file']['name'];
                ilFileUtils::moveUploadedFile(
                    $_FILES['image_file']['tmp_name'],
                    $_FILES['image_file']['name'],
                    $file
                );

                // get mime type
                $format = ilObjMediaObject::getMimeType($file);
                $location = $_FILES['image_file']['name'];
                $std_item->setFormat($format);
                $std_item->setLocation($location);
                $std_item->setLocationType("LocalFile");
                $mob->setDescription($format);
                $mob->update();
            }

            // set caption
            $std_alias_item = new ilMediaAliasItem(
                $this->content_obj->getDomDoc(),
                $this->getHierId(),
                "Standard",
                $this->content_obj->getPCId(),
                "InteractiveImage"
            );
            $std_alias_item->setCaption(
                $form->getInput("caption")
            );
            $this->edit_repo->setPageError($this->pg_obj->update());
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirectByClass("ilpcinteractiveimagegui", "editBaseImage");
    }


    /**
     * Align media object to center
     */
    public function centerAlign(): void
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->content_obj->getDomDoc(),
            $this->getHierId(),
            "Standard",
            $this->content_obj->getPCId(),
            "InteractiveImage"
        );
        $std_alias_item->setHorizontalAlign("Center");
        $this->updateAndReturn();
    }

    /**
     * align media object to left
     */
    public function leftAlign(): void
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->dom,
            $this->getHierId(),
            "Standard",
            $this->content_obj->getPCId(),
            "InteractiveImage"
        );
        $std_alias_item->setHorizontalAlign("Left");
        $this->updateAndReturn();
    }

    /**
     * align media object to right
     */
    public function rightAlign(): void
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->content_obj->getDomDoc(),
            $this->getHierId(),
            "Standard",
            $this->content_obj->getPCId(),
            "InteractiveImage"
        );
        $std_alias_item->setHorizontalAlign("Right");
        $this->updateAndReturn();
    }

    /**
     * align media object to left, floating text
     */
    public function leftFloatAlign(): void
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->content_obj->getDomDoc(),
            $this->getHierId(),
            "Standard",
            $this->content_obj->getPCId(),
            "InteractiveImage"
        );
        $std_alias_item->setHorizontalAlign("LeftFloat");
        $this->updateAndReturn();
    }

    /**
     * align media object to right, floating text
     */
    public function rightFloatAlign(): void
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->content_obj->getDomDoc(),
            $this->getHierId(),
            "Standard",
            $this->content_obj->getPCId(),
            "InteractiveImage"
        );
        $std_alias_item->setHorizontalAlign("RightFloat");
        $this->updateAndReturn();
    }

    public function getImportFormAdapter(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        $this->ctrl->setParameter($this, "cname", "InteractiveImage");
        $form = $this->gui->form([self::class], "#")
                          ->async()
            ->section("f", $this->lng->txt("cont_ed_insert_iim"))
                          ->file(
                              "input_file",
                              $this->lng->txt("file"),
                              \Closure::fromCallable([$this, 'handleUploadResult']),
                              "mob_id",
                              "",
                              1,
                              [],
                              [self::class],
                              "copg"
                          )->required();
        return $form;
    }

    public function handleUploadResult(
        FileUpload $upload,
        UploadResult $result
    ): BasicHandlerResult {
        return $this->iim_manager->handleUploadResult($upload, $result);
    }

    public function editor(): void
    {
        $ilTabs = $this->tabs;
        $ilTabs->activateTab("editor");
        $this->tpl->addCss(ilObjStyleSheet::getBaseContentStylePath());
        $this->tpl->setContent($this->iim_gui->editorInit()->getInitHtml());
        $this->initInteractiveImageEditor();
    }

    protected function initInteractiveImageEditor(): void
    {
        $this->setEditorToolContext();
        $this->iim_gui->editorInit()->initUI($this->tpl);
    }

    public function getOverlayUploadFormAdapter(?array $path = null): \ILIAS\Repository\Form\FormAdapterGUI
    {
        if (is_null($path)) {
            $path = [self::class];
        }

        $f = $this->gui->form($path, "#")
                       ->async()
                       ->file(
                           "overlay_file",
                           $this->lng->txt("file"),
                           \Closure::fromCallable([$this, 'handleOverlayUpload']),
                           "mob_id",
                           "",
                           1,
                           ["image/png", "image/jpeg", "image/gif"],
                           $path,
                           "copg"
                       );
        return $f;
    }


    public function handleOverlayUpload(
        FileUpload $upload,
        UploadResult $result
    ): BasicHandlerResult {
        return $this->iim_manager->handleOverlayUpload(
            $this->content_obj->getMediaObject(),
            $upload,
            $result
        );
    }

    public function getPopupFormAdapter(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        $f = $this->gui->form(null, "#")
                       ->text(
                           "title",
                           $this->lng->txt("title")
                       );
        return $f;
    }

    public function getBackgroundPropertiesFormAdapter(?array $path = null): \ILIAS\Repository\Form\FormAdapterGUI
    {
        if (is_null($path)) {
            $path = [self::class];
        }

        $f = $this->gui->form($path, "#")
                       ->async()
                       ->file(
                           "input_file",
                           $this->lng->txt("file"),
                           \Closure::fromCallable([$this, 'handleBackgroundUpload']),
                           "mob_id",
                           "",
                           1,
                           ["image/png", "image/jpeg", "image/gif"],
                           $path,
                           "copg"
                       )->text(
                           "caption",
                           $this->lng->txt("cont_caption")
                       );
        return $f;
    }


    public function handleBackgroundUpload(
        FileUpload $upload,
        UploadResult $result
    ): BasicHandlerResult {
        $this->log->debug(">>>");
        $this->log->debug("Start upload");
        $this->log->debug($this->content_obj->getMediaObject()->getId());
        return $this->iim_manager->handleUploadResult(
            $upload,
            $result,
            $this->content_obj->getMediaObject()
        );
    }

}
