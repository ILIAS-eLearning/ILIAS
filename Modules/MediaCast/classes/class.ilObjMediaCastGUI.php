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

use ILIAS\MediaCast\StandardGUIRequest;
use ILIAS\FileUpload\MimeType;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjMediaCastGUI: ilPermissionGUI, ilInfoScreenGUI, ilExportGUI
 * @ilCtrl_Calls ilObjMediaCastGUI: ilCommonActionDispatcherGUI, ilMediaCreationGUI
 * @ilCtrl_Calls ilObjMediaCastGUI: ilLearningProgressGUI, ilObjectCopyGUI, McstImageGalleryGUI, McstPodcastGUI
 * @ilCtrl_IsCalledBy ilObjMediaCastGUI: ilRepositoryGUI, ilAdministrationGUI
 */
class ilObjMediaCastGUI extends ilObjectGUI
{
    protected ilPropertyFormGUI $form_gui;
    protected ilNewsItem $mcst_item;
    protected StandardGUIRequest $mc_request;
    protected ilTabsGUI $tabs;
    protected ilLogger $log;
    protected ilHelpGUI $help;
    private array $additionalPurposes = [];
    private array $purposeSuffixes = [];
    private array $mimeTypes = [];
        
    /**
     * @param mixed $a_data
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->log = $DIC["ilLog"];
        $this->help = $DIC["ilHelp"];
        $this->locator = $DIC["ilLocator"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->mc_request = $DIC->mediaCast()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->type = "mcst";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $lng->loadLanguageModule("mcst");
        $lng->loadLanguageModule("news");
        $lng->loadLanguageModule("rep");
        
        $ilCtrl->saveParameter($this, "item_id");
        
        $settings = ilMediaCastSettings::_getInstance();
        $this->purposeSuffixes = $settings->getPurposeSuffixes();
        $this->mimeTypes = array();
        $mime_types = $settings->getMimeTypes();
        foreach ($mime_types as $mt) {
            $this->mimeTypes[$mt] = $mt;
        }
        
        foreach (MimeType::getExt2MimeMap() as $mt) {
//            $this->mimeTypes[$mt] = $mt;
        }
        asort($this->mimeTypes);
    }
    
    public function executeCommand() : void
    {
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
  
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
  
        switch ($next_class) {
            case "ilmediacreationgui":
                $this->ctrl->setReturn($this, "listItems");
                $ilTabs->activateTab("content");
                $this->addContentSubTabs("manage");
                $med_type = [];
                switch ($this->object->getViewMode()) {
                    case ilObjMediaCast::VIEW_VCAST:
                        $med_type = [ilMediaCreationGUI::TYPE_VIDEO];
                        break;
                    case ilObjMediaCast::VIEW_IMG_GALLERY:
                        $med_type = [ilMediaCreationGUI::TYPE_IMAGE];
                        break;
                    case ilObjMediaCast::VIEW_PODCAST:
                        $med_type = [ilMediaCreationGUI::TYPE_AUDIO];
                        break;
                }
                $creation = new ilMediaCreationGUI($med_type, function ($mob_id) {
                    $this->afterUpload($mob_id);
                }, function ($mob_id, $long_desc) {
                    $this->afterUrlSaving($mob_id, $long_desc);
                }, function ($mob_ids) {
                    $this->afterPoolInsert($mob_ids);
                }, function ($mob_id) {
                    $this->finishSingleUpload($mob_id);
                }, function ($mob_id) {
                    $this->onMobUpdate($mob_id);
                });
                $creation->setAllSuffixes($this->purposeSuffixes["Standard"]);
                $creation->setAllMimeTypes($this->mimeTypes);
                $this->ctrl->forwardCommand($creation);
                break;

            case "ilinfoscreengui":
                if (!$this->checkPermissionBool("read")) {
                    $this->checkPermission("visible");
                }
                $this->infoScreen();	// forwards command
                break;

            case "ilexportgui":
//				$this->prepareOutput();
                $ilTabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
                break;

            case 'ilpermissiongui':
                $ilTabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('mcst');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "illearningprogressgui":
                $ilTabs->activateTab('learning_progress');
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $this->mc_request->getUserId() ?: $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;

            case "mcstimagegallerygui":
                $view = new \McstImageGalleryGUI($this->object, $this->tpl);
                $this->ctrl->forwardCommand($view);
                break;

            default:
                if (!$cmd) {
                    $cmd = "infoScreen";
                }
                $cmd .= "Object";
                if ($cmd != "infoScreenObject") {
                    $this->checkPermission("read");
                } else {
                    $this->checkPermission("visible");
                }
                $this->$cmd();
    
            break;
        }
        $this->addHeaderAction();
    }

    protected function initCreationForms(string $new_type) : array
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($new_type),
                self::CFORM_IMPORT => $this->initImportForm($new_type),
                self::CFORM_CLONE => $this->fillCloneTemplate(null, $new_type));

        return $forms;
    }

    protected function afterSave(ilObject $new_object) : void
    {
        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        ilUtil::redirect("ilias.php?baseClass=ilMediaCastHandlerGUI&ref_id=" . $new_object->getRefId() . "&cmd=editSettings");
    }

    public function listItemsObject(bool $a_presentation_mode = false) : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilToolbar = $this->toolbar;
        
        $this->checkPermission("read");
        
        if ($a_presentation_mode) {
            $this->addContentSubTabs("content");
        } else {
            $this->addContentSubTabs("manage");
        }
        
        $med_items = $this->object->getSortedItemsArray();

        if ($a_presentation_mode) {
            $table_gui = new ilMediaCastTableGUI($this, "showContent", false, true);
        } else {
            $table_gui = new ilMediaCastTableGUI($this, "listItems");
        }

        $table_gui->setData($med_items);
        
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id) && !$a_presentation_mode) {
            if (in_array($this->object->getViewMode(), [
                ilObjMediaCast::VIEW_VCAST,
                ilObjMediaCast::VIEW_IMG_GALLERY,
                ilObjMediaCast::VIEW_PODCAST
            ], true)) {
                $ilToolbar->addButton($lng->txt("add"), $this->ctrl->getLinkTargetByClass("ilMediaCreationGUI", ""));
            } else {
                $ilToolbar->addButton($lng->txt("add"), $this->ctrl->getLinkTarget($this, "addCastItem"));
            }

            $table_gui->addMultiCommand("confirmDeletionItems", $lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("item_id");
        }

        $feed_icon_html = $this->getFeedIconsHTML();
        if ($feed_icon_html !== "") {
            $table_gui->setHeaderHTML($feed_icon_html);
        }
        
        $tpl->setContent($table_gui->getHTML());
    }
    
    public function getFeedIconsHTML() : string
    {
        $lng = $this->lng;
        $row1 = "";
        $row2 = "";

        $html = "";
        
        $public_feed = ilBlockSetting::_lookup(
            "news",
            "public_feed",
            0,
            $this->object->getId()
        );
            
        // rss icon/link
        if ($public_feed) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                // create dummy object in db (we need an id)
                $items = $this->object->getItemsArray();
                foreach (ilObjMediaCast::$purposes as $purpose) {
                    foreach ($items as  $id => $item) {
                        $mob = new ilObjMediaObject($item["mob_id"]);
                        $mob->read();
                        if ($mob->hasPurposeItem($purpose)) {
                            if ($html == "") {
                                $html = " ";
                            }
                            $url = ILIAS_HTTP_PATH . "/feed.php?client_id=" . rawurlencode(CLIENT_ID) . "&" . "ref_id=" . $this->requested_ref_id . "&purpose=$purpose";
                            $title = $lng->txt("news_feed_url");

                            switch (strtolower($purpose)) {
                                case "audioportable":
                                    $type1 = ilRSSButtonGUI::ICON_RSS_AUDIO;
                                    $type2 = ilRSSButtonGUI::ICON_ITUNES_AUDIO;
                                    break;

                                case "videoportable":
                                    $type1 = ilRSSButtonGUI::ICON_RSS_VIDEO;
                                    $type2 = ilRSSButtonGUI::ICON_ITUNES_VIDEO;
                                    break;

                                default:
                                    $type1 = ilRSSButtonGUI::ICON_RSS;
                                    $type2 = ilRSSButtonGUI::ICON_ITUNES;
                                    break;
                            }
                            $row1 .= "&nbsp;" . ilRSSButtonGUI::get($type1, $url);
                            if ($this->object->getPublicFiles()) {
                                $url = preg_replace("/https?/i", "itpc", $url);
                                $title = $lng->txt("news_feed_url");

                                $row2 .= "&nbsp;" . ilRSSButtonGUI::get($type2, $url);
                            }
                            break;
                        }
                    }
                }
                if ($html != "") {
                    $html .= $row1;
                    if ($row2 != "") {
                        $html .= $row2;
                    }
                }
            }
        }
        return $html;
    }
    
    /**
     * Add media cast item
     */
    public function addCastItemObject() : void
    {
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $this->initAddCastItemForm();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function editCastItemObject() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");
        
        // conversion toolbar
        if (ilFFmpeg::enabled()) {
            $this->mcst_item = new ilNewsItem(
                $this->mc_request->getItemId()
            );
            $mob = new ilObjMediaObject($this->mcst_item->getMobId());

            $conv_cnt = 0;
            // we had other purposes as source as well, but
            // currently only "Standard" is implemented in the convertFile method
            $p = "Standard";
            $med = $mob->getMediaItem($p);
            if (is_object($med)) {
                if (ilFFmpeg::supportsImageExtraction($med->getFormat())) {
                    // second
                    $ni = new ilTextInputGUI($this->lng->txt("mcst_second"), "sec");
                    $ni->setMaxLength(4);
                    $ni->setSize(4);
                    $ni->setValue(1);
                    $ilToolbar->addInputItem($ni, true);

                    $ilToolbar->addFormButton($this->lng->txt("mcst_extract_preview_image"), "extractPreviewImage");
                    $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
                }
            }
        }
        
        $this->initAddCastItemForm("edit");
        $this->getCastItemValues();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function finishSingleUpload(int $mob_id) : void
    {
        foreach ($this->object->getSortedItemsArray() as $item) {
            if ($mob_id == $item["mob_id"]) {
                $this->ctrl->setParameter($this, "item_id", $item["id"]);
                $this->ctrl->redirect($this, "editCastItem");
            }
        }
        $this->ctrl->redirect($this, "listItems");
    }

    protected function onMobUpdate(int $mob_id) : void
    {
        foreach ($this->object->getSortedItemsArray() as $item) {
            if ($mob_id == $item["mob_id"]) {
                $mob = new ilObjMediaObject($item["mob_id"]);
                $mc_item = new ilNewsItem($item["id"]);
                $mc_item->setTitle($mob->getTitle());
                $mc_item->setContent($mob->getLongDescription());
                $mc_item->update();
            }
        }
    }

    public function initAddCastItemForm(string $a_mode = "create") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("write");
        $ilTabs->activateTab("edit_content");
        
        $lng->loadLanguageModule("mcst");
        
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setMultipart(true);
        
        // Property Title
        $text_input = new ilTextInputGUI($lng->txt("title"), "title");
        $text_input->setMaxLength(200);
        $this->form_gui->addItem($text_input);
        
        // Property Content
        $text_area = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $text_area->setRequired(false);
        $this->form_gui->addItem($text_area);
        
        // Property Visibility
        if ($enable_internal_rss) {
            $radio_group = new ilRadioGroupInputGUI($lng->txt("access_scope"), "visibility");
            $radio_option = new ilRadioOption($lng->txt("access_users"), "users");
            $radio_group->addOption($radio_option);
            $radio_option = new ilRadioOption($lng->txt("access_public"), "public");
            $radio_group->addOption($radio_option);
            $radio_group->setInfo($lng->txt("mcst_visibility_info"));
            $radio_group->setRequired(true);
            $radio_group->setValue($this->object->getDefaultAccess() == 0 ? "users" : "public");
            $this->form_gui->addItem($radio_group);
        }
        
        // Duration
        $dur = new ilDurationInputGUI($lng->txt("mcst_duration"), "duration");
        $dur->setInfo($lng->txt("mcst_duration_info"));
        $dur->setShowDays(false);
        $dur->setShowHours(true);
        $dur->setShowSeconds(true);
        $this->form_gui->addItem($dur);
        
        foreach (ilObjMediaCast::$purposes as $purpose) {
            if ($purpose == "VideoAlternative" &&
                $a_mode == "create") {
                continue;
            }
            
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($lng->txt("mcst_" . strtolower($purpose) . "_title"));
            $this->form_gui->addItem($section);
            if ($a_mode != "create") {
                $value = new ilHiddenInputGUI("value_" . $purpose);
                $label = new ilNonEditableValueGUI($lng->txt("value"));
                $label->setPostVar("label_value_" . $purpose);
                $label->setInfo($lng->txt("mcst_current_value_info"));
                $this->form_gui->addItem($label);
                $this->form_gui->addItem($value);
            }
            $file = new ilFileInputGUI($lng->txt("file"), "file_" . $purpose);
            $file->setSuffixes($this->purposeSuffixes[$purpose]);
            $this->form_gui->addItem($file);
            $text_input = new ilRegExpInputGUI($lng->txt("url"), "url_" . $purpose);
            $text_input->setPattern("/https?\:\/\/.+/i");
            $text_input->setInfo($lng->txt("mcst_reference_info"));
            $this->form_gui->addItem($text_input);
            if ($purpose != "Standard") {
                $clearCheckBox = new ilCheckboxInputGUI();
                $clearCheckBox->setPostVar("delete_" . $purpose);
                $clearCheckBox->setTitle($lng->txt("mcst_clear_purpose_title"));
                $this->form_gui->addItem($clearCheckBox);
            } else {

                //
                $ne = new ilNonEditableValueGUI($lng->txt("mcst_mimetype"), "mimetype_" . $purpose);
                $this->form_gui->addItem($ne);

                // mime type selection
                /*
                $mimeTypeSelection = new ilSelectInputGUI();
                $mimeTypeSelection->setPostVar("mimetype_" . $purpose);
                $mimeTypeSelection->setTitle($lng->txt("mcst_mimetype"));
                $mimeTypeSelection->setInfo($lng->txt("mcst_mimetype_info"));
                $options = array("" => $lng->txt("mcst_automatic_detection"));
                $options = array_merge($options, $this->mimeTypes);
                $mimeTypeSelection->setOptions($options);
                $this->form_gui->addItem($mimeTypeSelection);*/
                
                // preview picure
                $pp = new ilImageFileInputGUI($lng->txt("mcst_preview_picture"), "preview_pic");
                $pp->setSuffixes(array("png", "jpeg", "jpg"));
                $pp->setInfo($lng->txt("mcst_preview_picture_info") . " mp4, mp3, png, jp(e)g, gif");
                $this->form_gui->addItem($pp);
            }
        }
        
        // save/cancel button
        if ($a_mode == "create") {
            $this->form_gui->setTitle($lng->txt("mcst_add_new_item"));
            $this->form_gui->addCommandButton("saveCastItem", $lng->txt("save"));
        } else {
            $this->form_gui->setTitle($lng->txt("mcst_edit_item"));
            $this->form_gui->addCommandButton("updateCastItem", $lng->txt("save"));
        }
        $this->form_gui->addCommandButton("listItems", $lng->txt("cancel"));
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveCastItem"));
    }
    
