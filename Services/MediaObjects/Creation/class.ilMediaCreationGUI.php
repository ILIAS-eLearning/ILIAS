<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilMediaCreationGUI: ilPropertyFormGUI
 */
class ilMediaCreationGUI
{
    const TYPE_VIDEO = 1;
    const TYPE_AUDIO = 2;
    const TYPE_IMAGE = 3;
    const TYPE_OTHER = 4;
    const TYPE_ALL = 5;

    const POOL_VIEW_FOLDER = "fold";
    const POOL_VIEW_ALL = "all";

    protected $accept_types = [1,2,3,4];

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilTemplate
     */
    protected $main_tpl;

    /**
     * @var closure
     */
    protected $after_upload;

    /**
     * @var closure
     */
    protected $after_url_saving;

    /**
     * @var closure
     */
    protected $after_pool_insert;

    /**
     * @var \ilAccessHandler
     */
    protected $access;

    protected $all_suffixes = [];
    protected $all_mime_types = [];

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var int
     */
    protected $requested_mep;

    /**
     * @var string
     */
    protected $pool_view = self::POOL_VIEW_FOLDER;

    /**
     * Constructor
     */
    public function __construct(
        array $accept_types,
        closure $after_upload,
        closure $after_url_saving,
        closure $after_pool_insert)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("mob");
        $this->lng->loadLanguageModule("content");
        $this->access = $DIC->access();

        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui = $DIC->ui();

        $this->accept_types = $accept_types;
        $this->after_upload = $after_upload;
        $this->after_url_saving = $after_url_saving;
        $this->after_pool_insert = $after_pool_insert;

        $this->ctrl->saveParameter($this, ["mep", "pool_view"]);

        $this->requested_mep = ((int) $_POST["mep"] > 0)
            ? (int) $_POST["mep"]
            : (int) $_GET["mep"];

