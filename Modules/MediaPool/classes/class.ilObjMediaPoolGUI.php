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

use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\FileUpload;
use ILIAS\MediaPool\StandardGUIRequest;

/**
 * User Interface class for media pool objects
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjMediaPoolGUI: ilObjMediaObjectGUI, ilObjFolderGUI, ilEditClipboardGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjMediaPoolGUI: ilInfoScreenGUI, ilMediaPoolPageGUI, ilExportGUI, ilFileSystemGUI
 * @ilCtrl_Calls ilObjMediaPoolGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI, ilObjectTranslationGUI, ilMediaPoolImportGUI
 * @ilCtrl_Calls ilObjMediaPoolGUI: ilMobMultiSrtUploadGUI, ilObjectMetaDataGUI
 */
class ilObjMediaPoolGUI extends ilObject2GUI
{
    protected ilPropertyFormGUI $form;
    protected string $mode;
    protected int $mep_item_id;
    protected StandardGUIRequest $mep_request;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilGlobalTemplateInterface $main_tpl;
    protected FileUpload $upload;
    protected ilLogger $mep_log;
    public bool $output_prepared;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        global $DIC;

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->help = $DIC["ilHelp"];

        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->upload = $DIC->upload();

        $this->mep_log = ilLoggerFactory::getLogger("mep");

