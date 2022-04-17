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

/**
 * TableGUI class for recent changes in wiki
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolTableGUI extends ilTable2GUI
{
    public const IL_MEP_SELECT = "select";
    public const IL_MEP_EDIT = "edit";
    public const IL_MEP_SELECT_CONTENT = "selectc";
    public const IL_MEP_SELECT_SINGLE = "selectsingle";

    protected string $mode = "";
    protected array $filter = [];
    protected int $current_folder = 0;
    protected string $folder_par;
    protected ilTree $tree;
    protected ilObjMediaPool $media_pool;
    protected bool $all_objects = false;
    protected \ILIAS\MediaPool\StandardGUIRequest $request;
    protected \ILIAS\MediaPool\Clipboard\ClipboardManager $clipboard_manager;

    protected ilAccessHandler $access;
    protected ilRbacReview $rbacreview;
    protected ilObjUser $user;
    public string $insert_command = "create_mob";
    protected ?ilGlobalTemplateInterface $parent_tpl = null;
    protected ilAdvancedMDRecordGUI $adv_filter_record_gui;
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjMediaPool $a_media_pool,
        string $a_folder_par = "obj_id",
        string $a_mode = ilMediaPoolTableGUI::IL_MEP_EDIT,
        bool $a_all_objects = false,
        ?ilGlobalTemplateInterface $a_parent_tpl = null
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacreview = $DIC->rbac()->review();
        $this->user = $DIC->user();

        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        $this->clipboard_manager = $DIC->mediaPool()
            ->internal()
            ->domain()
            ->clipboard();

        $this->request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();

        if ($a_parent_tpl === null) {
            $a_parent_tpl = $DIC->ui()->mainTemplate();
        }
        $this->parent_tpl = $a_parent_tpl;
        if ($a_all_objects) {
            $this->setId("mepall");
            if (is_object($a_parent_obj->object)) {
                $this->setId("mepall" . $a_parent_obj->object->getId());
            }
        } else {
            $this->setId("mepfold");
            if (is_object($a_parent_obj->object)) {
                $this->setId("mepfold" . $a_parent_obj->object->getId());
            }
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setMode($a_mode);
        $this->all_objects = $a_all_objects;
        $lng->loadLanguageModule("mep");
        
        $this->media_pool = $a_media_pool;
        $this->tree = ilObjMediaPool::_getPoolTree($this->media_pool->getId());
        $this->folder_par = $a_folder_par;
        
        if ($this->all_objects) {
            $this->setExternalSorting(true);
            $this->initFilter();
        }

        $current_folder = $this->clipboard_manager->getFolder();

        // folder determination
        $requested_folder_id = $this->request->getFolderId($this->folder_par);
        if ($requested_folder_id > 0) {
            $this->current_folder = $requested_folder_id;
        } elseif ($current_folder > 0 && $this->tree->isInTree($current_folder)) {
            $this->current_folder = $current_folder;
        } else {
            $this->current_folder = $this->tree->getRootId();
        }
        $this->clipboard_manager->setFolder($this->current_folder);

        // standard columns
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($lng->txt("mep_thumbnail"), "", "100px");
        $this->addColumn($lng->txt("mep_title_and_description"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.mep_list_row.html", "Modules/MediaPool");

        if ($this->showAdvMetadata()) {
            // adv metadata init (adds filter)
            $this->adv_filter_record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_FILTER,
                'mep',
                $this->media_pool->getId(),
                'mob'
            );
            $this->adv_filter_record_gui->setTableGUI($this);
            $this->adv_filter_record_gui->parse();

            // adv metadata columns
            $adv_th_record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_TABLE_HEAD,
                'mep',
                $this->media_pool->getId(),
                'mob'
            );
            $adv_th_record_gui->setTableGUI($this);
            $adv_th_record_gui->parse();
            if ($a_mode === self::IL_MEP_SELECT) {
                $this->setFilterCommand("insert_applyFilter");
                $this->setResetCommand("insert_resetFilter");
            }
        }

        // actions column
        $this->addColumn($lng->txt("actions"));

        // get items
        $this->getItems();

        // title
        if ($a_mode !== self::IL_MEP_EDIT) {
            if ($this->current_folder !== $this->tree->getRootId() && !$this->all_objects) {
                $node = $this->tree->getNodeData($this->current_folder);
                $this->setTitle(
                    $lng->txt("mep_choose_from_folder") . ": " . $node["title"],
                    "icon_fold.svg",
                    $node["title"]
                );
            } else {
                $this->setTitle(
                    $lng->txt("mep_choose_from_mep") . ": " .
                    ilObject::_lookupTitle($this->media_pool->getId()),
                    "icon_mep.svg",
                    ilObject::_lookupTitle($this->media_pool->getId())
                );
            }
        }
        
        // action commands
        if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
            $this->getMode() === self::IL_MEP_EDIT) {
            $this->addMultiCommand("copyToClipboard", $lng->txt("cont_copy_to_clipboard"));
            $this->addMultiCommand("move", $lng->txt("move"));
            $this->addMultiCommand("confirmRemove", $lng->txt("remove"));
        }

        if ($this->getMode() === self::IL_MEP_SELECT_SINGLE) {
            // ... even more coupling with ilpcmediaobjectgui
            $this->addMultiCommand("selectObjectReference", $lng->txt("cont_select"));
        }
        
        if ($this->getMode() === self::IL_MEP_EDIT &&
            $ilAccess->checkAccess("write", "", $this->media_pool->getRefId())) {
            $this->setSelectAllCheckbox("id");
        }
    }

    protected function showAdvMetadata() : bool
    {
        return ($this->all_objects);
    }

    protected function getAdvMDRecordGUI() : ilAdvancedMDRecordGUI
    {
        return $this->adv_filter_record_gui;
    }

    public function setInsertCommand(string $a_val) : void
    {
        $this->insert_command = $a_val;
    }
    
    public function getInsertCommand() : string
    {
        return $this->insert_command;
    }

    public function setTitleFilter(string $title) : void
    {
        // activate filter
        $tprop = new ilTablePropertiesStorage();
        $tprop->storeProperty(
            $this->getId(),
            $this->user->getId(),
            'filter',
            1
        );

        // reset filter and offset
        $this->resetFilter();
        $this->resetOffset();

        // set title input and write it to session
        /** @var ilTextInputGUI $input */
        $input = $this->getFilterItemByPostVar("title");
        $input->setValue($title);
        $input->writeToSession();
    }

    public function getHTML() : string
    {
        $html = parent::getHTML();
        $html .= ilObjMediaPoolGUI::getPreviewModalHTML($this->media_pool->getRefId(), $this->parent_tpl);
        return $html;
    }
    
    public function initFilter() : void
    {
        $lng = $this->lng;

        // title/description
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();
        
        // keyword
        $GLOBALS['lng']->loadLanguageModule('meta');
        $ke = new ilTextInputGUI($lng->txt('meta_keyword'), 'keyword');
        $ke->setMaxLength(64);
        $ke->setSize(20);
        $this->addFilterItem($ke);
        $ke->readFromSession();
        $this->filter['keyword'] = $ke->getValue();
        
        // Caption
        $ca = new ilTextInputGUI($lng->txt('cont_caption'), 'caption');
        $ca->setMaxLength(64);
        $ca->setSize(20);
        $this->addFilterItem($ca);
        $ca->readFromSession();
        $this->filter['caption'] = $ca->getValue();
        
        // format
        $options = array(
            "" => $lng->txt("mep_all"),
            );
        $formats = $this->media_pool->getUsedFormats();
        $options = array_merge($options, $formats);
        $si = new ilSelectInputGUI($this->lng->txt("mep_format"), "format");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["format"] = $si->getValue();
    }

    public function setMode(string $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : string
    {
        return $this->mode;
    }

    public function getItems() : void
    {
        if (!$this->all_objects) {
            $fobjs = $this->media_pool->getChilds($this->current_folder, "fold");
            $f2objs = array();
            foreach ($fobjs as $obj) {
                $f2objs[$obj["title"] . ":" . $obj["child"]] = $obj;
            }
            ksort($f2objs);
            
            // get current media objects / pages
            if ($this->getMode() === self::IL_MEP_SELECT) {
                $mobjs = $this->media_pool->getChilds($this->current_folder, "mob");
            } elseif ($this->getMode() === self::IL_MEP_SELECT_CONTENT) {
                $mobjs = $this->media_pool->getChilds($this->current_folder, "pg");
            } else {
                $mobjs = $this->media_pool->getChildsExceptFolders($this->current_folder);
            }
            $m2objs = array();
            foreach ($mobjs as $obj) {
                $m2objs[$obj["title"] . ":" . $obj["child"]] = $obj;
            }
            ksort($m2objs);
            
            // merge everything together
            $objs = array_merge($f2objs, $m2objs);
        } else {
            $objs = $this->media_pool->getMediaObjects(
                $this->filter["title"],
                $this->filter["format"],
                $this->filter['keyword'],
                $this->filter['caption']
            );
        }

        // add advanced metadata
        if ($this->showAdvMetadata()) {
            $objs = ilAdvancedMDValues::queryForRecords(
                $this->media_pool->getRefId(),
                "mep",
                "mob",
                [0],
                "mob",
                $objs,
                "",
                "foreign_id",
                $this->adv_filter_record_gui->getFilterElements()
            );
        }
        $this->setData($objs);
    }

    protected function prepareOutput() : void
    {
        $lng = $this->lng;
        
        if ($this->getMode() === self::IL_MEP_SELECT ||
            $this->getMode() === self::IL_MEP_SELECT_CONTENT) {
            $this->addMultiCommand($this->getInsertCommand(), $lng->txt("insert"));
            $this->addCommandButton("cancelCreate", $lng->txt("cancel"));
        }
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $this->tpl->setCurrentBlock("link");

        // adv metadata columns
        if ($this->showAdvMetadata()) {
            $adv_cell_record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_TABLE_CELLS,
                'mep',
                $this->media_pool->getId(),
                'mob'
            );
            $adv_cell_record_gui->setTableGUI($this);
            $adv_cell_record_gui->setRowData($a_set);
            $this->tpl->setVariable("ADV_CELLS", $adv_cell_record_gui->parse());
        }


        switch ($a_set["type"]) {
            case "fold":
                $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
                $ilCtrl->setParameter($this->parent_obj, $this->folder_par, $a_set["obj_id"]);
                $this->tpl->setVariable(
                    "LINK_VIEW",
                    $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd)
                );
                $this->tpl->parseCurrentBlock();
                
                if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
                    $this->getMode() === self::IL_MEP_EDIT) {
                    $this->tpl->setCurrentBlock("edit");
                    $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
                    $ilCtrl->setParameter($this->parent_obj, $this->folder_par, $a_set["obj_id"]);
                    $this->tpl->setVariable(
                        "EDIT_LINK",
                        $ilCtrl->getLinkTarget($this->parent_obj, "editFolder")
                    );
                    $ilCtrl->setParameter(
                        $this->parent_obj,
                        $this->folder_par,
                        $this->request->getFolderId($this->folder_par)
                    );
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_" . $a_set["type"] . ".svg")));
                $ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
                break;

            case "pg":
                if ($this->getMode() === self::IL_MEP_SELECT ||
                    $this->getMode() === self::IL_MEP_SELECT_SINGLE) {
                    $this->tpl->setVariable("TXT_NO_LINK_TITLE", $a_set["title"]);
                } else {
                    $this->tpl->setVariable("ONCLICK", "il.MediaPool.preview('" . $a_set["child"] . "'); return false;");
                    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
                    $ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $a_set["child"]);
                }
                
                if ($this->getMode() === self::IL_MEP_EDIT &&
                    $ilAccess->checkAccess("write", "", $this->media_pool->getRefId())) {
                    $this->tpl->setCurrentBlock("edit");
                    $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
                    $ilCtrl->setParameterByClass("ilmediapoolpagegui", "mepitem_id", $a_set["child"]);
                    $this->tpl->setVariable(
                        "EDIT_LINK",
                        $ilCtrl->getLinkTargetByClass("ilmediapoolpagegui", "edit")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_pg.svg")));
                $ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
                break;

            case "mob":
                $this->tpl->setVariable("ONCLICK", "il.MediaPool.preview('" . $a_set["child"] . "'); return false;");
                $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
                $ilCtrl->setParameterByClass("ilobjmediaobjectgui", "mepitem_id", $a_set["child"]);
                $ilCtrl->setParameter($this->parent_obj, "mob_id", $a_set["foreign_id"]);

                // edit link
                if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
                    $this->getMode() === self::IL_MEP_EDIT) {
                    $this->tpl->setCurrentBlock("edit");
                    $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
                    $this->tpl->setVariable(
                        "EDIT_LINK",
                        $ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "edit")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("link");
                $this->tpl->setCurrentBlock("tbl_content");
                
                // output thumbnail (or mob icon)
                if (ilObject::_lookupType($a_set["foreign_id"]) === "mob") {
                    $mob = new ilObjMediaObject($a_set["foreign_id"]);
                    $med = $mob->getMediaItem("Standard");
                    $target = "";

                    // thumbnail picture
                    if ($med) {
                        $target = $med->getThumbnailTarget();
                    }

                    // video preview
                    if ($target === "") {
                        $target = $mob->getVideoPreviewPic();
                    }

                    if ($target !== "") {
                        $this->tpl->setVariable("IMG", ilUtil::img(ilWACSignedPath::signFile($target)));
                    } else {
                        $this->tpl->setVariable(
                            "IMG",
                            ilUtil::img(ilUtil::getImagePath("icon_" . $a_set["type"] . ".svg"))
                        );
                    }
                    if ($med && ilUtil::deducibleSize($med->getFormat()) &&
                        $med->getLocationType() === "Reference") {
                        $size = getimagesize($med->getLocation());
                        if ($size[0] > 0 && $size[1] > 0) {
                            $wr = $size[0] / 80;
                            $hr = $size[1] / 80;
                            $r = max($wr, $hr);
                            $w = (int) ($size[0] / $r);
                            $h = (int) ($size[1] / $r);
                            $this->tpl->setVariable(
                                "IMG",
                                ilUtil::img($med->getLocation(), "", $w, $h)
                            );
                        }
                    }
                    
                    // output media info
                    $this->tpl->setVariable(
                        "MEDIA_INFO",
                        ilObjMediaObjectGUI::_getMediaInfoHTML($mob)
                    );
                    $ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
                }
                break;
        }

        if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId())) {
            if ((
                $this->getMode() === self::IL_MEP_EDIT ||
                    ($this->getMode() === self::IL_MEP_SELECT && $a_set["type"] === "mob") ||
                    ($this->getMode() === self::IL_MEP_SELECT_CONTENT && $a_set["type"] === "pg")
            )) {
                $this->tpl->setCurrentBlock("chbox");
                $this->tpl->setVariable("CHECKBOX_ID", $a_set["child"]);
                $this->tpl->parseCurrentBlock();
                $this->tpl->setCurrentBlock("tbl_content");
            } elseif ($this->getMode() === self::IL_MEP_SELECT_SINGLE && $a_set["type"] === "mob") {
                $this->tpl->setCurrentBlock("radio");
                $this->tpl->setVariable("RADIO_ID", $a_set["child"]);
                $this->tpl->parseCurrentBlock();
                $this->tpl->setCurrentBlock("tbl_content");
            }
        }
    }

    public function render() : string
    {
        $ilCtrl = $this->ctrl;

        $mtpl = new ilTemplate("tpl.media_sel_table.html", true, true, "Modules/MediaPool");
        
        $pre = "";
        if ($this->current_folder !== $this->tree->getRootId() && !$this->all_objects) {
            $path = $this->tree->getPathFull($this->current_folder);

            $loc = new ilLocatorGUI();
            foreach ($path as $p) {
                $ilCtrl->setParameter($this->parent_obj, $this->folder_par, $p["child"]);
                $title = $p["title"];
                if ($this->tree->getRootId() == $p["child"]) {
                    $title = ilObject::_lookupTitle($this->media_pool->getId());
                }
                $loc->addItem(
                    $title,
                    $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd)
                );
            }
            $ilCtrl->setParameter(
                $this->parent_obj,
                $this->folder_par,
                $this->current_folder
            );
            
            $mtpl->setCurrentBlock("loc");
            $mtpl->setVariable("LOC", $loc->getHTML());
            $mtpl->parseCurrentBlock();
        }

        $mtpl->setVariable("TABLE", parent::render());
        
        return $mtpl->get();
    }
}