        $this->pool_view = (in_array($_GET["pool_view"], [self::POOL_VIEW_FOLDER, self::POOL_VIEW_ALL]))
            ? $_GET["pool_view"]
            : self::POOL_VIEW_FOLDER;
    }

    /**
     * Set All suffixes
     * @param array $a_val 
     */
    function setAllSuffixes($a_val)
    {
        $this->all_suffixes = $a_val;
    }

    /**
     * Get All suffixes
     * @return array 
     */
    function getAllSuffixes()
    {
        return $this->all_suffixes;
    }

    /**
     * Set All mime types
     * @param array $a_val
     */
    function setAllMimeTypes($a_val)
    {
        $this->all_mime_types = $a_val;
    }

    /**
     * Get All mime types
     * @return array
     */
    function getAllMimeTypes()
    {
        return $this->all_mime_types;
    }


    /**
     * Get suffixes
     * @return array
     */
    protected function getSuffixes()
    {
        $suffixes = [];
        if (in_array(self::TYPE_ALL, $this->accept_types)) {
            $suffixes = $this->getAllSuffixes();
        }
        if (in_array(self::TYPE_VIDEO, $this->accept_types)) {
            $suffixes[] = "mp4";
        }
        return $suffixes;
    }

    /**
     * Get mimes
     * @return array
     */
    protected function getMimeTypes()
    {
        $mimes = [];
        if (in_array(self::TYPE_ALL, $this->accept_types)) {
            $mimes = $this->getAllMimeTypes();
        }
        if (in_array(self::TYPE_VIDEO, $this->accept_types)) {
            $mimes[] = "video/vimeo";
            $mimes[] = "video/mp4";
        }
        return $mimes;
    }

    /**
     * Execute command
     */
    function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("creationSelection");

        switch ($next_class) {

            case "ilpropertyformgui":
                $form = $this->initPoolSelection();
                $ctrl->forwardCommand($form);
                break;

            default:
                if (in_array($cmd, ["creationSelection", "uploadFile", "saveUrl", "cancel", "listPoolItems",
                                    "insertFromPool", "poolSelection", "selectPool", "applyFilter", "resetFilter"])) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Creation selection
     */
    protected function creationSelection()
    {
        $main_tpl = $this->main_tpl;

        $acc = new \ilAccordionGUI();
        $acc->setBehaviour(\ilAccordionGUI::FIRST_OPEN);
        $cnt = 1;
        $forms = [
            $this->initUploadForm(),
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
            $cf->setTitle(null);
            $cf->setTitleIcon(null);
            $cf->setTableWidth("100%");

            $acc->addItem($htpl->get(), $cf->getHTML());

            $cnt++;
        }
        $main_tpl->setContent($acc->getHTML());
    }

    /**
     * Init upload form.
     */
    public function initUploadForm()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();

        $fi = new \ilFileInputGUI($lng->txt("file"), "file");
        $fi->setSuffixes($this->getSuffixes());
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("uploadFile", $lng->txt("upload"));
        $form->addCommandButton("cancel", $lng->txt("cancel"));

        $form->setTitle($lng->txt("mob_upload_file"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Init url form.
     */
    public function initUrlForm()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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

    /**
     * Init pool selectio form.
     */
    public function initPoolSelection()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();

        // mediacast
        $mcst = new ilRepositorySelector2InputGUI($lng->txt("obj_mep"), "mep", false);
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

    /**
     * Upload file
     */
    protected function uploadFile()
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
            $file_name = ilUtil::getASCIIFilename($_FILES['file']["name"]);
            $file_name = str_replace(" ", "_", $file_name);

            $file = $mob_dir . "/" . $file_name;
            $title = $file_name;
            $locationType = "LocalFile";
            $location = $title;
            ilUtil::moveUploadedFile($_FILES['file']['tmp_name'], $file_name, $file);
            ilUtil::renameExecutables($mob_dir);

            // get mime type, if not already set!
            $format = ilObjMediaObject::getMimeType($file, false);

            // set real meta and object data
            $mediaItem->setFormat($format);
            $mediaItem->setLocation($location);
            $mediaItem->setLocationType($locationType);
            $mediaItem->setHAlign("Left");
            $mob->setTitle($title);
            $mob->setDescription($format);

            $mob->update();

            $mob = new ilObjMediaObject($mob->getId());
            $mob->generatePreviewPic(320, 240);

            //
            // @todo: save usage
            //

            ($this->after_upload)($mob->getId());
        }

    }

    /**
     * Cancel
     */
    protected function cancel()
    {
        $ctrl = $this->ctrl;
        $ctrl->returnToParent($this);
    }

    /**
     * Save url
     */
    protected function saveUrl()
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
            $title = $url;

            // get mime type, if not already set!
            $format = ilObjMediaObject::getMimeType($url, true);

            // set real meta and object data
            $mediaItem->setFormat($format);
            $mediaItem->setLocation($url);
            $mediaItem->setLocationType("Reference");
            $mediaItem->setHAlign("Left");
            $mob->setTitle($title);
            $mob->setDescription($format);
            /*try {
                $mob->getExternalMetadata();
            } catch (Exception $e) {
                ilUtil::sendFailure($e->getMessage());
                $form->setValuesByPost();
                $this->main_tpl->setContent($form->getHTML());
                return;
            }*/

            $long_desc = $mob->getLongDescription();
            $mob->update();

            //
            // @todo: save usage
            //

            ($this->after_url_saving)($mob->getId(), $long_desc);
        }
    }

    /**
     * Insert media object from pool
     */
    public function listPoolItems()
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

    /**
     * Apply filter
     */
    protected function applyFilter()
    {
        $mpool_table = $this->getPoolTable();
        $mpool_table->resetOffset();
        $mpool_table->writeFilterToSession();
        $this->ctrl->redirect($this, "listPoolItems");
    }

    /**
     * Reset filter
     */
    protected function resetFilter()
    {
        $mpool_table = $this->getPoolTable();
        $mpool_table->resetOffset();
        $mpool_table->resetFilter();
        $this->ctrl->redirect($this, "listPoolItems");
    }

    /**
     * Get pool table
     * @param
     * @return
     */
    protected function getPoolTable()
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
    public function selectPool()
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameter($this, "mep", $_GET["mep_ref_id"]);
        $ctrl->redirect($this, "listPoolItems");
    }

    /**
     * Pool Selection
     */
    public function poolSelection()
    {
        $main_tpl = $this->main_tpl;
        $exp = new ilPoolSelectorGUI($this, "poolSelection", null,
            "selectPool", "", "mep_ref_id");
        $exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "mep"));
        $exp->setClickableTypes(array('mep'));
        if (!$exp->handleCommand()) {
            $main_tpl->setContent($exp->getHTML());
        }
    }

    /**
     * Insert media from pool
     */
    protected function insertFromPool()
    {
        if (!is_array($_POST["id"])) {
            ilUtil::sendFailure($this->lng->txt("select_one"));
            $this->listPoolItems();
            return;
        }
        $mob_ids = [];
        foreach ($_POST["id"] as $pool_entry_id) {
            $id = ilMediaPoolItem::lookupForeignId($pool_entry_id);
            $mob = new ilObjMediaObject((int) $id);
            if (!in_array($mob->getMediaItem("Standard")->getFormat(), $this->getMimeTypes())) {
                ilUtil::sendFailure($this->lng->txt("mob_mime_type_not_allowed").": ".
                    $mob->getMediaItem("Standard")->getFormat());
                $this->listPoolItems();
                return;
            }
            $mob_ids[] = $id;
        }
        ($this->after_pool_insert)($mob_ids);
    }

}