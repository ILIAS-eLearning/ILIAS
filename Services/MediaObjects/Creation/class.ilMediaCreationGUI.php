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

use ILIAS\MediaObjects\Creation\CreationGUIRequest;

use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\FileUpload;
use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\FileUpload\Handler\HandlerResult;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilMediaCreationGUI: ilPropertyFormGUI, ilRepoStandardUploadHandlerGUI
 */
class ilMediaCreationGUI
{
    public const TYPE_VIDEO = 1;
    public const TYPE_AUDIO = 2;
    public const TYPE_IMAGE = 3;
    public const TYPE_OTHER = 4;
    public const TYPE_ALL = 5;
    public const POOL_VIEW_FOLDER = "fold";
    public const POOL_VIEW_ALL = "all";
    protected InternalGUIService $gui;
    protected ?FormAdapterGUI $bulk_upload_form = null;
    protected CreationGUIRequest $request;
    
    protected array $accept_types = [1,2,3,4];
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected Closure $after_upload;
    protected Closure $after_url_saving;
    protected Closure $after_pool_insert;
    protected Closure $finish_single_upload;
    protected Closure $on_mob_update;
    protected ilAccessHandler $access;
    /**
     * @var string[]
     */
    protected array $all_suffixes = [];
    /**
     * @var string[]
     */
    protected array $all_mime_types = [];
    protected \ILIAS\DI\UIServices $ui;
    protected int $requested_mep;
    protected string $pool_view = self::POOL_VIEW_FOLDER;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected ilLogger $mob_log;

    public function __construct(
        array $accept_types,
        Closure $after_upload,
        Closure $after_url_saving,
        Closure $after_pool_insert,
        Closure $finish_single_upload = null,
        Closure $on_mob_update = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("mob");
        $this->lng->loadLanguageModule("content");
        $this->access = $DIC->access();

        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui = $DIC->ui();
        $this->upload = $DIC->upload();
        $this->mob_log = $DIC->logger()->mob();

        $this->accept_types = $accept_types;
        $this->after_upload = $after_upload;
        $this->after_url_saving = $after_url_saving;
        $this->after_pool_insert = $after_pool_insert;
        $this->finish_single_upload = $finish_single_upload;
        $this->on_mob_update = $on_mob_update;

        $this->ctrl->saveParameter($this, ["mep", "pool_view"]);

        $this->request = $DIC->mediaObjects()
            ->internal()
            ->gui()
            ->creation()
            ->request();

        $this->requested_mep = $this->request->getMediaPoolId();

        $pv = $this->request->getPoolView();
        $this->pool_view = (in_array($pv, [self::POOL_VIEW_FOLDER, self::POOL_VIEW_ALL]))
            ? $pv
            : self::POOL_VIEW_FOLDER;
        $this->gui = $DIC->mediaObjects()->internal()->gui();
    }

    public function setAllSuffixes(
        array $a_val
    ) : void {
        $this->all_suffixes = $a_val;
    }
    
    public function getAllSuffixes() : array
    {
        return $this->all_suffixes;
    }

    public function setAllMimeTypes(
        array $a_val
    ) : void {
        $this->all_mime_types = $a_val;
    }

    /**
     * @return string[]
     */
    public function getAllMimeTypes() : array
    {
        return $this->all_mime_types;
    }

    /**
     * @return string[]
     */
    protected function getSuffixes() : array
    {
        $suffixes = [];
        if (in_array(self::TYPE_ALL, $this->accept_types)) {
            $suffixes = $this->getAllSuffixes();
        }
        if (in_array(self::TYPE_VIDEO, $this->accept_types)) {
            $suffixes[] = "mp4";
        }
        if (in_array(self::TYPE_AUDIO, $this->accept_types)) {
            $suffixes[] = "mp3";
        }
        if (in_array(self::TYPE_IMAGE, $this->accept_types)) {
            $suffixes[] = "jpeg";
            $suffixes[] = "jpg";
            $suffixes[] = "png";
            $suffixes[] = "gif";
        }
        return $suffixes;
    }