        $this->mep_request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->mep_item_id = $this->mep_request->getItemId();
        $this->mode = ($this->mep_request->getMode() != "")
            ? $this->mep_request->getMode()
            : "listMedia";
    }

    protected function getMediaPool() : ilObjMediaPool
    {
        /** @var ilObjMediaPool $mp */
        $mp = $this->object;
        return $mp;
    }

    /**
     * @throws ilCtrlException
     */
    protected function afterConstructor()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("mep");
        
        if ($this->ctrl->getCmd() == "explorer") {
            $this->ctrl->saveParameter($this, array("ref_id"));
        } else {
            $this->ctrl->saveParameter($this, array("ref_id", "mepitem_id"));
        }
        $this->ctrl->saveParameter($this, array("mep_mode"));
        
        $lng->loadLanguageModule("content");
    }

    final public function getType()
    {
        return "mep";
    }
    
    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     * @throws ilPermissionException
     */
    public function executeCommand() : void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $new_type = $this->mep_request->getNewType();
        $tree = null;

        if ($new_type != "" && ($cmd != "confirmRemove" && $cmd != "copyToClipboard"
            && $cmd != "pasteFromClipboard")) {
            $this->setCreationMode(true);
        }

        if (!$this->getCreationMode()) {
            $tree = $this->getMediaPool()->getTree();
            if ($this->mep_item_id == 0) {
                $this->mep_item_id = $tree->getRootId();
            }
        }
        if ($cmd == "create") {
            switch ($this->mep_request->getNewType()) {
                case "mob":
                    $this->ctrl->redirectByClass("ilobjmediaobjectgui", "create");
                    break;
                    
                case "fold":
                    $this->ctrl->redirectByClass("ilobjfoldergui", "create");
                    break;
            }
        }

        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->checkPermission("write");

                $this->prepareOutput();
                $this->addHeaderAction();

                $this->tabs_gui->activateTab('meta_data');
                $md_gui = new ilObjectMetaDataGUI($this->object, 'mob');
                $this->ctrl->forwardCommand($md_gui);
                $this->tpl->printToStdout();
                break;


            case 'ilmediapoolpagegui':
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $ilTabs->clearTargets();
                $mep_page_gui = new ilMediaPoolPageGUI(
                    $this->mep_item_id,
                    $this->mep_request->getOldNr()
                );

                if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                    $mep_page_gui->setEnableEditing(false);
                }
                $ret = $this->ctrl->forwardCommand($mep_page_gui);
                if ($ret != "") {
                    $tpl->setContent($ret);
                }
                $this->setMediaPoolPageTabs();
                $this->tpl->printToStdout();
                break;

            case "ilobjmediaobjectgui":
                $this->checkPermission("write");
                if ($cmd == "create" || $cmd == "save" || $cmd == "cancel") {
                    $ret_obj = $this->mep_item_id;
                    $ilObjMediaObjectGUI = new ilObjMediaObjectGUI("", 0, false, false);
                    $ilObjMediaObjectGUI->setWidthPreset($this->getMediaPool()->getDefaultWidth());
                    $ilObjMediaObjectGUI->setHeightPreset($this->getMediaPool()->getDefaultHeight());
                } else {
                    $ret_obj = $tree->getParentId($this->mep_item_id);
                    $ilObjMediaObjectGUI = new ilObjMediaObjectGUI("", ilMediaPoolItem::lookupForeignId($this->mep_item_id), false, false);
                    $this->ctrl->setParameter($this, "mepitem_id", $this->getParentFolderId());
                    $ilTabs->setBackTarget(
                        $lng->txt("back"),
                        $this->ctrl->getLinkTarget(
                            $this,
                            $this->mode
                        )
                    );
                }
                if ($this->ctrl->getCmdClass() == "ilinternallinkgui") {
                    $this->ctrl->setReturn($this, "explorer");
                } else {
                    $this->ctrl->setParameter($this, "mepitem_id", $ret_obj);
                    $this->ctrl->setReturn(
                        $this,
                        $this->mode
                    );
                    $this->ctrl->setParameter($this, "mepitem_id", $this->mep_item_id);
                }
                $this->getTemplate();
                $ilObjMediaObjectGUI->setTabs();
                $this->setLocator();

                // set adv metadata record dobject
                $ilObjMediaObjectGUI->setAdvMdRecordObject($this->object->getRefId(), "mep", "mob");

                $ret = $this->ctrl->forwardCommand($ilObjMediaObjectGUI);

                if ($cmd == "save" && $ret != false) {
                    $mep_item = new ilMediaPoolItem();
                    $mep_item->setTitle($ret->getTitle());
                    $mep_item->setType("mob");
                    $mep_item->setForeignId($ret->getId());
                    $mep_item->create();

                    $parent = ($this->mep_item_id == 0)
                        ? $tree->getRootId()
                        : $this->mep_item_id;
                    $tree->insertNode($mep_item->getId(), $parent);
                    ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&cmd=listMedia&ref_id=" .
                        $this->requested_ref_id . "&mepitem_id=" . $this->mep_item_id);
                } else {
                    $this->tpl->printToStdout();
                }
                break;

            case "ilobjfoldergui":
                $this->checkPermission("write");
                $this->addHeaderAction();
                $folder_gui = new ilObjFolderGUI("", 0, false, false);
                $this->ctrl->setReturn($this, "listMedia");
                $cmd .= "Object";
                switch ($cmd) {
                    case "createObject":
                        $this->prepareOutput();
                        $folder_gui = new ilObjFolderGUI("", 0, false, false);
                        $folder_gui->setFormAction(
                            "save",
                            $this->ctrl->getFormActionByClass("ilobjfoldergui")
                        );
                        $folder_gui->createObject();
                        $this->tpl->printToStdout();
                        break;

                    case "saveObject":
                        //$folder_gui->setReturnLocation("save", $this->ctrl->getLinkTarget($this, "listMedia"));
                        $parent = ($this->mep_item_id == 0)
                            ? $tree->getRootId()
                            : $this->mep_item_id;
                        $folder_gui->setFolderTree($tree);
                        $folder_gui->saveObject($parent);
                        //$this->ctrl->redirect($this, "listMedia");
                        break;

                    case "editObject":
                        $this->prepareOutput();
                        $folder_gui = new ilObjFolderGUI(
                            "",
                            ilMediaPoolItem::lookupForeignId($this->mep_item_id),
                            false,
                            false
                        );
                        $this->ctrl->setParameter($this, "foldereditmode", "1");
                        $folder_gui->setFormAction("update", $this->ctrl->getFormActionByClass("ilobjfoldergui"));
                        $folder_gui->editObject();
                        $this->tpl->printToStdout();
                        break;

                    case "updateObject":
                        $folder_gui = new ilObjFolderGUI(
                            "",
                            ilMediaPoolItem::lookupForeignId($this->mep_item_id),
                            false,
                            false
                        );
                        $this->ctrl->setParameter($this, "mepitem_id", $this->getParentFolderId());
                        $this->ctrl->setReturn($this, "listMedia");
                        $folder_gui->updateObject();		// this returns to parent
                        break;

                    case "cancelObject":
                        if ($this->mep_request->getFolderEditMode()) {
                            $this->ctrl->setParameter($this, "mepitem_id", $this->getParentFolderId());
                        }
                        $this->ctrl->redirect($this, "listMedia");
                        break;
                }
                break;

            case "ileditclipboardgui":
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->ctrl->setReturn($this, $this->mode);
                $clip_gui = new ilEditClipboardGUI();
                $clip_gui->setMultipleSelections(true);
                $clip_gui->setInsertButtonTitle($lng->txt("mep_copy_to_mep"));
                $ilTabs->setTabActive("clipboard");
                $this->ctrl->forwardCommand($clip_gui);
                $this->tpl->printToStdout();
                break;
                
            case 'ilinfoscreengui':
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->infoScreen();
                $this->tpl->printToStdout();
                break;

            case 'ilpermissiongui':
                $this->checkPermission("edit_permission");
                $this->prepareOutput();
                $this->addHeaderAction();
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                $this->tpl->printToStdout();
                break;
                
            case "ilexportgui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ot = ilObjectTranslation::getInstance($this->object->getId());
                if ($ot->getContentActivated()) {
                    $exp_gui->addFormat("xml_master", "XML (" . $lng->txt("mep_master_language_only") . ")", $this, "export");
                    $exp_gui->addFormat("xml_masternomedia", "XML (" . $lng->txt("mep_master_language_only_no_media") . ")", $this, "export");
                }
                $this->ctrl->forwardCommand($exp_gui);
                $this->tpl->printToStdout();
                break;

            case "ilfilesystemgui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "listMedia")
                );
                $mset = new ilSetting("mobs");
                $import_directory_factory = new ilImportDirectoryFactory();
                $mob_import_directory = $import_directory_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_MOB);
                if ($mob_import_directory->exists()) {
                    $fs_gui = new ilFileSystemGUI($mob_import_directory->getAbsolutePath());
                    $fs_gui->setPostDirPath(true);
                    $fs_gui->setTableId("mepud" . $this->object->getId());
                    $fs_gui->setAllowFileCreation(false);
                    $fs_gui->setAllowDirectoryCreation(false);
                    $fs_gui->clearCommands();
                    $fs_gui->addCommand(
                        $this,
                        "selectUploadDirFiles",
                        $this->lng->txt("mep_sel_upload_dir_files"),
                        false,
                        true
                    );
                    $this->ctrl->forwardCommand($fs_gui);
                }
                $this->tpl->printToStdout();
                break;
                
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjecttranslationgui':
                $this->prepareOutput();
                $this->addHeaderAction();
                //$this->setTabs("settings");
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("obj_multilinguality");
                $transgui = new ilObjectTranslationGUI($this);
                $transgui->setTitleDescrOnlyMode(false);
                $this->ctrl->forwardCommand($transgui);
                $this->tpl->printToStdout();
                break;

            case "ilmediapoolimportgui":
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->checkPermission("write");
                $ilTabs->activateTab("import");
                $gui = new ilMediaPoolImportGUI($this->getMediaPool());
                $this->ctrl->forwardCommand($gui);
                $this->tpl->printToStdout();
                break;

            case "ilmobmultisrtuploadgui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->setContentSubTabs("srt_files");
                $gui = new ilMobMultiSrtUploadGUI(new ilMepMultiSrt($this->getMediaPool()));
                $this->ctrl->forwardCommand($gui);
                $this->tpl->printToStdout();
                break;


            default:
                $this->prepareOutput();
                $this->addHeaderAction();
                $cmd = $this->ctrl->getCmd("listMedia") ?: "listMedia";
                $this->$cmd();
                if (!$this->getCreationMode()) {
                    $this->tpl->printToStdout();
                }
                break;
        }
    }

    public function createMediaObject() : void
    {
        $this->ctrl->redirectByClass("ilobjmediaobjectgui", "create");
    }

    protected function initCreationForms($a_new_type)
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $this->initImportForm($a_new_type));

        return $forms;
    }

    protected function afterSave(ilObject $a_new_object)
    {
        // always send a message
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);

        //ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
        ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&ref_id=" . $a_new_object->getRefId() . "&cmd=listMedia");
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->object_service;

        // default width
        $ni = new ilNumberInputGUI($this->lng->txt("mep_default_width"), "default_width");
        $ni->setMinValue(0);
        $ni->setSuffix("px");
        $ni->setMaxLength(5);
        $ni->setSize(5);
        $a_form->addItem($ni);

        // default height
        $ni = new ilNumberInputGUI($this->lng->txt("mep_default_height"), "default_height");
        $ni->setSuffix("px");
        $ni->setMinValue(0);
        $ni->setMaxLength(5);
        $ni->setSize(5);
        $ni->setInfo($this->lng->txt("mep_default_width_height_info"));
        $a_form->addItem($ni);

        // presentation
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $a_form->addItem($pres);

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();

        // additional features
        $feat = new ilFormSectionHeaderGUI();
        $feat->setTitle($this->lng->txt('obj_features'));
        $a_form->addItem($feat);

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            array(
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            )
        );
    }

    /**
     * @throws ilPermissionException
     */
    public function edit()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs_gui;

        $this->setSettingsSubTabs("settings");

        if (!$this->checkPermissionBool("write")) {
            throw new ilPermissionException($this->lng->txt("msg_no_perm_write"));
        }

        $ilTabs->activateTab("settings");

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values, true);
        }

        $this->addExternalEditFormCustom($form);

        $tpl->setContent($form->getHTML());
    }


    protected function getEditFormCustomValues(array &$a_values)
    {
        if ($this->getMediaPool()->getDefaultWidth() > 0) {
            $a_values["default_width"] = $this->object->getDefaultWidth();
        }
        if ($this->getMediaPool()->getDefaultHeight() > 0) {
            $a_values["default_height"] = $this->object->getDefaultHeight();
        }
    }

    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->object_service;

        $this->getMediaPool()->setDefaultWidth($a_form->getInput("default_width"));
        $this->object->setDefaultHeight($a_form->getInput("default_height"));

        // additional features
        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            array(
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            )
        );

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();
    }

    /**
     * list media objects
     */
    public function listMedia() : void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;

        $ilCtrl->setParameter($this, "mep_mode", "listMedia");

        $this->checkPermission("read");

        $ilTabs->setTabActive("content");
        $this->setContentSubTabs("content");
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilToolbar->addButton(
                $lng->txt("mep_create_mob"),
                $ilCtrl->getLinkTarget($this, "createMediaObject")
            );
            
            $mset = new ilSetting("mobs");
            if ($mset->get("mep_activate_pages")) {
                $ilToolbar->addButton(
                    $lng->txt("mep_create_content_snippet"),
                    $ilCtrl->getLinkTarget($this, "createMediaPoolPage")
                );
            }
    
            $ilToolbar->addButton(
                $lng->txt("mep_create_folder"),
                $ilCtrl->getLinkTarget($this, "createFolderForm")
            );

            $upload_factory = new ilImportDirectoryFactory();
            $media_upload = $upload_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_MOB);
            if ($media_upload->exists() && $this->rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID)) {
                $ilToolbar->addButton(
                    $lng->txt("mep_create_from_upload_dir"),
                    $ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles")
                );
            }

            $ilToolbar->addButton(
                $lng->txt("mep_bulk_upload"),
                $ilCtrl->getLinkTarget($this, "bulkUpload")
            );
        }

        $mep_table_gui = new ilMediaPoolTableGUI($this, "listMedia", $this->getMediaPool(), "mepitem_id");
        $tpl->setContent($mep_table_gui->getHTML());
    }

    protected function toggleExplorerNodeState() : void
    {
        $exp = new ilMediaPoolExplorerGUI($this, "listMedia", $this->getMediaPool());
        $exp->toggleExplorerNodeState();
    }

    /**
     * list all media objects
     */
    public function allMedia() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $ilCtrl->setParameter($this, "mep_mode", "allMedia");

        $this->checkPermission("read");
        $ilTabs->setTabActive("content");
        $this->setContentSubTabs("mep_all_mobs");
        
        $mep_table_gui = new ilMediaPoolTableGUI(
            $this,
            "allMedia",
            $this->getMediaPool(),
            "mepitem_id",
            ilMediaPoolTableGUI::IL_MEP_EDIT,
            true
        );
            
            
        if ($this->mep_request->getForceFilter() > 0) {
            $mep_table_gui->setTitleFilter(
                ilMediaPoolItem::lookupTitle($this->mep_request->getForceFilter())
            );

            // Read again
            $mep_table_gui = new ilMediaPoolTableGUI(
                $this,
                "allMedia",
                $this->getMediaPool(),
                "mepitem_id",
                ilMediaPoolTableGUI::IL_MEP_EDIT,
                true
            );
        }

        $tpl->setContent($mep_table_gui->getHTML());
    }
    
    /**
     * Apply filter
     */
    public function applyFilter() : void
    {
        $mtab = new ilMediaPoolTableGUI(
            $this,
            "allMedia",
            $this->getMediaPool(),
            "mepitem_id",
            ilMediaPoolTableGUI::IL_MEP_EDIT,
            true
        );
        $mtab->writeFilterToSession();
        $mtab->resetOffset();
        $this->allMedia();
    }

    public function resetFilter() : void
    {
        $mtab = new ilMediaPoolTableGUI(
            $this,
            "allMedia",
            $this->getMediaPool(),
            "mepitem_id",
            ilMediaPoolTableGUI::IL_MEP_EDIT,
            true
        );
        $mtab->resetFilter();
        $mtab->resetOffset();
        $this->allMedia();
    }

    /**
     * Get standard template
     */
    public function getTemplate() : void
    {
        $this->tpl->loadStandardTemplate();
    }


    /**
     * Get folder parent ID
     */
    public function getParentFolderId() : ?int
    {
        if ($this->mep_item_id == 0) {
            return null;
        }
        $par_id = $this->object->getPoolTree()->getParentId($this->mep_item_id);
        if ($par_id != $this->object->getPoolTree()->getRootId()) {
            return (int) $par_id;
        } else {
            return null;
        }
    }
    
    /**
     * show media object
     */
    protected function showMedia() : void
    {
        $this->checkPermission("read");
        $link_xml = "";
        $pg_frame = "";

        $item = new ilMediaPoolItem($this->mep_item_id);
        $mob_id = $item->getForeignId();

        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );


        ilObjMediaObjectGUI::includePresentationJS($this->tpl);
        $media_obj = new ilObjMediaObject($mob_id);


        $this->tpl->setVariable("TITLE", " - " . $media_obj->getTitle());

        $xml = "<dummy>";
        // todo: we get always the first alias now (problem if mob is used multiple
        // times in page)
        $xml .= $media_obj->getXML(IL_MODE_ALIAS);
        $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
        $xml .= $link_xml;
        $xml .= "</dummy>";

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        $wb_path = ilFileUtils::getWebspaceDir("output") . "/";

        $mode = ($this->ctrl->getCmd() != "showPreview")
            ? "fullscreen"
            : "media";
        $enlarge_path = ilUtil::getImagePath("enlarge.svg", false, "output");
        $fullscreen_link =
            $this->ctrl->getLinkTarget($this, "showFullscreen", "", false, false);
        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $this->requested_ref_id,'fullscreen_link' => $fullscreen_link,
            'ref_id' => $this->requested_ref_id, 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_free($xh);
        // unmask user html
        $this->tpl->setVariable("MEDIA_CONTENT", $output);
    }
    
    /**
     * Show page
     *
     * @param
     * @return
     */
    public function showPage() : void
    {
        //$tpl = new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());
        $tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Services/COPage");

        $tpl->addCss(ilUtil::getStyleSheetLocation());
        $tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        // get page object
        $page_gui = new ilMediaPoolPageGUI($this->mep_item_id);
        $page_gui->setTemplate($tpl);

        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $ret = $page_gui->showPage(true);

        //$tpl->setBodyClass("ilMediaPoolPagePreviewBody");
        $tpl->setVariable("MEDIA_CONTENT", "<div>" . $ret . "</div>");


        $tpl->printToStdout();
        exit;
    }
    

    /**
     * Show content snippet
     */
    public function showPreview() : void
    {
        $this->checkPermission("read");

        $item = new ilMediaPoolItem($this->mep_item_id);

        switch ($item->getType()) {
            case "mob":
                $this->showMedia();
                break;

            case "pg":
                $this->showPage();
                break;
        }
    }


    /**
     * show media fullscreen
     */
    public function showFullscreen() : void
    {
        $this->showMedia();
    }
    
    /**
     * confirm remove of mobs
     */
    public function confirmRemove() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("write");

        $ids = $this->mep_request->getItemIds();
        if (count($ids) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "");
        }
        
        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_remove_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelRemove");
        $cgui->setConfirm($this->lng->txt("confirm"), "remove");
            
        foreach ($ids as $obj_id) {
            $type = ilMediaPoolItem::lookupType($obj_id);
            $title = ilMediaPoolItem::lookupTitle($obj_id);
            
            // check whether page can be removed
            $add = "";
            if ($type == "pg") {
                $usages = ilPageContentUsage::getUsages("incl", $obj_id, false);
                if (count($usages) > 0) {
                    $this->main_tpl->setOnScreenMessage('failure', sprintf($lng->txt("mep_content_snippet_in_use"), $title), true);
                    $ilCtrl->redirect($this, "listMedia");
                } else {
                    // check whether the snippet is used in older versions of pages
                    $usages = ilPageContentUsage::getUsages("incl", $obj_id, true);
                    if (count($usages) > 0) {
                        $add = "<div class='small'>" . $lng->txt("mep_content_snippet_used_in_older_versions") . "</div>";
                    }
                }
            }
            
            $caption = ilUtil::getImageTagByType($type, $this->tpl->tplPath) .
                " " . $title . $add;
            
            $cgui->addItem("id[]", $obj_id, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }
    
    /**
     * paste from clipboard
     */
    public function openClipboard() : void
    {
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $ilCtrl->setParameterByClass(
            "ileditclipboardgui",
            "returnCommand",
            rawurlencode($ilCtrl->getLinkTarget(
                $this,
                "insertFromClipboard",
                "",
                false,
                false
            ))
        );
        $ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
    }
    

    /**
     * insert media object from clipboard
     */
    public function insertFromClipboard() : void
    {
        $this->checkPermission("write");

        $ids = ilEditClipboardGUI::_getSelectedIDs();
        $not_inserted = array();
        foreach ($ids as $id2) {
            $id = explode(":", $id2);
            $type = $id[0];
            $id = $id[1];

            if ($type == "mob") {		// media object
                if (ilObjMediaPool::isForeignIdInTree($this->object->getId(), $id)) {
                    $not_inserted[] = ilObject::_lookupTitle($id) . " [" .
                        $id . "]";
                } else {
                    $item = new ilMediaPoolItem();
                    $item->setType("mob");
                    $item->setForeignId($id);
                    $item->setTitle(ilObject::_lookupTitle($id));
                    $item->create();
                    if ($item->getId() > 0) {
                        $this->object->insertInTree($item->getId(), $this->mep_item_id);
                    }
                }
            }
            if ($type == "incl") {		// content snippet
                if (ilObjMediaPool::isItemIdInTree($this->object->getId(), $id)) {
                    $not_inserted[] = ilMediaPoolPage::lookupTitle($id) . " [" .
                        $id . "]";
                } else {
                    $original = new ilMediaPoolPage($id);

                    // copy the page into the pool
                    $item = new ilMediaPoolItem();
                    $item->setType("pg");
                    $item->setTitle(ilMediaPoolItem::lookupTitle($id));
                    $item->create();
                    if ($item->getId() > 0) {
                        $this->object->insertInTree($item->getId(), $this->mep_item_id);

                        // create page
                        $page = new ilMediaPoolPage();
                        $page->setId($item->getId());
                        $page->setParentId($this->object->getId());
                        $page->create(false);

                        // copy content
                        $original->copy($page->getId(), $page->getParentType(), $page->getParentId(), true);
                    }
                }
            }
        }
        if (count($not_inserted) > 0) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("mep_not_insert_already_exist") . "<br>" .
                implode("<br>", $not_inserted), true);
        }
        $this->ctrl->redirect($this, $this->mode);
    }


    /**
     * cancel deletion of media objects/folders
     */
    public function cancelRemove() : void
    {
        $this->ctrl->redirect($this, $this->mode);
    }

    public function remove() : void
    {
        $this->checkPermission("write");

        $ids = $this->mep_request->getItemIds();
        foreach ($ids as $obj_id) {
            $this->object->deleteChild($obj_id);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("cont_obj_removed"), true);
        $this->ctrl->redirect($this, $this->mode);
    }


    /**
     * copy media objects to clipboard
     */
    public function copyToClipboard() : void
    {
        $ilUser = $this->user;

        $this->checkPermission("write");

        $ids = $this->mep_request->getItemIds();
        if (count($ids) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, $this->mode);
        }

        foreach ($ids as $obj_id) {
            $type = ilMediaPoolItem::lookupType($obj_id);
            if ($type == "fold") {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("cont_cant_copy_folders"), true);
                $this->ctrl->redirect($this, $this->mode);
            }
        }
        foreach ($ids as $obj_id) {
            $fid = ilMediaPoolItem::lookupForeignId($obj_id);
            $type = ilMediaPoolItem::lookupType($obj_id);
            if ($type == "mob") {
                $ilUser->addObjectToClipboard($fid, "mob", "");
            }
            if ($type == "pg") {
                $ilUser->addObjectToClipboard($obj_id, "incl", "");
            }
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("copied_to_clipboard"), true);
        $this->ctrl->redirect($this, $this->mode);
    }

    /**
     * add locator items for media pool
     */
    public function addLocatorItems() : void
    {
        $ilLocator = $this->locator;
        $ilAccess = $this->access;
        
        if (!$this->getCreationMode() && $this->ctrl->getCmd() != "explorer") {
            $tree = $this->object->getTree();
            $obj_id = ($this->mep_item_id == 0)
                ? $tree->getRootId()
                : $this->mep_item_id;
            $path = $tree->getPathFull($obj_id);
            foreach ($path as $node) {
                if ($node["child"] == $tree->getRootId()) {
                    $this->ctrl->setParameter($this, "mepitem_id", "");
                    $link = "";
                    if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
                        $link = $this->ctrl->getLinkTarget($this, "listMedia");
                    } elseif ($ilAccess->checkAccess("visible", "", $this->object->getRefId())) {
                        $link = $this->ctrl->getLinkTarget($this, "infoScreen");
                    }
                    $title = $this->object->getTitle();
                    $this->ctrl->setParameter($this, "mepitem_id", $this->mep_item_id);
                    $ilLocator->addItem($title, $link, "", $this->requested_ref_id);
                }
            }
        }
    }
    
    ////
    //// FOLDER Handling
    ////
    
    public function createFolderForm() : void
    {
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $this->initFolderForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    public function editFolder() : void
    {
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $this->initFolderForm();
        $this->getFolderValues();
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Get current values for folder from
     */
    public function getFolderValues() : void
    {
        $values = array();
    
        $values["title"] = ilMediaPoolItem::lookupTitle($this->mep_item_id);
    
        $this->form->setValuesByArray($values);
    }

    /**
     * Save folder form
     */
    public function saveFolder() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $this->initFolderForm("create");
        if ($this->form->checkInput()) {
            if ($this->object->createFolder($this->form->getInput("title"), $this->mep_item_id)) {
                $this->main_tpl->setOnScreenMessage('success', $lng->txt("mep_folder_created"), true);
            }
            $ilCtrl->redirect($this, "listMedia");
        }
        
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }
    
    public function updateFolder() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $this->initFolderForm("edit");
        if ($this->form->checkInput()) {
            $item = new ilMediaPoolItem($this->mep_item_id);
            $item->setTitle($this->form->getInput("title"));
            $item->update();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->setParameter(
                $this,
                "mepitem_id",
                $this->object->getTree()->getParentId($this->mep_item_id)
            );
            $ilCtrl->redirect($this, "listMedia");
        }
        
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * @param string $a_mode "edit" | "create"
     * @throws ilCtrlException
     */
    public function initFolderForm(string $a_mode = "edit") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->form = new ilPropertyFormGUI();
        
        // desc
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(128);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveFolder", $lng->txt("save"));
            $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("mep_new_folder"));
        } else {
            $this->form->addCommandButton("updateFolder", $lng->txt("save"));
            $this->form->addCommandButton("cancelFolderUpdate", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("mep_edit_folder"));
        }
                    
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function cancelFolderUpdate() : void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter(
            $this,
            "mepitem_id",
            $this->object->getTree()->getParentId($this->mep_item_id)
        );
        $ilCtrl->redirect($this, "listMedia");
    }

    public function cancelSave() : void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "listMedia");
    }
    
    ////
    //// CONTENT SNIPPETS Handling
    ////

    /**
     * Create new content snippet
     */
    public function createMediaPoolPage() : void
    {
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $this->initMediaPoolPageForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * @throws ilObjectException
     */
    public function editMediaPoolPage() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $this->checkPermission("write");

        $ilTabs->clearTargets();
        
        $mep_page_gui = new ilMediaPoolPageGUI(
            $this->mep_item_id,
            $this->mep_request->getOldNr()
        );
        $mep_page_gui->getTabs();

        $this->setMediaPoolPageTabs();

        $this->initMediaPoolPageForm("edit");
        $this->getMediaPoolPageValues();
        $tpl->setContent($this->form->getHTML());
    }

    public function saveMediaPoolPage() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $this->initMediaPoolPageForm("create");
        if ($this->form->checkInput()) {
            // create media pool item
            $item = new ilMediaPoolItem();
            $item->setTitle($this->form->getInput("title"));
            $item->setType("pg");
            $item->create();
            
            if ($item->getId() > 0) {
                // put in tree
                $tree = $this->object->getTree();
                $parent = $this->mep_item_id > 0
                    ? $this->mep_item_id
                    : $tree->getRootId();
                $this->object->insertInTree($item->getId(), $parent);
                
                // create page
                $page = new ilMediaPoolPage();
                $page->setId($item->getId());
                $page->setParentId($this->object->getId());
                $page->create(false);
                
                $ilCtrl->setParameterByClass("ilmediapoolpagegui", "mepitem_id", $item->getId());
                $ilCtrl->redirectByClass("ilmediapoolpagegui", "edit");
            }
            $ilCtrl->redirect($this, "listMedia");
        }
        
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    public function updateMediaPoolPage() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $this->initMediaPoolPageForm("edit");
        if ($this->form->checkInput()) {
            $item = new ilMediaPoolItem($this->mep_item_id);
            $item->setTitle($this->form->getInput("title"));
            $item->update();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editMediaPoolPage");
        }
        
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * @param string $a_mode "edit" | "create"
     * @throws ilCtrlException
     */
    public function initMediaPoolPageForm(string $a_mode = "edit") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->form = new ilPropertyFormGUI();
    
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(128);
        $ti->setRequired(true);
        $this->form->addItem($ti);
    
        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveMediaPoolPage", $lng->txt("save"));
            $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("mep_new_content_snippet"));
        } else {
            $this->form->addCommandButton("updateMediaPoolPage", $lng->txt("save"));
            $this->form->setTitle($lng->txt("mep_edit_content_snippet"));
        }
                    
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for media pool page from
     */
    public function getMediaPoolPageValues() : void
    {
        $values = array();
    
        $values["title"] = ilMediaPoolItem::lookupTitle($this->mep_item_id);
    
        $this->form->setValuesByArray($values);
    }

    public function setMediaPoolPageTabs() : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
    
        $ilTabs->addTarget(
            "cont_usage",
            $ilCtrl->getLinkTarget($this, "showMediaPoolPageUsages"),
            array("showMediaPoolPageUsages", "showAllMediaPoolPageUsages"),
            get_class($this)
        );
        $ilTabs->addTarget(
            "settings",
            $ilCtrl->getLinkTarget($this, "editMediaPoolPage"),
            "editMediaPoolPage",
            get_class($this)
        );
        $ilCtrl->setParameter($this, "mepitem_id", $this->object->getPoolTree()->getParentId($this->mep_item_id));
        $ilTabs->setBackTarget($lng->txt("mep_folder"), $ilCtrl->getLinkTarget($this, "listMedia"));
        $ilCtrl->setParameter($this, "mepitem_id", $this->mep_item_id);
    }

    /**
     * List usages of the content snippet
     */
    public function showAllMediaPoolPageUsages() : void
    {
        $this->showMediaPoolPageUsages(true);
    }

    public function showMediaPoolPageUsages(
        bool $a_all = false
    ) : void {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $ilTabs->clearTargets();
        
        $ilTabs->addSubTab(
            "current_usages",
            $lng->txt("cont_current_usages"),
            $ilCtrl->getLinkTarget($this, "showMediaPoolPageUsages")
        );
        
        $ilTabs->addSubTab(
            "all_usages",
            $lng->txt("cont_all_usages"),
            $ilCtrl->getLinkTarget($this, "showAllMediaPoolPageUsages")
        );
        
        if ($a_all) {
            $ilTabs->activateSubTab("all_usages");
            $cmd = "showAllMediaPoolPageUsages";
        } else {
            $ilTabs->activateSubTab("current_usages");
            $cmd = "showMediaPoolPageUsages";
        }

        
        $mep_page_gui = new ilMediaPoolPageGUI(
            $this->mep_item_id,
            $this->mep_request->getOldNr()
        );
        $mep_page_gui->getTabs();

        $this->setMediaPoolPageTabs();
        
        $page = new ilMediaPoolPage($this->mep_item_id);

        $table = new ilMediaPoolPageUsagesTableGUI($this, $cmd, $page, $a_all);

        $tpl->setContent($table->getHTML());
    }
    
    
    ////
    //// OTHER Functions...
    ////

    /**
     * Set sub tabs for content tab
     */
    public function setContentSubTabs(
        string $a_active
    ) : void {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTab("content", $this->lng->txt("objs_fold"), $this->ctrl->getLinkTarget($this, ""));

        $ilCtrl->setParameter($this, "mepitem_id", "");
        $ilTabs->addSubTab("mep_all_mobs", $this->lng->txt("mep_all_mobs"), $this->ctrl->getLinkTarget($this, "allMedia"));
        $ilCtrl->setParameter($this, "mepitem_id", $this->mep_item_id);

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilTabs->addSubTab(
                "srt_files",
                $this->lng->txt("mep_media_subtitles"),
                $ilCtrl->getLinkTargetByClass("ilmobmultisrtuploadgui", "")
            );
        }

        $ilTabs->activateSubTab($a_active);
    }

    protected function setTabs() : void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("mep");
        
        if ($ilAccess->checkAccess('read', '', $this->ref_id) ||
            $ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilTabs->addTab("content", $this->lng->txt("mep_content"), $this->ctrl->getLinkTarget($this, ""));
        }

        // info tab
        if ($ilAccess->checkAccess('visible', '', $this->ref_id) ||
            $ilAccess->checkAccess('read', '', $this->ref_id) ||
            $ilAccess->checkAccess('write', '', $this->ref_id)) {
            $force_active = $this->ctrl->getNextClass() == "ilinfoscreengui"
                || strtolower($this->ctrl->getCmdClass()) == "ilnotegui";
            $ilTabs->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    array("ilobjmediapoolgui", "ilinfoscreengui"),
                    "showSummary"
                ),
                array("showSummary", "infoScreen"),
                "",
                "",
                $force_active
            );
        }

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                array("", "ilobjmediapoolgui")
            );
        }
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "clipboard",
                $this->ctrl->getLinkTarget($this, "openClipboard"),
                "view",
                "ileditclipboardgui"
            );
        }

        // properties
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            // meta data
            $mdgui = new ilObjectMetaDataGUI($this->object, "mob");
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $ilTabs->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilobjectmetadatagui"
                );
            }
        }

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "export",
                $this->ctrl->getLinkTargetByClass("ilexportgui", ""),
                "",
                "ilexportgui"
            );

            $ilTabs->addTarget(
                "import",
                $this->ctrl->getLinkTargetByClass("ilmediapoolimportgui", ""),
                "",
                "ilmediapoolimportgui"
            );
        }
        
        if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    public function setSettingsSubTabs(
        string $a_active
    ) : void {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addSubTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );

            $mset = new ilSetting("mobs");
            if ($mset->get("mep_activate_pages")) {
                $ilTabs->addSubTabTarget(
                    "obj_multilinguality",
                    $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "")
                );
            }
        }

        $ilTabs->setSubTabActive($a_active);
    }


    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        $subitem_id = "";
        $targets = explode('_', $a_target);
        $ref_id = $targets[0];
        if (count((array) $targets) > 1) {
            $subitem_id = $targets[1];
        }

        $ctrl->setParameterByClass("ilMediaPoolPresentationGUI", "ref_id", $ref_id);
        if ($ilAccess->checkAccess("read", "", $ref_id)) {
            $ctrl->setParameterByClass("ilMediaPoolPresentationGUI", "mepitem_id", $subitem_id);
            $ctrl->redirectByClass("ilMediaPoolPresentationGUI", "");
        } elseif ($ilAccess->checkAccess("visible", "", $ref_id)) {
            $ctrl->redirectByClass("ilMediaPoolPresentationGUI", "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read"));
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreenObject() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    /**
     * show information screen
     * @throws ilCtrlException
     * @throws ilPermissionException
     */
    public function infoScreen() : void
    {
        $this->tabs->activateTab("info_short");
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id) &&
            !$ilAccess->checkAccess("read", "", $this->ref_id) &&
            !$ilAccess->checkAccess("write", "", $this->ref_id)) {
            throw new ilPermissionException($this->lng->txt("msg_no_perm_read"));
        }
        
        if ($this->ctrl->getCmd() == "infoScreen") {
            $this->ctrl->setCmd("showSummary");
            $this->ctrl->setCmdClass("ilinfoscreengui");
        }

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();


        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        // forward the command
        $this->ctrl->forwardCommand($info);
    }


    ////
    //// Upload directory handling
    ////

    /**
     * Select files from upload directory
     */
    public function selectUploadDirFiles(
        ?array $a_files = null
    ) : void {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;


        if (!$a_files) {
            $a_files = $this->mep_request->getFiles();
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listMedia")
        );

        $this->checkPermission("write");

        if ($this->rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID)) {

            // action type
            $options = array(
                "rename" => $lng->txt("mep_up_dir_move"),
                "copy" => $lng->txt("mep_up_dir_copy"),
                );
            $si = new ilSelectInputGUI("", "action");
            $si->setOptions($options);
            $ilToolbar->addInputItem($si);
            $ilToolbar->setCloseFormTag(false);
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->setFormName("mep_up_form");

            $tab = new ilUploadDirFilesTableGUI(
                $this,
                "selectUploadDirFiles",
                $a_files
            );
            $tab->setFormName("mep_up_form");
            $tpl->setContent($tab->getHTML());
        }
    }

    /**
     * Create media object from upload directory
     */
    public function createMediaFromUploadDir() : void
    {
        $this->checkPermission("write");

        $import_directory_factory = new ilImportDirectoryFactory();
        $mob_import_directory = $import_directory_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_MOB);
        $upload_dir = $mob_import_directory->getAbsolutePath();

        $files = $this->mep_request->getFiles();
        if ($this->rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID)) {
            foreach ($files as $f) {
                $f = str_replace("..", "", $f);
                $fullpath = $upload_dir . "/" . $f;
                $mob = new ilObjMediaObject();
                $mob->setTitle(basename($fullpath));
                $mob->setDescription("");
                $mob->create();

                // determine and create mob directory, move uploaded file to directory
                //$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob->getId();
                $mob->createDirectory();
                $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());

                $media_item = new ilMediaItem();
                $mob->addMediaItem($media_item);
                $media_item->setPurpose("Standard");

                $file = $mob_dir . "/" . basename($fullpath);

                // virus handling
                $vir = ilVirusScanner::virusHandling($fullpath, basename($fullpath));
                if (!$vir[0]) {
                    $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("file_is_infected") . "<br />" . $vir[1], true);
                    ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&cmd=listMedia&ref_id=" .
                        $this->requested_ref_id . "&mepitem_id=" . $this->mep_item_id);
                }

                switch ($this->mep_request->getFileAction()) {
                    case "rename":
                        rename($fullpath, $file);
                        break;

                    case "copy":
                        copy($fullpath, $file);
                        break;
                }

                // get mime type
                $format = ilObjMediaObject::getMimeType($file);
                $location = basename($fullpath);

                // set real meta and object data
                $media_item->setFormat($format);
                $media_item->setLocation($location);
                $media_item->setLocationType("LocalFile");

                $mob->setDescription($format);

                // determine width and height of known image types
                $wh = ilObjMediaObject::_determineWidthHeight(
                    $format,
                    "File",
                    $mob_dir . "/" . $location,
                    $media_item->getLocation(),
                    true,
                    true,
                    "",
                    ""
                );
                $media_item->setWidth($wh["width"]);
                $media_item->setHeight($wh["height"]);

                $media_item->setHAlign("Left");
                ilFileUtils::renameExecutables($mob_dir);
                $mob->update();


                // put it into current folder
                $mep_item = new ilMediaPoolItem();
                $mep_item->setTitle($mob->getTitle());
                $mep_item->setType("mob");
                $mep_item->setForeignId($mob->getId());
                $mep_item->create();

                $tree = $this->object->getTree();
                $parent = ($this->mep_item_id == 0)
                    ? $tree->getRootId()
                    : $this->mep_item_id;
                $tree->insertNode($mep_item->getId(), $parent);
            }
        }
        ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&cmd=listMedia&ref_id=" .
            $this->requested_ref_id . "&mepitem_id=" . $this->mep_item_id);
    }

    /**
     * Get preview modal html
     */
    public static function getPreviewModalHTML(
        int $a_mpool_ref_id,
        ilGlobalTemplateInterface $a_tpl
    ) : string {
        global $DIC;

        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();

        ilObjMediaObjectGUI::includePresentationJS($a_tpl);

        $tpl->addJavaScript("./Modules/MediaPool/js/ilMediaPool.js");

        $ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", "");
        $ilCtrl->setParameterByClass("ilobjmediapoolgui", "ref_id", $a_mpool_ref_id);
        $tpl->addOnloadCode("il.MediaPool.setPreviewUrl('" . $ilCtrl->getLinkTargetByClass(array("ilmediapoolpresentationgui", "ilobjmediapoolgui"), "showPreview", "", false, false) . "');");
        $ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $request->getItemId());
        $ilCtrl->setParameterByClass(
            "ilobjmediapoolgui",
            "ref_id",
            $request->getRefId()
        );

        $modal = ilModalGUI::getInstance();
        $modal->setHeading($lng->txt("preview"));
        $modal->setId("ilMepPreview");
        $modal->setType(ilModalGUI::TYPE_LARGE);
        $modal->setBody("<iframe id='ilMepPreviewContent'></iframe>");

        return $modal->getHTML();
    }

    public function export() : void
    {
        $ot = ilObjectTranslation::getInstance($this->object->getId());
        $opt = "";
        if ($ot->getContentActivated()) {
            $format = explode("_", $this->mep_request->getExportFormat());
            $opt = ilUtil::stripSlashes($format[1]);
        }

        $this->object->exportXML($opt);
    }

    //
    // BULK UPLOAD
    //

    protected function bulkUpload() : void
    {
        $this->checkPermission("write");

        $main_tpl = $this->main_tpl;

        $form = $this->initBulkUploadForm();
        $main_tpl->setContent($form->getHTML());
    }

    /**
     * Init bulk upload form
     */
    public function initBulkUploadForm() : ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();

        $form->setFormAction($ctrl->getFormAction($this));
        $form->setPreventDoubleSubmission(false);

        $item = new ilFileStandardDropzoneInputGUI(
            'cancel',
            $lng->txt("mep_media_files"),
            'media_files'
        );
        $item->setUploadUrl($ctrl->getLinkTarget($this, "performBulkUpload", "", true, true));
        $item->setMaxFiles(20);
        $form->addItem($item);

        $form->addCommandButton("performBulkUpload", $lng->txt("upload"));

        $form->setTitle($lng->txt("mep_bulk_upload"));

        return $form;
    }

    /**
     * Save bulk upload form
     */
    public function performBulkUpload() : void
    {
        $this->checkPermission("write");

        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;
        $upload = $this->upload;
        $log = $this->mep_log;

        $form = $this->initBulkUploadForm();
        if ($form->checkInput()) {
            // Check if this is a request to upload a file
            $log->debug("checking for uploads...");
            if ($upload->hasUploads()) {
                $log->debug("has upload...");
                try {
                    $upload->process();
                    $log->debug("nr of results: " . count($upload->getResults()));
                    foreach ($upload->getResults() as $result) {
                        $title = $result->getName();

                        $mob = new ilObjMediaObject();
                        $mob->setTitle($title);
                        $mob->setDescription("");
                        $mob->create();

                        $mob->createDirectory();
                        $media_item = new ilMediaItem();
                        $mob->addMediaItem($media_item);
                        $media_item->setPurpose("Standard");

                        $mob_dir = ilObjMediaObject::_getRelativeDirectory($mob->getId());
                        $file_name = ilObjMediaObject::fixFilename($title);
                        $file = $mob_dir . "/" . $file_name;

                        $upload->moveOneFileTo(
                            $result,
                            $mob_dir,
                            Location::WEB,
                            $file_name,
                            true
                        );

                        $mep_item = new ilMediaPoolItem();
                        $mep_item->setTitle($title);
                        $mep_item->setType("mob");
                        $mep_item->setForeignId($mob->getId());
                        $mep_item->create();

                        $tree = $this->object->getTree();
                        $parent = ($this->mep_item_id == 0)
                            ? $tree->getRootId()
                            : $this->mep_item_id;
                        $tree->insertNode($mep_item->getId(), $parent);

                        // get mime type
                        $format = ilObjMediaObject::getMimeType($file);
                        $location = $file_name;

                        // set real meta and object data
                        $media_item->setFormat($format);
                        $media_item->setLocation($location);
                        $media_item->setLocationType("LocalFile");
                        $media_item->setUploadHash($this->mep_request->getUploadHash());
                        $mob->update();
                    }
                } catch (Exception $e) {
                    $log->debug("Got exception: " . $e->getMessage());
                    echo json_encode(array( 'success' => false, 'message' => $e->getMessage()));
                }
                $log->debug("end of 'has_uploads'");
            }
            $log->debug("has no upload...");

            $log->debug("calling redirect... (" . $this->mep_request->getUploadHash() . ")");
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ctrl->setParameter($this, "mep_hash", $this->mep_request->getUploadHash());
            $ctrl->redirect($this, "editTitlesAndDescriptions");
        }

        $form->setValuesByPost();
        $main_tpl->setContent($form->getHTML());
    }

    protected function editTitlesAndDescriptions() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("write");
        $ctrl->saveParameter($this, "mep_hash");

        $main_tpl = $this->main_tpl;

        $media_items = ilMediaItem::getMediaItemsForUploadHash(
            $this->mep_request->getUploadHash()
        );

        $tb = new ilToolbarGUI();
        $tb->setFormAction($ctrl->getFormAction($this));
        $tb->addFormButton($lng->txt("save"), "saveTitlesAndDescriptions");
        $tb->setOpenFormTag(true);
        $tb->setCloseFormTag(false);
        $tb->setId("tb_top");

        $html = $tb->getHTML();
        foreach ($media_items as $mi) {
            $acc = new ilAccordionGUI();
            $acc->setBehaviour(ilAccordionGUI::ALL_CLOSED);
            $acc->setId("acc_" . $mi["mob_id"]);

            $mob = new ilObjMediaObject($mi["mob_id"]);
            $form = $this->initMediaBulkForm($mi["mob_id"], $mob->getTitle());
            $acc->addItem($mob->getTitle(), $form->getHTML());

            $html .= $acc->getHTML();
        }

        $html .= $tb->getHTML();
        $tb->setOpenFormTag(false);
        $tb->setCloseFormTag(true);
        $tb->setId("tb_bottom");

        $main_tpl->setContent($html);
    }

    /**
     * Init media bulk form.
     */
    public function initMediaBulkForm(
        int $a_id,
        string $a_title
    ) : ilPropertyFormGUI {
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title_" . $a_id);
        $ti->setValue($a_title);
        $form->addItem($ti);

        // description
        $ti = new ilTextAreaInputGUI($lng->txt("description"), "description_" . $a_id);
        $form->addItem($ti);

        return $form;
    }

    protected function saveTitlesAndDescriptions() : void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        
        $this->checkPermission("write");

        $media_items = ilMediaItem::getMediaItemsForUploadHash(
            $this->mep_request->getUploadHash()
        );

        foreach ($media_items as $mi) {
            $mob = new ilObjMediaObject($mi["mob_id"]);
            $form = $this->initMediaBulkForm($mi["mob_id"], $mob->getTitle());
            $form->checkInput();
            $title = $form->getInput("title_" . $mi["mob_id"]);
            $desc = $form->getInput("description_" . $mi["mob_id"]);
            if (trim($title) != "") {
                $mob->setTitle($title);
            }
            $mob->setDescription($desc);
            $mob->update();
        }
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, "listMedia");
    }
}