    /**
     * Get cast item values into form.
     */
    public function getCastItemValues() : void
    {
        $lng = $this->lng;
        
        // get mob
        $this->mcst_item = new ilNewsItem(
            $this->mc_request->getItemId()
        );
        $mob = new ilObjMediaObject($this->mcst_item->getMobId());
        
        // preview
        $ppic = $mob->getVideoPreviewPic();
        if ($ppic != "") {
            $i = $this->form_gui->getItemByPostVar("preview_pic");
            $i->setImage($ppic);
        }
        
        
        $values = array();
        $mediaItems = $this->getMediaItems(
            $this->mc_request->getItemId()
        );
        if (count($mediaItems) > 0) {
            foreach ($mediaItems as $med) {
                if (!isset($values["title"])) {
                    // first item, so set title, description, ...
                    $values["title"] = $this->mcst_item->getTitle();
                    $values["description"] = $this->mcst_item->getContent();
                    $values["visibility"] = $this->mcst_item->getVisibility();
                    $length = explode(":", $this->mcst_item->getPlaytime());
                    $values["duration"] = array("hh" => $length[0], "mm" => $length[1], "ss" => $length[2]);
                }
                
                $values["value_" . $med->getPurpose()] = (strlen($med->getLocation()) > 100) ? "..." . substr($med->getLocation(), strlen($med->getLocation()) - 100) : $med->getLocation();
                $values["label_value_" . $med->getPurpose()] = (strlen($med->getLocation()) > 100) ? "..." . substr($med->getLocation(), strlen($med->getLocation()) - 100) : $med->getLocation();
                $values["mimetype_" . $med->getPurpose()] = $med->getFormat();
            }
        }
        foreach (ilObjMediaCast::$purposes as $purpose) {
            if (!isset($values["value_" . $purpose])) {
                $values["label_value_" . $purpose] = $lng->txt("none");
                $values["value_" . $purpose] = $lng->txt("none");
            }
        }
        $this->form_gui->setValuesByArray($values);
    }
    