    /**
     * @return string[]
     */
    protected function getMimeTypes() : array
    {
        $mimes = [];
        if (in_array(self::TYPE_ALL, $this->accept_types)) {
            $mimes = $this->getAllMimeTypes();
        }
        if (in_array(self::TYPE_VIDEO, $this->accept_types)) {
            $mimes[] = "video/vimeo";
            $mimes[] = "video/mp4";
        }
        if (in_array(self::TYPE_AUDIO, $this->accept_types)) {
            $mimes[] = "audio/mpeg";
        }
        if (in_array(self::TYPE_IMAGE, $this->accept_types)) {
            $mimes[] = "image/png";
            $mimes[] = "image/jpeg";
            $mimes[] = "image/gif";
        }
        return $mimes;
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("creationSelection");

        switch ($next_class) {

            case "ilpropertyformgui":
                $form = $this->initPoolSelection();
                $ctrl->forwardCommand($form);
                break;

            case strtolower(ilRepoStandardUploadHandlerGUI::class):
                $form = $this->getUploadForm();
                $gui = $form->getRepoStandardUploadHandlerGUI("media_files");
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (in_array($cmd, ["creationSelection", "uploadFile", "saveUrl", "cancel", "listPoolItems",
                    "insertFromPool", "poolSelection", "selectPool", "applyFilter", "resetFilter", "performBulkUpload",
                    "editTitlesAndDescriptions", "saveTitlesAndDescriptions"])) {
                    $this->$cmd();
                }
        }
    }

    protected function creationSelection() : void
    {
        $main_tpl = $this->main_tpl;

        $acc = new \ilAccordionGUI();
        $acc->setBehaviour(\ilAccordionGUI::FIRST_OPEN);
        $cnt = 1;
        $forms = [
            $this->getUploadForm(),
            $this->initUrlForm(),
            $this->initPoolSelection()
        ];
        foreach ($forms as $form_type => $cf) {
            $htpl = new \ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");

            // using custom form titles (used for repository plugins)
            $form_title = "";
            if (method_exists($this, "getCreationFormTitle")) {
                $form_title = $this->getCreationFormTitle($form_type);
            }
            if (!$form_title) {
                $form_title = $cf->getTitle();
            }

            // move title from form to accordion
            $htpl->setVariable("TITLE", $this->lng->txt("option") . " " . $cnt . ": " .
                $form_title);
            if (!($cf instanceof FormAdapterGUI)) {
                $cf->setTitle("");
                $cf->setTitleIcon("");
                $cf->setTableWidth("100%");

                $acc->addItem($htpl->get(), $cf->getHTML());
            } else {
                $acc->addItem($htpl->get(), $cf->render());
            }

            $cnt++;
        }
        $main_tpl->setContent($acc->getHTML());
    }

    public function getUploadForm() : FormAdapterGUI
    {
        // $item->setSuffixes($this->getSuffixes());
        if (is_null($this->bulk_upload_form)) {
            $mep_hash = uniqid();
            $this->ctrl->setParameter($this, "mep_hash", $mep_hash);
            $this->bulk_upload_form = $this->gui
                ->form(self::class, 'performBulkUpload')
                ->section("props", $this->lng->txt('mob_upload_file'))
                ->file(
                    "media_files",
                    $this->lng->txt("files"),
                    \Closure::fromCallable([$this, 'handleUploadResult']),
                    "mep_id",
                    20,
                    $this->getMimeTypes()
                );
            // ->meta()->text()->meta()->textarea()
        }
        return $this->bulk_upload_form;
    }

    public function initUrlForm() : ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new \ilPropertyFormGUI();

        //
        $ti = new \ilTextInputGUI($lng->txt("mob_url"), "url");
        $ti->setInfo($lng->txt("mob_url_info"));
        $ti->setRequired(true);
        $form->addItem($ti);

        $form->addCommandButton("saveUrl", $lng->txt("save"));
        $form->addCommandButton("cancel", $lng->txt("cancel"));

