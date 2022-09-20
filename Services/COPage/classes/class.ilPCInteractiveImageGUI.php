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

/**
 * User interface class for interactive images
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCInteractiveImageGUI: ilPCIIMTriggerEditorGUI
 */
class ilPCInteractiveImageGUI extends ilPageContentGUI
{
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
            $tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
            $this->getTabs();
        }

        switch ($next_class) {
            // trigger editor
            case "ilpciimtriggereditorgui":
                $ilTabs->setTabActive("triggers");
                /** @var ilPCInteractiveImage $iim */
                $iim = $this->content_obj;
                $image_map_edit = new ilPCIIMTriggerEditorGUI(
                    $iim,
                    $this->pg_obj,
                    $this->request
                );
                $ret = $this->ctrl->forwardCommand($image_map_edit);
                if ($ret != "") {
                    $tpl->setContent($ret);
                }
                break;

            default:
                $this->$cmd();
                break;
        }
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

            $ilTabs->addTab(
                "triggers",
                $lng->txt("cont_active_areas"),
                $ilCtrl->getLinkTargetByClass("ilpciimtriggereditorgui", "editMapAreas")
            );

            $ilTabs->addTab(
                "list_overlays",
                $lng->txt("cont_overlay_images"),
                $ilCtrl->getLinkTarget($this, "listOverlayImages")
            );

            $ilTabs->addTab(
                "content_popups",
                $lng->txt("cont_content_popups"),
                $ilCtrl->getLinkTarget($this, "listContentPopups")
            );

            $ilTabs->addTab(
                "edit_base_image",
                $lng->txt("cont_base_image") . " & " . $lng->txt("cont_caption"),
                $ilCtrl->getLinkTarget($this, "editBaseImage")
            );
        }
    }

    /**
     * Insert new media object form
     */
    public function insert(
        string $a_post_cmd = "edpost",
        string $a_submit_cmd = "create_mob",
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
        $ilCtrl->redirectByClass(array("ilpcinteractiveimagegui", "ilpciimtriggereditorgui"), "editMapAreas");
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
        $fi->setALlowDeletion(false);
        if ($a_mode == "edit") {
            $fi->setImage($this->content_obj->getBaseThumbnailTarget());
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
                $this->dom,
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

    public function create(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->content_obj = new ilPCInteractiveImage($this->getPage());
        $this->content_obj->createMediaObject();
        $media_obj = $this->content_obj->getMediaObject();
        $media_obj->setTitle($_FILES['image_file']['name']);
        $media_obj->create();
        $mob_dir = ilObjMediaObject::_getDirectory($media_obj->getId());
        //		$media_obj->setStandardType("File");
        $media_obj->createDirectory();
        $media_item = new ilMediaItem();
        $media_obj->addMediaItem($media_item);
        $media_item->setPurpose("Standard");

        $file = $mob_dir . "/" . $_FILES['image_file']['name'];
        try {
            ilFileUtils::moveUploadedFile(
                $_FILES['image_file']['tmp_name'],
                $_FILES['image_file']['name'],
                $file
            );
        } catch (ilException $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("file_is_infected"), true);
            return;
        }
        // get mime type
        $format = ilObjMediaObject::getMimeType($file);
        $location = $_FILES['image_file']['name'];

        // set real meta and object data
        $media_item->setFormat($format);
        $media_item->setLocation($location);
        $media_item->setLocationType("LocalFile");

        ilFileUtils::renameExecutables($mob_dir);
        $media_obj->update();

        $this->content_obj->createAlias($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->pg_obj->stripHierIDs();
            $this->pg_obj->addHierIDs();
            $ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
            $ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
            $this->content_obj->setHierId($this->content_obj->readHierId());
            $this->setHierId($this->content_obj->readHierId());
            $this->content_obj->setPcId($this->content_obj->readPCId());
            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_saved_interactive_image"), true);
            $this->ctrl->redirectByClass("ilpcinteractiveimagegui", "edit");

        //$this->ctrl->returnToParent($this, "jump".$this->hier_id);
        } else {
            $this->insert();
        }
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
                $this->dom,
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
            $this->dom,
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
            $this->dom,
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
            $this->dom,
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
            $this->dom,
            $this->getHierId(),
            "Standard",
            $this->content_obj->getPCId(),
            "InteractiveImage"
        );
        $std_alias_item->setHorizontalAlign("RightFloat");
        $this->updateAndReturn();
    }

    ////
    //// Overlay Images
    ////

    public function listOverlayImages(): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        $this->tpl->setOnScreenMessage('info', $lng->txt("cont_iim_overlay_info"));

        $ilTabs->setTabActive("list_overlays");

        $ilToolbar->addButton(
            $lng->txt("cont_add_images"),
            $ilCtrl->getLinkTarget($this, "addOverlayImages")
        );

        $tab = new ilPCIIMOverlaysTableGUI(
            $this,
            "listOverlayImages",
            $this->content_obj->getMediaObject()
        );
        $tpl->setContent($tab->getHTML());
    }

    public function addOverlayImages(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        if ($a_form) {
            $form = $a_form;
        } else {
            $form = $this->initAddOverlaysForm();
        }

        $tpl->setContent($form->getHTML());
    }

    public function initAddOverlaysForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilTabs->setTabActive("list_overlays");

        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("cont_add_images"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        // file input
        $fi = new ilFileWizardInputGUI($lng->txt("file"), "ovfile");
        $fi->setSuffixes(array("gif", "jpeg", "jpg", "png"));
        $fi->setFilenames(array(0 => ''));
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("uploadOverlayImages", $lng->txt("upload"));
        $form->addCommandButton("listOverlayImages", $lng->txt("cancel"));

        return $form;
    }

    public function uploadOverlayImages(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initAddOverlaysForm();
        if ($form->checkInput()) {
            if (is_array($_FILES["ovfile"]["name"])) {
                foreach ($_FILES["ovfile"]["name"] as $k => $v) {
                    $name = $_FILES["ovfile"]["name"][$k];
                    $tmp_name = $_FILES["ovfile"]["tmp_name"][$k];

                    $this->content_obj->getMediaObject()->uploadAdditionalFile(
                        $name,
                        $tmp_name,
                        "overlays"
                    );
                    $piname = pathinfo($name);
                    $this->content_obj->getMediaObject()->makeThumbnail(
                        "overlays/" . $name,
                        basename($name, "." . $piname['extension']) . ".png"
                    );
                }
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"));
            $ilCtrl->redirect($this, "listOverlayImages");
        } else {
            $form->setValuesByPost();
            $this->addOverlayImages($form);
        }
    }

    public function confirmDeleteOverlays(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilTabs->setTabActive("list_overlays");

        $files = $this->request->getStringArray("file");
        if (count($files) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listOverlayImages");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_delete_overlays"));
            $cgui->setCancel($lng->txt("cancel"), "listOverlayImages");
            $cgui->setConfirm($lng->txt("delete"), "deleteOverlays");

            foreach ($files as $i => $d) {
                $cgui->addItem("file[]", $i, $i);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteOverlays(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $files = $this->request->getStringArray("file");
        if (count($files) > 0) {
            foreach ($files as $f) {
                $f = str_replace("..", "", ilUtil::stripSlashes($f));
                $this->content_obj->getMediaObject()
                    ->removeAdditionalFile("overlays/" . $f);
            }

            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_overlays_have_been_deleted"), true);
        }
        $ilCtrl->redirect($this, "listOverlayImages");
    }


    ////
    //// Content Popups
    ////

    public function listContentPopups(): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        $this->tpl->setOnScreenMessage('info', $lng->txt("cont_iim_content_popups_info"));

        $ilTabs->setTabActive("content_popups");

        $ilToolbar->addButton(
            $lng->txt("cont_add_popup"),
            $ilCtrl->getLinkTarget($this, "addPopup")
        );

        /** @var ilPCInteractiveImage $iim */
        $iim = $this->content_obj;
        $tab = new ilPCIIMPopupTableGUI(
            $this,
            "listContentPopups",
            $iim
        );
        $tpl->setContent($tab->getHTML());
    }

    public function addPopup(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->content_obj->addContentPopup();
        $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listContentPopups");
    }

    public function savePopups(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $titles = $this->request->getStringArray("title");
        if (count($titles) > 0) {
            $this->content_obj->savePopups($titles);
            $this->pg_obj->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listContentPopups");
    }

    public function confirmPopupDeletion(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilTabs->setTabActive("content_popups");

        $tids = $this->request->getStringArray("tid");
        $titles = $this->request->getStringArray("title");

        if (count($tids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listContentPopups");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_delete_popups"));
            $cgui->setCancel($lng->txt("cancel"), "listContentPopups");
            $cgui->setConfirm($lng->txt("delete"), "deletePopups");

            foreach ($tids as $i => $d) {
                $cgui->addItem("tid[]", $i, $titles[$i]);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deletePopups(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $tids = $this->request->getStringArray("tid");

        if (count($tids) > 0) {
            foreach ($tids as $id) {
                $id = explode(":", $id);
                $this->content_obj->deletePopup($id[0], $id[1]);
            }
            $this->pg_obj->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_popups_have_been_deleted"), true);
        }
        $ilCtrl->redirect($this, "listContentPopups");
    }
}