    public function saveCastItemObject() : void
    {
        return;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $this->checkPermission("write");
        $ilTabs->activateTab("edit_content");
        
        $this->initAddCastItemForm();

        if (!$this->form_gui->checkInput() ||
            ($this->form_gui->getInput("url_Standard") == "" && !$_FILES['file_Standard']['tmp_name'])) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("mcst_input_either_file_or_url"));
            $this->populateFormFromPost();
        } else {
            // create dummy object in db (we need an id)
            $mob = new ilObjMediaObject();
            $mob->create();

            //handle standard purpose
            $file = $this->createMediaItemForPurpose($mob, "Standard");

            // set title and description
            // set title to basename of file if left empty
            $title = $this->form_gui->getInput("title") != "" ? $this->form_gui->getInput("title") : basename($file);
            $description = $this->form_gui->getInput("description");
            $mob->setTitle($title);
            $mob->setDescription($description);

            // save preview pic
            $prevpic = $this->form_gui->getInput("preview_pic");
            if ($prevpic["size"] > 0) {
                $mob->uploadVideoPreviewPic($prevpic);
            }
            
            // determine duration for standard purpose
            $duration = $this->getDuration($file);

            // handle other purposes
            foreach ($this->additionalPurposes as $purpose) {
                // check if some purpose has been uploaded
                $file_gui = $this->form_gui->getInput("file_" . $purpose);
                $url_gui = $this->form_gui->getInput("url_" . $purpose);
                if ($url_gui || $file_gui["size"] > 0) {
                    $this->createMediaItemForPurpose($mob, $purpose);
                }
            }

            $mob->update();

            if ($prevpic["size"] == 0) {
                // re-read media object
                $mob = new ilObjMediaObject($mob->getId());
                $mob->generatePreviewPic(320, 240);
            }
            
            //
            // @todo: save usage
            //
            
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            // create new media cast item
            $mc_item = new ilNewsItem();
            $mc_item->setMobId($mob->getId());
            $mc_item->setContentType(NEWS_AUDIO);
            $mc_item->setContextObjId($this->object->getId());
            $mc_item->setContextObjType($this->object->getType());
            $mc_item->setUserId($ilUser->getId());
            $mc_item->setPlaytime($duration);
            $mc_item->setTitle($title);
            $mc_item->setContent($description);
            $mc_item->setLimitation(false);
            if ($enable_internal_rss) {
                $mc_item->setVisibility($this->form_gui->getInput("visibility"));
            } else {
                $mc_item->setVisibility("users");
            }
            $mc_item->create();
            
            $ilCtrl->redirect($this, "listItems");
        }
    }
    
    private function getDuration(ilMediaItem $media_item) : string
    {
        $duration = isset($this->form_gui)
            ? $this->form_gui->getInput("duration")
            : array("hh" => 0, "mm" => 0, "ss" => 0);

        $duration_str = str_pad($duration["hh"], 2, "0", STR_PAD_LEFT) . ":" .
            str_pad($duration["mm"], 2, "0", STR_PAD_LEFT) . ":" .
            str_pad($duration["ss"], 2, "0", STR_PAD_LEFT);

        if ($duration["hh"] == 0 && $duration["mm"] == 0 && $duration["ss"] == 0) {
            $media_item->determineDuration();
            $d = $media_item->getDuration();
            if ($d > 0) {
                $duration_str = $this->object->getPlaytimeForSeconds($d);
            }
        }

        return $duration_str;
    }
    
    /**
     * Handle media item for given purpose
     */
    private function createMediaItemForPurpose(
        ilObjMediaObject $mob,
        string $purpose
    ) : string {
        $mediaItem = new ilMediaItem();
        $mob->addMediaItem($mediaItem);
        $mediaItem->setPurpose($purpose);
        return $this->updateMediaItem($mob, $mediaItem);
    }
    
    /**
     * Update media item from form
     */
    private function updateMediaItem(
        ilObjMediaObject $mob,
        ilMediaItem $mediaItem
    ) : string {
        $locationType = "";
        $location = "";
        $file = "";
        $purpose = $mediaItem->getPurpose();
        $locationType = $mediaItem->getLocationType();
        $url_gui = $this->form_gui->getInput("url_" . $purpose);
        $file_gui = $this->form_gui->getInput("file_" . $purpose);
        if ($url_gui) {
            // http
            $file = $this->form_gui->getInput("url_" . $purpose);
            $title = basename($file);
            $location = $this->form_gui->getInput("url_" . $purpose);
            $locationType = "Reference";
        } elseif ($file_gui["size"] > 0) {
            // lokal
            // determine and create mob directory, move uploaded file to directory
            $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
            if (!is_dir($mob_dir)) {
                $mob->createDirectory();
            }
            
            $file_name = ilFileUtils::getASCIIFilename($_FILES['file_' . $purpose]['name']);
            $file_name = str_replace(" ", "_", $file_name);

            $file = $mob_dir . "/" . $file_name;
            $title = $file_name;
            $locationType = "LocalFile";
            $location = $title;
            ilFileUtils::moveUploadedFile($_FILES['file_' . $purpose]['tmp_name'], $file_name, $file);
            ilFileUtils::renameExecutables($mob_dir);
        }
        
        // check if not automatic mimetype detection
        $format = ilObjMediaObject::getMimeType($mediaItem->getLocation(), ($locationType === "Reference"));
        $mediaItem->setFormat($format);

        if ($file != "") {
            // get mime type, if not already set!
            if ($format === "") {
                $format = ilObjMediaObject::getMimeType($file, ($locationType === "Reference"));
            }

            // set real meta and object data
            $mediaItem->setFormat($format);
            $mediaItem->setLocation($location);
            $mediaItem->setLocationType($locationType);
            $mediaItem->setHAlign("Left");
            $mediaItem->setHeight(self::isAudio($format)?0:180);
        }
                    
        if (($purpose === "Standard") && isset($title)) {
            $mob->setTitle($title);
        }

        return $file;
    }
    
    /**
     * Update cast item
     */
    public function updateCastItemObject() : void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilLog = $this->log;
        $title = "";
        $description = "";
        $file = null;

        $this->checkPermission("write");
        
        $this->initAddCastItemForm("edit");

        if ($this->form_gui->checkInput()) {
            // create new media cast item
            $mc_item = new ilNewsItem(
                $this->mc_request->getItemId()
            );
            $mob_id = $mc_item->getMobId();
            
            // create dummy object in db (we need an id)
            $mob = new ilObjMediaObject($mob_id);


            foreach (ilObjMediaCast::$purposes as $purpose) {
                if ($this->form_gui->getInput("delete_" . $purpose)) {
                    $mob->removeMediaItem($purpose);
                    $ilLog->write("Mcst: deleting purpose $purpose");
                    continue;
                }
                $media_item = $mob->getMediaItem($purpose);
                $url_gui = $this->form_gui->getInput("url_" . $purpose);
                $file_gui = $this->form_gui->getInput("file_" . $purpose);
                
                if ($media_item == null) {
                    if ($purpose != "Standard" &&
                       ($url_gui || $file_gui["size"] > 0)) {
                        // check if we added an additional purpose when updating
                        // either by url or by file
                        $file = $this->createMediaItemForPurpose($mob, $purpose);
                    }
                } else {
                    $file = $this->updateMediaItem($mob, $media_item);
                }

                if ($purpose == "Standard") {
                    $duration = $this->getDuration($media_item);
                    $title = $this->form_gui->getInput("title") != "" ? $this->form_gui->getInput("title") : basename($file);
                    $description = $this->form_gui->getInput("description");
            
                    $mob->setTitle($title);
                    $mob->setDescription($description);
                    
                    $prevpic = $this->form_gui->getInput("preview_pic");
                    if ($prevpic["size"] > 0) {
                        $mob->uploadVideoPreviewPic($prevpic);
                    } else {
                        $prevpici = $this->form_gui->getItemByPostVar("preview_pic");
                        if ($prevpici->getDeletionFlag()) {
                            $mob->removeAdditionalFile($mob->getVideoPreviewPic(true));
                        }
                    }
                }
            }
            
            // set real meta and object data
            $mob->update();
            
            //
            // @todo: save usage
            //
            
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            $mc_item->setUserId($ilUser->getId());
            if (isset($duration)) {
                $mc_item->setPlaytime($duration);
                $dur_arr = explode(":", $duration);
                $seconds = ((int) $dur_arr[0] * 60 * 60) + ((int) $dur_arr[1] * 60) + ((int) $dur_arr[2]);
                $st_med = $mob->getMediaItem("Standard");
                $st_med->setDuration($seconds);
                $st_med->update();
            }
            $mc_item->setTitle($title);
            $mc_item->setContent($description);
            if ($enable_internal_rss) {
                $mc_item->setVisibility($this->form_gui->getInput("visibility"));
            }
            $mc_item->update();

            $ilCtrl->redirect($this, "listItems");
        } else {
            $this->populateFormFromPost();
        }
    }

    public function confirmDeletionItemsObject() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("write");
        $ilTabs->activateTab("edit_content");

        $ids = $this->mc_request->getItemIds();
        if (count($ids) == 0) {
            $this->listItemsObject();
            return;
        }
        
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($ilCtrl->getFormAction($this, "deleteItems"));
        $c_gui->setHeaderText($lng->txt("info_delete_sure"));
        $c_gui->setCancel($lng->txt("cancel"), "listItems");
        $c_gui->setConfirm($lng->txt("confirm"), "deleteItems");

        // add items to delete
        foreach ($ids as $item_id) {
            $item = new ilNewsItem($item_id);
            $c_gui->addItem(
                "item_id[]",
                $item_id,
                $item->getTitle(),
                ilUtil::getImagePath("icon_mcst.svg")
            );
        }
        
        $tpl->setContent($c_gui->getHTML());
    }

    public function deleteItemsObject() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");
        
        // delete all selected news items
        $ids = $this->mc_request->getItemIds();
        foreach ($ids as $item_id) {
            $mc_item = new ilNewsItem($item_id);
            $mc_item->delete();
        }
        
        $ilCtrl->redirect($this, "listItems");
    }
    
    /**
     * Download news media item
     */
    public function downloadItemObject() : void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $this->checkPermission("read");
        $news_item = new ilNewsItem($this->mc_request->getItemId());
        $this->object->handleLPUpdate($ilUser->getId(), $news_item->getMobId());
        if (!$news_item->deliverMobFile(
            $this->mc_request->getPurpose(),
            $this->mc_request->getPresentation()
        )) {
            $ilCtrl->redirect($this, "listItems");
        }
        exit;
    }
    
    public function determinePlaytimeObject() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $mc_item = new ilNewsItem($this->mc_request->getItemId());
        $mob = $mc_item->getMobId();
        $mob = new ilObjMediaObject($mob);
        $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
        $m_item = $mob->getMediaItem("Standard");

        $success = false;

        $m_item->determineDuration();
        $dur = $m_item->getDuration();
        if ($dur > 0) {
            $mc_item->setPlaytime($this->object->getPlaytimeForSeconds($dur));
            $mc_item->update();
            $success = true;
            $this->tpl->setOnScreenMessage('success', $lng->txt("mcst_set_playtime"), true);
        }

        if (!$success) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("mcst_unable_to_determin_playtime"), true);
        }

        $ilCtrl->redirect($this, "listItems");
    }

    /**
     * This one is called from the info button in the repository
     */
    public function infoScreenObject() : void
    {
        if (!$this->checkPermissionBool("read")) {
            $this->checkPermission("visible");
        }
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function infoScreen() : void
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("id_info");

        if (!$this->checkPermissionBool("read")) {
            $this->checkPermission("visible");
        }

        $info = new ilInfoScreenGUI($this);
        
        $info->enablePrivateNotes();

        // general information
        $this->lng->loadLanguageModule("meta");
        $this->lng->loadLanguageModule("mcst");
        $med_items = $this->object->getItemsArray();
        $info->addSection($this->lng->txt("meta_general"));
        $info->addProperty(
            $this->lng->txt("mcst_nr_items"),
            count($med_items)
        );
            
        if (count($med_items) > 0) {
            $cur = current($med_items);
            $last = ilDatePresentation::formatDate(new ilDateTime($cur["creation_date"], IL_CAL_DATETIME));
        } else {
            $last = "-";
        }

        $info->addProperty($this->lng->txt("mcst_last_submission"), $last);

        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    protected function setTabs() : void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilHelp = $this->help;
        
        $ilHelp->setScreenIdComponent("mcst");
        
        // list items
        if ($ilAccess->checkAccess('read', "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "content",
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "showContent")
            );
        }

        // info screen
        if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()) ||
            $ilAccess->checkAccess('read', "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
            );
        }

        // settings
        if ($ilAccess->checkAccess('write', "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "id_settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
        }
        
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $ilTabs->addTab(
                'learning_progress',
                $lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass(array(__CLASS__, 'illearningprogressgui'), '')
            );
        }

        // export
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        // edit permissions
        if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "id_permissions",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm")
            );
        }
    }

    public function addContentSubTabs(
        string $a_active = "content"
    ) : void {
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;
        $lng = $this->lng;
        
        $ilTabs->addSubTab(
            "content",
            $lng->txt("view"),
            $this->ctrl->getLinkTarget($this, "showContent")
        );

        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $ilTabs->addSubTab(
                "manage",
                $lng->txt("mcst_manage"),
                $this->ctrl->getLinkTarget($this, "listItems")
            );
            
            if ($this->object->getOrder() == ilObjMediaCast::ORDER_MANUAL) {
                $ilTabs->addSubTab(
                    "sorting",
                    $lng->txt("mcst_ordering"),
                    $this->ctrl->getLinkTarget($this, "editOrder")
                );
            }
        }
        
        $ilTabs->activateSubTab($a_active);
        $ilTabs->activateTab("content");
    }

    public function editSettingsObject() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("write");
        $ilTabs->activateTab("id_settings");
        
        $this->initSettingsForm();
        $tpl->setContent($this->form_gui->getHTML());
    }
    
    /**
     * Init Settings Form
     */
    public function initSettingsForm() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $obj_service = $this->object_service;
        
        $lng->loadLanguageModule("mcst");
        
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setTitle($lng->txt("mcst_settings"));
        
        // Title
        $tit = new ilTextInputGUI($lng->txt("title"), "title");
        $tit->setValue($this->object->getTitle());
        $tit->setRequired(true);
        $this->form_gui->addItem($tit);
        
        // description
        $des = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $des->setValue($this->object->getLongDescription());
        $this->form_gui->addItem($des);
        
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("rep_activation_availability"));
        $this->form_gui->addItem($sh);

        // Online
        $online = new ilCheckboxInputGUI($lng->txt("online"), "online");
        $online->setChecked($this->object->getOnline());
        $this->form_gui->addItem($online);

        // presentation
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("obj_presentation"));
        $this->form_gui->addItem($sh);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form_gui, $this->object)->addTileImage();
        
        // Sorting
        $sort = new ilRadioGroupInputGUI($lng->txt("mcst_ordering"), "order");
        $sort->addOption(new ilRadioOption(
            $lng->txt("mcst_ordering_title"),
            ilObjMediaCast::ORDER_TITLE
        ));
        $sort->addOption(new ilRadioOption(
            $lng->txt("mcst_ordering_creation_date_asc"),
            ilObjMediaCast::ORDER_CREATION_DATE_ASC
        ));
        $sort->addOption(new ilRadioOption(
            $lng->txt("mcst_ordering_creation_date_desc"),
            ilObjMediaCast::ORDER_CREATION_DATE_DESC
        ));
        $sort->addOption(new ilRadioOption(
            $lng->txt("mcst_ordering_manual"),
            ilObjMediaCast::ORDER_MANUAL
        ));
        $sort->setValue($this->object->getOrder());
        $this->form_gui->addItem($sort);
        
        // view mode
        $si = new ilRadioGroupInputGUI($this->lng->txt("mcst_viewmode"), "viewmode");
        $si->addOption(new ilRadioOption(
            $lng->txt("mcst_list"),
            ilObjMediaCast::VIEW_LIST
        ));
        $si->addOption(new ilRadioOption(
            $lng->txt("mcst_gallery"),
            ilObjMediaCast::VIEW_GALLERY
        ));
        $si->addOption(new ilRadioOption(
            $lng->txt("mcst_img_gallery"),
            ilObjMediaCast::VIEW_IMG_GALLERY
        ));
        $si->addOption(new ilRadioOption(
            $lng->txt("mcst_podcast"),
            ilObjMediaCast::VIEW_PODCAST
        ));
        $si->addOption($vc_opt = new ilRadioOption(
            $lng->txt("mcst_video_cast"),
            ilObjMediaCast::VIEW_VCAST
        ));

        //		$si->setOptions($options);
        $si->setValue($this->object->getViewMode());
        $this->form_gui->addItem($si);

        // autoplay
        $options = array(
            ilObjMediaCast::AUTOPLAY_NO => $lng->txt("mcst_no_autoplay"),
            ilObjMediaCast::AUTOPLAY_ACT => $lng->txt("mcst_autoplay_active"),
            ilObjMediaCast::AUTOPLAY_INACT => $lng->txt("mcst_autoplay_inactive")
        );
        $si = new ilSelectInputGUI($lng->txt("mcst_autoplay"), "autoplaymode");
        $si->setOptions($options);
        $si->setValue($this->object->getAutoplayMode());
        $vc_opt->addSubItem($si);

        // number of initial videos
        $ti = new ilNumberInputGUI($lng->txt("mcst_nr_videos"), "nr_videos");
        $ti->setValue($this->object->getNumberInitialVideos());
        $ti->setSize(2);
        $vc_opt->addSubItem($ti);

        // Downloadable
        $downloadable = new ilCheckboxInputGUI($lng->txt("mcst_downloadable"), "downloadable");
        $downloadable->setChecked($this->object->getDownloadable());
        $downloadable->setInfo($lng->txt("mcst_downloadable_info"));
        $this->form_gui->addItem($downloadable);
        
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        //Default Visibility
        if ($enable_internal_rss) {
            // webfeed
            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("mcst_webfeed"));
            $this->form_gui->addItem($sh);

            $radio_group = new ilRadioGroupInputGUI($lng->txt("news_default_visibility"), "defaultaccess");
            $radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "0");
            $radio_option->setInfo($lng->txt("news_news_item_def_visibility_users_info"));
            $radio_group->addOption($radio_option);
            $radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "1");
            $radio_option->setInfo($lng->txt("news_news_item_def_visibility_public_info"));
            $radio_group->addOption($radio_option);
            $radio_group->setRequired(false);
            $radio_group->setValue($this->object->getDefaultAccess());
            #$ch->addSubItem($radio_group);
            $this->form_gui->addItem($radio_group);
        
            //Extra Feed
            $public_feed = ilBlockSetting::_lookup("news", "public_feed", 0, $this->object->getId());
            $ch = new ilCheckboxInputGUI($lng->txt("news_public_feed"), "extra_feed");
            $ch->setInfo($lng->txt("news_public_feed_info"));
            $ch->setChecked((bool) $public_feed);
            $this->form_gui->addItem($ch);
            
            // keep minimal x number of items
            $ni = new ilNumberInputGUI($this->lng->txt("news_keep_minimal_x_items"), "keep_rss_min");
            $ni->setMaxValue(100);
            $ni->setMinValue(0);
            $ni->setMaxLength(3);
            $ni->setSize(3);
            $ni->setInfo($this->lng->txt("news_keep_minimal_x_items_info") . " (" .
                    ilNewsItem::_lookupRSSPeriod() . " " . (ilNewsItem::_lookupRSSPeriod() == 1 ? $lng->txt("day") : $lng->txt("days")) . ")");
            $ni->setValue((int) ilBlockSetting::_lookup("news", "keep_rss_min", 0, $this->object->getId()));
            $ch->addSubItem($ni);
            
            // Include Files in Pubic Items
            $incl_files = new ilCheckboxInputGUI($lng->txt("mcst_incl_files_in_rss"), "public_files");
            $incl_files->setChecked($this->object->getPublicFiles());
            $incl_files->setInfo($lng->txt("mcst_incl_files_in_rss_info"));
            #$ch->addSubItem($incl_files);
            $this->form_gui->addItem($incl_files);
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("learning_progress"));
            $this->form_gui->addItem($sh);

            // Include new items automatically in learning progress
            $auto_lp = new ilCheckboxInputGUI($lng->txt("mcst_new_items_det_lp"), "auto_det_lp");
            $auto_lp->setChecked($this->object->getNewItemsInLearningProgress());
            $auto_lp->setInfo($lng->txt("mcst_new_items_det_lp_info"));
            $this->form_gui->addItem($auto_lp);
        }


        // Form action and save button
        $this->form_gui->addCommandButton("saveSettings", $lng->txt("save"));
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveSettings"));
    }
    
    public function saveSettingsObject() : void
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $obj_service = $this->object_service;
        
        $this->checkPermission("write");
        $ilTabs->activateTab("id_settings");
        
        $this->initSettingsForm();
        if ($this->form_gui->checkInput()) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            $this->object->setTitle($this->form_gui->getInput("title"));
            $this->object->setDescription($this->form_gui->getInput("description"));
            $this->object->setOnline($this->form_gui->getInput("online"));
            $this->object->setDownloadable($this->form_gui->getInput("downloadable"));
            $this->object->setOrder($this->form_gui->getInput("order"));
            $this->object->setViewMode($this->form_gui->getInput("viewmode"));
            $this->object->setAutoplayMode((int) $this->form_gui->getInput("autoplaymode"));
            $this->object->setNumberInitialVideos((int) $this->form_gui->getInput("nr_videos"));
            $this->object->setNewItemsInLearningProgress((int) $this->form_gui->getInput("auto_det_lp"));

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form_gui, $this->object)->saveTileImage();
            
            if ($enable_internal_rss) {
                $this->object->setPublicFiles($this->form_gui->getInput("public_files"));
                $this->object->setDefaultAccess($this->form_gui->getInput("defaultaccess"));
            }
            $this->object->update();
            
            if ($enable_internal_rss) {
                ilBlockSetting::_write(
                    "news",
                    "public_feed",
                    $this->form_gui->getInput("extra_feed"),
                    0,
                    $this->object->getId()
                );

                ilBlockSetting::_write(
                    "news",
                    "keep_rss_min",
                    $this->form_gui->getInput("keep_rss_min"),
                    0,
                    $this->object->getId()
                );
            }
            
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editSettings");
        } else {
            $this->form_gui->setValuesByPost();
            $this->tpl->setContent($this->form_gui->getHTML());
        }
    }

    protected function addLocatorItems() : void
    {
        $ilLocator = $this->locator;
        
        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "listItems"), "", $this->requested_ref_id);
        }
    }

    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $ctrl->setParameterByClass("ilobjmediacastgui", "ref_id", $a_target);
            $ctrl->redirectByClass(["ilmediacasthandlergui", "ilobjmediacastgui"], "showContent");
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ));
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read"));
    }
    
    protected static function isAudio(string $extension) : bool
    {
        return strpos($extension, "audio") !== false;
    }
    
    /**
     * Get MediaItem for id and updates local variable mcst_item
     */
    protected function getMediaItem(int $id) : ilMediaItem
    {
        $this->mcst_item = new ilNewsItem($id);
        // create dummy object in db (we need an id)
        $mob = new ilObjMediaObject($this->mcst_item->getMobId());
        return $mob->getMediaItem("Standard");
    }
    
    protected function getMediaItems(int $id) : array
    {
        $this->mcst_item = new ilNewsItem($id);
        // create dummy object in db (we need an id)
        $mob = new ilObjMediaObject($this->mcst_item->getMobId());
        return $mob->getMediaItems();
    }
    
    private function populateFormFromPost() : void
    {
        $tpl = $this->tpl;
        $this->form_gui->setValuesByPost();

        //issue: we have to display the current settings
        // problem: POST does not contain values of disabled text fields
        // solution: use hidden field and label to display-> here we need to synchronize the labels
        // with the values from the hidden fields.
        foreach (ilObjMediaCast::$purposes as $purpose) {
            if ($this->form_gui->getInput("value_" . $purpose)) {
                $input = $this->form_gui->getItemByPostVar("label_value_" . $purpose);
                $input->setValue($this->form_gui->getInput("value_" . $purpose));
            }
        }
        
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }
    
    protected function editOrderObject() : void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        $ilTabs->activateTab("edit_content");
        
        $this->addContentSubTabs("sorting");
        
        // sort by order setting
        switch ($this->object->getOrder()) {
            case ilObjMediaCast::ORDER_TITLE:
            case ilObjMediaCast::ORDER_CREATION_DATE_ASC:
            case ilObjMediaCast::ORDER_CREATION_DATE_DESC:
                $this->listItemsObject();
                return;
            
            case ilObjMediaCast::ORDER_MANUAL:
                // sub-tabs
                break;
        }
    
        $table_gui = new ilMediaCastTableGUI($this, "editOrder", true);
                
        $table_gui->setTitle($lng->txt("mcst_media_cast"));
        $table_gui->setData($this->object->getSortedItemsArray());
        
        $table_gui->addCommandButton("saveOrder", $lng->txt("mcst_save_order"));
        
        $tpl->setContent($table_gui->getHTML());
    }
    
    public function saveOrderObject() : void
    {
        $lng = $this->lng;

        $ids = $this->mc_request->getItemIds();
        asort($ids);
        
        $items = array();
        foreach (array_keys($ids) as $id) {
            $items[] = $id;
        }
        $this->object->saveOrder($items);
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "editOrder");
    }
    
    ////
    //// Show content
    ////
    
    public function showContentObject() : void
    {
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;

        // need read events for parent for LP statistics
        ilChangeEvent::_recordReadEvent(
            "mcst",
            $this->object->getRefId(),
            $this->object->getId(),
            $ilUser->getId()
        );

        // trigger LP update
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());

        if ($this->object->getViewMode() == ilObjMediaCast::VIEW_GALLERY) {
            $this->showGallery();
        } elseif ($this->object->getViewMode() == ilObjMediaCast::VIEW_IMG_GALLERY) {
            $view = new \McstImageGalleryGUI($this->object, $this->tpl);
            $this->tabs->activateTab("content");
            $this->addContentSubTabs("content");
            $tpl->setContent($this->ctrl->getHTML($view));
        } elseif ($this->object->getViewMode() == ilObjMediaCast::VIEW_PODCAST) {
            $view = new \McstPodcastGUI($this->object, $this->tpl);
            $this->tabs->activateTab("content");
            $this->addContentSubTabs("content");
            $tpl->setContent($this->ctrl->getHTML($view));
        } elseif ($this->object->getViewMode() == ilObjMediaCast::VIEW_VCAST) {
            $ilTabs->activateTab("content");
            $this->addContentSubTabs("content");
            $view = new \ILIAS\MediaCast\Presentation\VideoViewGUI($this->object, $tpl);
            $view->setCompletedCallback($this->ctrl->getLinkTarget(
                $this,
                "handlePlayerCompletedEvent",
                "",
                true,
                false
            ));
            $view->setAutoplayCallback($this->ctrl->getLinkTarget(
                $this,
                "handleAutoplayTrigger",
                "",
                true,
                false
            ));
            $view->show();
        } else {
            $this->listItemsObject(true);
        }

        $tpl->setPermanentLink($this->object->getType(), $this->object->getRefId());
    }
    
    public function showGallery() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $tpl->addJavascript("./Modules/MediaCast/js/MediaCast.js");
        
        $ilTabs->activateTab("content");
        
        $this->addContentSubTabs("content");
        
        $ctpl = new ilTemplate("tpl.mcst_content.html", true, true, "Modules/MediaCast");
        
        foreach ($this->object->getSortedItemsArray() as $item) {
            $mob = new ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            
            $ctpl->setCurrentBlock("item");
            $ctpl->setVariable("TITLE", $item["title"]);
            $ctpl->setVariable("TIME", $item["playtime"]);
            $ctpl->setVariable("ID", $item["id"]);
            
            if ($mob->getVideoPreviewPic() != "") {
                $ctpl->setVariable(
                    "PREVIEW_PIC",
                    ilUtil::img(ilWACSignedPath::signFile($mob->getVideoPreviewPic()), $item["title"], 320, 240)
                );
            } else {
                $ctpl->setVariable(
                    "PREVIEW_PIC",
                    ilUtil::img(ilUtil::getImagePath("mcst_preview.svg"), $item["title"], 320, 240)
                );
            }
            
            // player
            if (is_object($med)) {

                // the news id will be used as player id, see also ilMediaCastTableGUI
                $mpl = new ilMediaPlayerGUI(
                    $item["id"],
                    $ilCtrl->getLinkTarget($this, "handlePlayerEvent", "", true, false)
                );
                
                if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                    ilWACSignedPath::signFolderOfStartFile($med->getLocation());
                    $mpl->setFile($med->getLocation());
                } else {
                    $path_to_file = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                    ilWACSignedPath::signFolderOfStartFile($path_to_file);
                    $mpl->setFile($path_to_file);
                }
                $mpl->setMimeType($med->getFormat());
                //$mpl->setDisplayHeight($med->getHeight());
                //$mpl->setDisplayHeight("480");
                //$mpl->setDisplayWidth("320px");
                $mpl->setVideoPreviewPic(ilWACSignedPath::signFile($mob->getVideoPreviewPic()));
                $mpl->setTitle($item["title"]);
                $mpl->setDescription($item["content"]);
                $mpl->setForceAudioPreview(true);
                if ($this->object->getDownloadable()) {
                    $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $item["id"]);
                    $ilCtrl->setParameterByClass("ilobjmediacastgui", "purpose", "Standard");
                    $mpl->setDownloadLink($ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem"));
                }
                $med_alt = $mob->getMediaItem("VideoAlternative");
                if (is_object($med_alt)) {
                    $mpl->setAlternativeVideoFile(ilWACSignedPath::signFile(ilObjMediaObject::_getURL($mob->getId()) . "/" .
                        $med_alt->getLocation()));
                    $mpl->setAlternativeVideoMimeType($med_alt->getFormat());
                }
                
                $ctpl->setVariable("PLAYER", $mpl->getPreviewHtml());
            }

            
            $ctpl->parseCurrentBlock();
        }
        
        $feed_icon_html = $this->getFeedIconsHTML();

        if ($feed_icon_html != "") {
            $feed_icon_html = '<p>' . $feed_icon_html . '</p>';
        }
        
        $tpl->setContent($feed_icon_html . $ctpl->get());
    }

    public function extractPreviewImageObject() : void
    {
        $ilCtrl = $this->ctrl;
        $add = "";
        
        $this->checkPermission("write");
        
        $this->mcst_item = new ilNewsItem($this->mc_request->getItemId());
        $mob = new ilObjMediaObject($this->mcst_item->getMobId());
        
        try {
            $sec = $this->mc_request->getSeconds();
            if ($sec < 0) {
                $sec = 0;
            }

            $mob->generatePreviewPic(320, 240, $sec);
            if ($mob->getVideoPreviewPic() !== "") {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("mcst_image_extracted"), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("mcst_no_extraction_possible"), true);
            }
        } catch (ilException $e) {
            if (DEVMODE == 1) {
                $ret = ilFFmpeg::getLastReturnValues();
                $add = (is_array($ret) && count($ret) > 0)
                    ? "<br />" . implode("<br />", $ret)
                    : "";
            }
            $this->tpl->setOnScreenMessage('failure', $e->getMessage() . $add, true);
        }
        
        $ilCtrl->redirect($this, "editCastItem");
    }

    public function handlePlayerEventObject() : void
    {
        if ($this->mc_request->getEvent() === "play") {
            $player = explode("_", $this->mc_request->getPlayer());
            $news_id = (int) $player[1];
            $item = new ilNewsItem($news_id);
            $item->increasePlayCounter();
            
            $mob_id = $item->getMobId();
            if ($mob_id) {
                $ilUser = $this->user;
                $this->object->handleLPUpdate($ilUser->getId(), $mob_id);
            }
        }
        exit;
    }

    protected function handlePlayerCompletedEventObject() : void
    {
        $mob_id = $this->mc_request->getMobId();
        if ($mob_id > 0) {
            $ilUser = $this->user;
            $this->object->handleLPUpdate($ilUser->getId(), $mob_id);
        }
        exit;
    }

    protected function afterUpload($mob_ids) : void
    {
        $this->addMobsToCast($mob_ids, "", false);
    }

    protected function afterUrlSaving(int $mob_id, string $long_desc) : void
    {
        $this->addMobsToCast([$mob_id], $long_desc);
    }

    protected function addMobsToCast(
        array $mob_ids,
        string $long_desc = "",
        bool $redirect = true
    ) : void {
        $ctrl = $this->ctrl;
        $user = $this->user;

        $item_ids = [];
        foreach ($mob_ids as $mob_id) {
            $item_ids[] = $this->object->addMobToCast($mob_id, $user->getId(), $long_desc);
        }

        if ($redirect) {
            if (count($item_ids) === 1) {
                $ctrl->setParameter($this, "item_id", $item_ids[0]);
                $ctrl->redirect($this, "editCastItem");
            }
            $ctrl->redirect($this, "listItems");
        }
    }

    protected function afterPoolInsert(array $mob_ids) : void
    {
        $this->addMobsToCast($mob_ids);
    }

    protected function handleAutoplayTriggerObject() : void
    {
        $this->user->writePref(
            "mcst_autoplay",
            $this->mc_request->getAutoplay()
        );
        exit;
    }
}