        $form->setTitle($lng->txt("mob_external_url"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function initPoolSelection() : ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new \ilPropertyFormGUI();

        $mcst = new ilRepositorySelector2InputGUI(
            $lng->txt("obj_mep"),
            "mep",
            false,
            $form
        );
        $exp = $mcst->getExplorerGUI();
        $exp->setSelectableTypes(["mep"]);
        $exp->setTypeWhiteList(["root", "mep", "cat", "crs", "grp", "fold"]);
        $mcst->setRequired(true);
        $form->addItem($mcst);

        $form->addCommandButton("listPoolItems", $lng->txt("continue"));
        $form->addCommandButton("cancel", $lng->txt("cancel"));

        $form->setTitle($lng->txt("mob_choose_from_pool"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    /*
    protected function uploadFile() : void
    {
        $form = $this->initUploadForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->main_tpl->setContent($form->getHTML());
        //$this->creationSelection();
        } else {
            $mob = new ilObjMediaObject();
            $mob->create();

            //handle standard purpose
            $mediaItem = new ilMediaItem();
            $mob->addMediaItem($mediaItem);
            $mediaItem->setPurpose("Standard");

            // determine and create mob directory, move uploaded file to directory
            $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
            if (!is_dir($mob_dir)) {
                $mob->createDirectory();
            }
            $file_name = ilFileUtils::getASCIIFilename($_FILES['file']["name"]);
            $file_name = str_replace(" ", "_", $file_name);

            $file = $mob_dir . "/" . $file_name;
            $title = $file_name;
            $locationType = "LocalFile";
            $location = $title;
            ilFileUtils::moveUploadedFile($_FILES['file']['tmp_name'], $file_name, $file);
            ilFileUtils::renameExecutables($mob_dir);

            // get mime type, if not already set!
            $format = ilObjMediaObject::getMimeType($file, false);

            // set real meta and object data
            $mediaItem->setFormat($format);
            $mediaItem->setLocation($location);
            $mediaItem->setLocationType($locationType);
            $mediaItem->setHAlign("Left");
            $mob->setTitle($title);

            $mob->update();

            // preview pic
            $mob = new ilObjMediaObject($mob->getId());
            $mob->generatePreviewPic(320, 240);

            // duration
            $med_item = $mob->getMediaItem("Standard");
            $med_item->determineDuration();
            $med_item->update();

            //
            // @todo: save usage
            //

            ($this->after_upload)($mob->getId());
        }
    }*/

    protected function handleUploadResult(
        FileUpload $upload,
        UploadResult $result
    ) : BasicHandlerResult {
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

        // get mime type
        $format = ilObjMediaObject::getMimeType($file);
        $location = $file_name;

        // set real meta and object data
        $media_item->setFormat($format);
        $media_item->setLocation($location);
        $media_item->setLocationType("LocalFile");
        $media_item->setUploadHash($this->request->getUploadHash());
        $mob->update();
        $item_ids[] = $mob->getId();

        $mob = new ilObjMediaObject($mob->getId());
        $mob->generatePreviewPic(320, 240);

        // duration
        $med_item = $mob->getMediaItem("Standard");
        $med_item->determineDuration();
        $med_item->update();

        ($this->after_upload)([$mob->getId()]);

        return new BasicHandlerResult(
            "mep_id",
            HandlerResult::STATUS_OK,
            $med_item->getId(),
            ''
        );
    }

    /**
     * Save bulk upload form
     */
    public function performBulkUpload() : void
    {
        $this->ctrl->setParameter($this, "mep_hash", $this->request->getUploadHash());
        $this->ctrl->redirect($this, "editTitlesAndDescriptions");
    }


    protected function editTitlesAndDescriptions() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $ctrl->saveParameter($this, "mep_hash");

        $main_tpl = $this->main_tpl;

        $media_items = ilMediaItem::getMediaItemsForUploadHash($this->request->getUploadHash());

        $tb = new ilToolbarGUI();
        $tb->setFormAction($ctrl->getFormAction($this));
        $tb->addFormButton($lng->txt("save"), "saveTitlesAndDescriptions");
        $tb->setOpenFormTag(true);
        $tb->setCloseFormTag(false);
        $tb->setId("tb_top");

        if (count($media_items) == 1 && $this->finish_single_upload) {
            $mi = current($media_items);
            ($this->finish_single_upload)($mi["mob_id"]);
            return;
        }

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

    public function initMediaBulkForm(string $a_id, string $a_title) : ilPropertyFormGUI
    {
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

        $media_items = ilMediaItem::getMediaItemsForUploadHash($this->request->getUploadHash());

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
            ($this->on_mob_update)($mob->getId());
        }
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ctrl->returnToParent($this);
    }

    protected function cancel() : void
    {
        $ctrl = $this->ctrl;
        $ctrl->returnToParent($this);
    }

    protected function saveUrl() : void
    {
        $form = $this->initUrlForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->main_tpl->setContent($form->getHTML());
        } else {
            $mob = new ilObjMediaObject();
            $mob->create();

            //handle standard purpose
            $mediaItem = new ilMediaItem();
            $mob->addMediaItem($mediaItem);
            $mediaItem->setPurpose("Standard");

            // determine and create mob directory, move uploaded file to directory
            $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
            if (!is_dir($mob_dir)) {
                $mob->createDirectory();
            }
            $locationType = "Reference";
            $url = $form->getInput("url");
            $url_pi = pathinfo(basename($url));
            $title = str_replace("_", " ", $url_pi["filename"]);

            // get mime type, if not already set!
            $format = ilObjMediaObject::getMimeType($url, true);
            // set real meta and object data
            $mediaItem->setFormat($format);
            $mediaItem->setLocation($url);
            $mediaItem->setLocationType("Reference");
            $mediaItem->setHAlign("Left");
            $mob->setTitle($title);
            try {
                $mob->getExternalMetadata();
            } catch (Exception $e) {
                $this->main_tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                $form->setValuesByPost();
                $this->main_tpl->setContent($form->getHTML());
                return;
            }

            $long_desc = $mob->getLongDescription();
            $mob->update();

            $mob = new ilObjMediaObject($mob->getId());
            $mob->generatePreviewPic(320, 240);

            // duration
            $med_item = $mob->getMediaItem("Standard");
            $med_item->determineDuration();
            $med_item->update();

            //
            // @todo: save usage
            //

            ($this->after_url_saving)($mob->getId(), $long_desc);
        }
    }

    /**
     * Insert media object from pool
     */
    public function listPoolItems() : void
    {
        $ctrl = $this->ctrl;
        $access = $this->access;
        $lng = $this->lng;
        $ui = $this->ui;
        $main_tpl = $this->main_tpl;

        if ($this->requested_mep > 0 &&
            $access->checkAccess("write", "", $this->requested_mep)
            && ilObject::_lookupType(ilObject::_lookupObjId($this->requested_mep)) == "mep") {
            $tb = new ilToolbarGUI();

            // button: select pool
            $tb->addButton(
                $lng->txt("cont_switch_to_media_pool"),
                $ctrl->getLinkTarget($this, "poolSelection")
            );

            // view mode: pool view (folders/all media objects)
            $f = $ui->factory();
            $lng->loadLanguageModule("mep");
            $ctrl->setParameter($this, "pool_view", self::POOL_VIEW_FOLDER);
            $actions[$lng->txt("folders")] = $ctrl->getLinkTarget($this, "listPoolItems");
            $ctrl->setParameter($this, "pool_view", self::POOL_VIEW_ALL);
            $actions[$lng->txt("mep_all_mobs")] = $ctrl->getLinkTarget($this, "listPoolItems");
            $ctrl->setParameter($this, "pool_view", $this->pool_view);
            $aria_label = $lng->txt("cont_change_pool_view");
            $view_control = $f->viewControl()->mode($actions, $aria_label)->withActive(($this->pool_view == self::POOL_VIEW_FOLDER)
                ? $lng->txt("folders") : $lng->txt("mep_all_mobs"));
            $tb->addSeparator();
            $tb->addComponent($view_control);

            $html = $tb->getHTML();

            $pool_table = $this->getPoolTable();

            $html .= $pool_table->getHTML();

            $main_tpl->setContent($html);
        }
    }

    protected function applyFilter() : void
    {
        $mpool_table = $this->getPoolTable();
        $mpool_table->resetOffset();
        $mpool_table->writeFilterToSession();
        $this->ctrl->redirect($this, "listPoolItems");
    }

    protected function resetFilter() : void
    {
        $mpool_table = $this->getPoolTable();
        $mpool_table->resetOffset();
        $mpool_table->resetFilter();
        $this->ctrl->redirect($this, "listPoolItems");
    }

    protected function getPoolTable() : ilMediaPoolTableGUI
    {
        $pool = new ilObjMediaPool($this->requested_mep);
        $mpool_table = new ilMediaPoolTableGUI(
            $this,
            "listPoolItems",
            $pool,
            "mep_folder",
            ilMediaPoolTableGUI::IL_MEP_SELECT,
            $this->pool_view == self::POOL_VIEW_ALL
        );
        $mpool_table->setFilterCommand("applyFilter");
        $mpool_table->setResetCommand("resetFilter");
        $mpool_table->setInsertCommand("insertFromPool");
        return $mpool_table;
    }

    /**
     * Select concrete pool
     */
    public function selectPool() : void
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameter($this, "mep", $this->request->getSelectedMediaPoolRefId());
        $ctrl->redirect($this, "listPoolItems");
    }

    public function poolSelection() : void
    {
        $main_tpl = $this->main_tpl;
        $exp = new ilPoolSelectorGUI(
            $this,
            "poolSelection",
            null,
            "selectPool",
            "",
            "mep_ref_id"
        );
        $exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "mep"));
        $exp->setClickableTypes(array('mep'));
        if (!$exp->handleCommand()) {
            $main_tpl->setContent($exp->getHTML());
        }
    }

    /**
     * Insert media from pool
     */
    protected function insertFromPool() : void
    {
        $ids = $this->request->getIds();
        if (count($ids) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"));
            $this->listPoolItems();
            return;
        }
        $mob_ids = [];
        foreach ($ids as $pool_entry_id) {
            $id = ilMediaPoolItem::lookupForeignId($pool_entry_id);
            $mob = new ilObjMediaObject((int) $id);
            if (!in_array($mob->getMediaItem("Standard")->getFormat(), $this->getMimeTypes())) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("mob_mime_type_not_allowed") . ": " .
                    $mob->getMediaItem("Standard")->getFormat());
                $this->listPoolItems();
                return;
            }
            $mob_ids[] = $id;
        }
        ($this->after_pool_insert)($mob_ids);
    }
}
