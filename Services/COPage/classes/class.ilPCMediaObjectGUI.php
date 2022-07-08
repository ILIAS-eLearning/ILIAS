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
 * Editing User Interface for MediaObjects within LMs (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCMediaObjectGUI: ilObjMediaObjectGUI, ilPCImageMapEditorGUI
 */
class ilPCMediaObjectGUI extends ilPageContentGUI
{
    protected ilPropertyFormGUI $form;
    protected ilPropertyFormGUI $form_gui;
    protected string $page_back_title = "";
    protected bool $enabledmapareas;
    protected ilTabsGUI $tabs;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected \ILIAS\DI\UIServices $ui;
    protected string $pool_view;
    public string $header;
    protected string $sub_cmd;
    protected \ILIAS\MediaObjects\MediaType\MediaType $media_type;

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
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        $request = $DIC
            ->copage()
            ->internal()
            ->gui()
            ->pc()
            ->editRequest();

        $this->media_type = new ILIAS\MediaObjects\MediaType\MediaType();

        $pc_id = $request->getPCId();
        if ($pc_id != "" && $a_hier_id == "") {
            $hier_ids = $a_pg_obj->getHierIdsForPCIds([$pc_id]);
            $a_hier_id = $hier_ids[$pc_id];
            $ilCtrl->setParameter($this, "hier_id", $a_hier_id);
        }

        $this->pool_view = "folder";

        $pv = $request->getString("pool_view");
        if (in_array($pv, array("folder", "all"))) {
            $this->pool_view = $pv;
        }

        $this->ctrl = $ilCtrl;

        $this->ctrl->saveParameter($this, ["pool_view", "pcid"]);

        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        
        $this->setCharacteristics(self::_getStandardCharacteristics());
    }

    /**
     * Set table sub command
     */
    public function setSubCmd(string $a_val) : void
    {
        $this->sub_cmd = $a_val;
    }

    public function getSubCmd() : string
    {
        return $this->sub_cmd;
    }


    public function setHeader(string $a_title = "") : void
    {
        $this->header = $a_title;
    }

    public function getHeader() : string
    {
        return $this->header;
    }

    /**
     * Set Enable map areas.
     */
    public function setEnabledMapAreas(bool $a_enabledmapareas) : void
    {
        $this->enabledmapareas = $a_enabledmapareas;
    }

    public function getEnabledMapAreas() : bool
    {
        return $this->enabledmapareas;
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $this->getCharacteristicsOfCurrentStyle(["media_cont"]);	// scorm-2004
        
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();
        if (is_object($this->content_obj)) {
            //$this->tpl->clearHeader();
            $tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
            $this->getTabs();

            $mob = $this->content_obj->getMediaObject();
            if (is_object($mob)) {
                $tpl->setTitle($lng->txt("mob") . ": " .
                    $this->content_obj->getMediaObject()->getTitle());
                $mob_gui = new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(), false, false);
                $mob_gui->setBackTitle($this->page_back_title);
                $mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
                $mob_gui->getTabs();
            }
        }

        switch ($next_class) {
            case "ilobjmediaobjectgui":
                //$this->tpl->clearHeader();
                $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
                $this->tpl->setTitle($this->lng->txt("mob") . ": " .
                    $this->content_obj->getMediaObject()->getTitle());
                $mob_gui = new ilObjMediaObjectGUI("", $this->content_obj->getMediaObject()->getId(), false, false);
                $mob_gui->setBackTitle($this->page_back_title);
                $mob_gui->setEnabledMapAreas($this->getEnabledMapAreas());
                $ret = $this->ctrl->forwardCommand($mob_gui);
                break;

            // instance image map editing
            case "ilpcimagemapeditorgui":
                $ilTabs->setTabActive("cont_inst_map_areas");
                /** @var ilPCMediaObject $pc_med */
                $pc_med = $this->content_obj;
                $image_map_edit = new ilPCImageMapEditorGUI(
                    $pc_med,
                    $this->pg_obj,
                    $this->request
                );
                $ret = $this->ctrl->forwardCommand($image_map_edit);
                $tpl->setContent($ret);
                $this->checkFixSize();
                break;
            
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    public function insert(
        $a_post_cmd = "edpost",
        $a_submit_cmd = "create_mob",
        $a_input_error = false
    ) : void {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $sub_command = $this->sub_command;

        if (in_array($sub_command, ["insertNew", "insertFromPool"])) {
            $this->edit_repo->setSubCmd($sub_command);
        }

        if (($sub_command == "") && $this->edit_repo->getSubCmd() != "") {
            $sub_command = $this->edit_repo->getSubCmd();
        }

        switch ($sub_command) {
            case "insertFromPool":
                $this->insertFromPool();
                break;

            case "poolSelection":
                $this->poolSelection();
                break;

            case "selectPool":
                $this->selectPool();
                break;
            
            case "insertNew":
            default:
                $this->getTabs(true);
                $ilTabs->setSubTabActive("cont_new_mob");
                
                if ($a_input_error) {
                    $form = $this->form;
                } else {
                    $mob_gui = new ilObjMediaObjectGUI("");
                    $mob_gui->initForm("create");
                    $form = $mob_gui->getForm();
                }
                $form->setFormAction($ilCtrl->getFormAction($this, "create_mob"));
                $form->clearCommandButtons();
                $form->addCommandButton("create_mob", $lng->txt("save"));
                $form->addCommandButton("cancelCreate", $lng->txt("cancel"));

                $this->displayValidationError();
                
                $tpl->setContent($form->getHTML());

                break;
        }
    }

    /**
     * Change object reference
     */
    public function changeObjectReference() : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $sub_command = $this->sub_command;

        if (in_array($sub_command, ["insertNew", "insertFromPool"])) {
            $this->edit_repo->setSubCmd($sub_command);
        }

        if (($sub_command == "") && $this->edit_repo->getSubCmd() != "") {
            $sub_command = $this->edit_repo->getSubCmd();
        }

        switch ($sub_command) {
            case "insertFromPool":
                $this->insertFromPool(true);
                break;

            case "poolSelection":
                $this->poolSelection(true);
                break;

            case "selectPool":
                $this->selectPool(true);
                break;
            
            case "insertNew":
            default:
                $ilCtrl->setParameter($this, "subCmd", "changeObjectReference");
                $this->getTabs(true, true);
                $ilTabs->setSubTabActive("cont_new_mob");
        
                $this->displayValidationError();
                        
                $mob_gui = new ilObjMediaObjectGUI("");
                $mob_gui->initForm("create");
                $form = $mob_gui->getForm();
                $form->setFormAction($ilCtrl->getFormAction($this));
                $form->clearCommandButtons();
                $form->addCommandButton("createNewObjectReference", $lng->txt("save"));
                $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
                $this->tpl->setContent($form->getHTML());
        }
    }

    protected function checkFixSize() : void
    {
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

        $ok = false;
        if (($std_alias_item->getWidth() != "" && $std_alias_item->getHeight() != "")) {
            $ok = true;
        }
        if ($std_alias_item->getWidth() == "" && $std_alias_item->getHeight() == ""
            && $std_item->getWidth() != "" && $std_item->getHeight() != "") {
            $ok = true;
        }

        if (!$ok) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("mob_no_fixed_size_map_editing"));
        }
    }

    /**
     * Insert media object from pool
     */
    public function insertFromPool(bool $a_change_obj_ref = false) : void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ui = $this->ui;

        if ($this->edit_repo->getMediaPool() > 0 &&
            $ilAccess->checkAccess("write", "", $this->edit_repo->getMediaPool())
            && ilObject::_lookupType(ilObject::_lookupObjId($this->edit_repo->getMediaPool())) == "mep") {
            $html = "";
            $tb = new ilToolbarGUI();

            // button: select pool
            $ilCtrl->setParameter($this, "subCmd", "poolSelection");
            if ($a_change_obj_ref) {
                $tb->addButton(
                    $lng->txt("cont_switch_to_media_pool"),
                    $ilCtrl->getLinkTarget($this, "changeObjectReference")
                );
            } else {
                $tb->addButton(
                    $lng->txt("cont_switch_to_media_pool"),
                    $ilCtrl->getLinkTarget($this, "insert")
                );
            }
            $ilCtrl->setParameter($this, "subCmd", "");

            // view mode: pool view (folders/all media objects)
            $f = $ui->factory();
            $tcmd = ($a_change_obj_ref)
                ? "changeObjectReference"
                : "insert";
            $lng->loadLanguageModule("mep");
            $ilCtrl->setParameter($this, "pool_view", "folder");
            $actions[$lng->txt("folders")] = $ilCtrl->getLinkTarget($this, $tcmd);
            $ilCtrl->setParameter($this, "pool_view", "all");
            $actions[$lng->txt("mep_all_mobs")] = $ilCtrl->getLinkTarget($this, $tcmd);
            $ilCtrl->setParameter($this, "pool_view", $this->pool_view);
            $aria_label = $lng->txt("cont_change_pool_view");
            $view_control = $f->viewControl()->mode($actions, $aria_label)->withActive(($this->pool_view == "folder")
                ? $lng->txt("folders") : $lng->txt("mep_all_mobs"));
            $tb->addSeparator();
            $tb->addComponent($view_control);

            $html = $tb->getHTML();

            $this->getTabs(true, $a_change_obj_ref);
            $ilTabs->setSubTabActive("cont_mob_from_media_pool");

            $pool = new ilObjMediaPool($this->edit_repo->getMediaPool());
            $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
            $tcmd = ($a_change_obj_ref)
                ? "changeObjectReference"
                : "insert";
            $tmode = ($a_change_obj_ref)
                ? ilMediaPoolTableGUI::IL_MEP_SELECT_SINGLE
                : ilMediaPoolTableGUI::IL_MEP_SELECT;

            // handle table sub commands and get the table
            if ($this->getSubCmd() == "applyFilter") {
                $mpool_table = new ilMediaPoolTableGUI(
                    $this,
                    $tcmd,
                    $pool,
                    "mep_folder",
                    $tmode,
                    $this->pool_view == "all"
                );
                $mpool_table->resetOffset();
                $mpool_table->writeFilterToSession();
            }
            if ($this->getSubCmd() == "resetFilter") {
                $mpool_table = new ilMediaPoolTableGUI(
                    $this,
                    $tcmd,
                    $pool,
                    "mep_folder",
                    $tmode,
                    $this->pool_view == "all"
                );
                $mpool_table->resetOffset();
                $mpool_table->resetFilter();
            }
            $mpool_table = new ilMediaPoolTableGUI(
                $this,
                $tcmd,
                $pool,
                "mep_folder",
                $tmode,
                $this->pool_view == "all"
            );

            $html .= $mpool_table->getHTML();

            $tpl->setContent($html);
        } else {
            $this->poolSelection($a_change_obj_ref);
        }
    }
    
    /**
     * Select concrete pool
     */
    public function selectPool(
        bool $a_change_obj_ref = false
    ) : void {
        $ilCtrl = $this->ctrl;
        
        $this->edit_repo->setMediaPool($this->request->getInt("pool_ref_id"));
        $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
        if ($a_change_obj_ref) {
            $ilCtrl->redirect($this, "changeObjectReference");
        } else {
            $ilCtrl->redirect($this, "insert");
        }
    }
    
    /**
     * Pool Selection
     */
    public function poolSelection(
        bool $a_change_obj_ref = false
    ) : void {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $this->getTabs(true, $a_change_obj_ref);
        $ilTabs->setSubTabActive("cont_mob_from_media_pool");

        $ilCtrl->setParameter($this, "subCmd", "poolSelection");
        if ($a_change_obj_ref) {
            $exp = new ilPoolSelectorGUI($this, "changeObjectReference", $this, "changeObjectReference");
        } else {
            $exp = new ilPoolSelectorGUI($this, "insert");
        }

        // filter
        $exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "mep"));
        $exp->setClickableTypes(array('mep'));

        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }

    
    /**
     * Create new media object and replace currrent media item with it.
     * (keep all instance parameters)
     */
    public function createNewObjectReference() : void
    {
        $this->create(false, true);
    }

    /**
     * Create new media object and replace currrent media item with it.
     * (keep all instance parameters)
     */
    public function selectObjectReference() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ids = $this->request->getIntArray("id");
        if (count($ids) == 1) {
            $fid = ilMediaPoolItem::lookupForeignId($ids[0]);
            $this->content_obj->readMediaObject($fid);
            $this->content_obj->updateObjectReference();
            $this->updated = $this->pg_obj->update();
        } else {
            $this->tpl->setOnScreenMessage('info', $lng->txt("cont_select_max_one_item"), true);
            $ilCtrl->redirect($this, "changeObjectReference");
        }
        $ilCtrl->redirect($this, "editAlias");
    }

    /**
     * create new media object in dom and update page in db
     */
    public function create(
        bool $a_create_alias = true,
        bool $a_change_obj_ref = false
    ) : ?ilPCMediaObject {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if ($this->sub_command == "insertFromPool") {
            $ids = $this->request->getIntArray("id");
            for ($i = count($ids) - 1; $i >= 0; $i--) {
                $fid = ilMediaPoolItem::lookupForeignId($ids[$i]);
                $this->content_obj = new ilPCMediaObject($this->getPage());
                $this->content_obj->readMediaObject($fid);
                $this->content_obj->createAlias(
                    $this->pg_obj,
                    $this->request->getHierId(),
                    $this->pc_id
                );
            }
            $this->updated = $this->pg_obj->update();

            $this->redirectToParent($this->request->getHierId());
        }
        
        // check form input
        $mob_gui = new ilObjMediaObjectGUI("");
        $mob_gui->initForm("create");

        if (!$mob_gui->checkFormInput()) {
            $this->form = $mob_gui->getForm();
            $this->insert("edpost", "create_mob", true);
            return null;
        }
        // create dummy object in db (we need an id)
        if ($a_change_obj_ref != true) {
            $this->content_obj = new ilPCMediaObject($this->getPage());
        }
        $this->content_obj->createMediaObject();
        $media_obj = $this->content_obj->getMediaObject();

        $mob_gui->setObjectPerCreationForm($media_obj);

        if ($a_create_alias) {
            // need a pcmediaobject here
            //$this->node = $this->createPageContentNode();
            
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
                $this->tpl->setOnScreenMessage('success', $lng->txt("saved_media_object"), true);
                $this->ctrl->redirectByClass("ilobjmediaobjectgui", "edit");

            //$this->ctrl->returnToParent($this, "jump".$this->hier_id);
            } else {
                $this->insert();
            }
        } else {
            if ($a_change_obj_ref == true) {
                $this->content_obj->updateObjectReference();
                $this->updated = $this->pg_obj->update();
                $this->ctrl->redirect($this, "editAlias");
            }
            return $this->content_obj;
        }
        return null;
    }


    /**
     * edit properties form
     */
    public function edit() : void
    {
        if ($this->content_obj->checkInstanceEditing()) {
            $this->ctrl->redirect($this, "editAlias");
        }
        $this->ctrl->redirectByClass("ilobjmediaobjectgui", "edit");
    }

    public function editAlias() : void
    {
        $tpl = $this->tpl;
        
        $this->initAliasForm();
        $this->getAliasValues();
        $tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * Init alias form
     */
    public function initAliasForm() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->form_gui = new ilPropertyFormGUI();

        // standard view resource
        $std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

        // title, location and format
        $title = new ilNonEditableValueGUI($lng->txt("title"), "title");
        $this->form_gui->addItem($title);
        $loc = new ilNonEditableValueGUI(
            $this->lng->txt("cont_" . strtolower($std_item->getLocationType())),
            "st_location"
        );
        $this->form_gui->addItem($loc);
        $format = new ilNonEditableValueGUI(
            $this->lng->txt("cont_format"),
            "st_format"
        );
        $this->form_gui->addItem($format);

        // standard size
        $radio_size = new ilRadioGroupInputGUI($lng->txt("size"), "st_derive_size");
        $orig_size = $std_item->getOriginalSize();
        $add_str = (!is_null($orig_size))
            ? " (" . $orig_size["width"] . " x " . $orig_size["height"] . ")"
            : "";
        $op1 = new ilRadioOption($lng->txt("cont_default") . $add_str, "y");
        $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
        $radio_size->addOption($op1);
        
        // width height
        $width_height = new ilWidthHeightInputGUI($lng->txt("cont_width") .
                " / " . $lng->txt("cont_height"), "st_width_height");
        $width_height->setConstrainProportions(true);
        $op2->addSubItem($width_height);

        $radio_size->addOption($op2);
        $this->form_gui->addItem($radio_size);
        
        // standard caption
        $rad_caption = new ilRadioGroupInputGUI($lng->txt("cont_caption"), "st_derive_caption");
        $op1 = new ilRadioOption($lng->txt("cont_default"), "y");
        $def_cap = new ilNonEditableValueGUI("", "def_caption");
        $op1->addSubItem($def_cap);
        $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
        $rad_caption->addOption($op1);

        $caption = new ilTextAreaInputGUI("", "st_caption");
        $caption->setCols(30);
        $caption->setRows(2);
        $op2->addSubItem($caption);

        /*$caption = new ilTextInputGUI("", "st_caption");
        $caption->setSize(40);
        $caption->setMaxLength(200);
        $op2->addSubItem($caption);*/
        $rad_caption->addOption($op2);
        $this->form_gui->addItem($rad_caption);

        // standard text representation
        if ($this->media_type->usesAltTextProperty($std_item->getFormat())) {
            $rad_tr = new ilRadioGroupInputGUI($lng->txt("text_repr"), "st_derive_text_representation");
            $op1 = new ilRadioOption($lng->txt("cont_default"), "y");
            $def_tr = new ilNonEditableValueGUI("", "def_text_representation");
            $op1->addSubItem($def_tr);
            $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
            $tr = new ilTextAreaInputGUI("", "st_text_representation");
            $tr->setCols(30);
            $tr->setRows(2);
            $rad_tr->addOption($op1);
            $op2->addSubItem($tr);
            $rad_tr->addOption($op2);
            $this->form_gui->addItem($rad_tr);
            $rad_tr->setInfo($lng->txt("text_repr_info"));
        }

        // standard parameters
        if ($this->media_type->usesParameterProperty($std_item->getFormat())) {
            if ($this->media_type->usesAutoStartParameterOnly(
                $std_item->getLocation(),
                $std_item->getFormat()
            )) {	// autostart
                /*
                $par = $std_item->getParameters();
                $def_str = ($par["autostart"] == "true")
                    ? " (" . $lng->txt("yes") . ")"
                    : " (" . $lng->txt("no") . ")";
                $rad_auto = new ilRadioGroupInputGUI(
                    $lng->txt("cont_autostart"),
                    "st_derive_parameters"
                );
                $op1 = new ilRadioOption($lng->txt("cont_default") . $def_str, "y");
                $rad_auto->addOption($op1);
                $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
                $auto = new ilCheckboxInputGUI($lng->txt("enabled"), "st_autostart");
                $op2->addSubItem($auto);
                $rad_auto->addOption($op2);
                $this->form_gui->addItem($rad_auto);*/
            } else {							// parameters
                $rad_parameters = new ilRadioGroupInputGUI($lng->txt("cont_parameter"), "st_derive_parameters");
                $op1 = new ilRadioOption($lng->txt("cont_default"), "y");
                $def_par = new ilNonEditableValueGUI("", "def_parameters");
                $op1->addSubItem($def_par);
                $rad_parameters->addOption($op1);
                $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
                $par = new ilTextAreaInputGUI("", "st_parameters");
                $par->setRows(5);
                $par->setCols(50);
                $op2->addSubItem($par);
                $rad_parameters->addOption($op2);
                $this->form_gui->addItem($rad_parameters);
            }
        }
        
        // fullscreen view
        if ($this->content_obj->getMediaObject()->hasFullScreenItem()) {
            $full_item = $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");
            
            $fs_sec = new ilFormSectionHeaderGUI();
            $fs_sec->setTitle($lng->txt("cont_fullscreen"));
            $this->form_gui->addItem($fs_sec);

            
            // resource
            $radio_prop = new ilRadioGroupInputGUI($lng->txt("cont_resource"), "fullscreen");
            $op1 = new ilRadioOption($lng->txt("cont_none"), "n");
            $radio_prop->addOption($op1);
            $op2 = new ilRadioOption($this->lng->txt("cont_" . strtolower($full_item->getLocationType())) . ": " .
                $full_item->getLocation(), "y");
            $radio_prop->addOption($op2);
            $this->form_gui->addItem($radio_prop);

            // format
            $format = new ilNonEditableValueGUI(
                $this->lng->txt("cont_format"),
                "full_format"
            );
            $this->form_gui->addItem($format);
            
            // full size
            $radio_size = new ilRadioGroupInputGUI($lng->txt("size"), "full_derive_size");
            $fw_size = $std_item->getOriginalSize();
            $add_str = (!is_null($fw_size))
                ? " (" . $fw_size["width"] . " x " . $fw_size["height"] . ")"
                : "";
            $op1 = new ilRadioOption($lng->txt("cont_default") . $add_str, "y");
            $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
            $radio_size->addOption($op1);
            
            // width height
            $width_height = new ilWidthHeightInputGUI($lng->txt("cont_width") .
                    " / " . $lng->txt("cont_height"), "full_width_height");
            $width_height->setConstrainProportions(true);
            $op2->addSubItem($width_height);
    
            $radio_size->addOption($op2);
            $this->form_gui->addItem($radio_size);
            
            // fullscreen caption
            $rad_caption = new ilRadioGroupInputGUI($lng->txt("cont_caption"), "full_derive_caption");
            $op1 = new ilRadioOption($lng->txt("cont_default"), "y");
            $def_cap = new ilNonEditableValueGUI("", "full_def_caption");
            $op1->addSubItem($def_cap);
            $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
            $rad_caption->addOption($op1);

            $caption = new ilTextAreaInputGUI("", "full_caption");
            $caption->setCols(30);
            $caption->setRows(2);
            $op2->addSubItem($caption);

            /*$caption = new ilTextInputGUI("", "full_caption");
            $caption->setSize(40);
            $caption->setMaxLength(200);
            $op2->addSubItem($caption);*/
            $rad_caption->addOption($op2);
            $this->form_gui->addItem($rad_caption);
            
            // fullscreen text representation
            if (substr($full_item->getFormat(), 0, 5) == "image") {
                $rad_tr = new ilRadioGroupInputGUI($lng->txt("text_repr"), "full_derive_text_representation");
                $op1 = new ilRadioOption($lng->txt("cont_default"), "y");
                $def_tr = new ilNonEditableValueGUI("", "full_def_text_representation");
                $op1->addSubItem($def_tr);
                $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
                $tr = new ilTextAreaInputGUI("", "full_text_representation");
                $tr->setCols(30);
                $tr->setRows(2);
                $rad_tr->addOption($op1);
                $op2->addSubItem($tr);
                $rad_tr->addOption($op2);
                $this->form_gui->addItem($rad_tr);
                $rad_tr->setInfo($lng->txt("text_repr_info"));
            }
    
            // fullscreen parameters
            if ($this->media_type->usesParameterProperty($full_item->getFormat())) {
                if ($this->media_type->usesAutoStartParameterOnly(
                    $full_item->getLocation(),
                    $full_item->getFormat()
                )) {	// autostart
                    /*
                    $par = $full_item->getParameters();
                    $def_str = ($par["autostart"] == "true")
                        ? " (" . $lng->txt("yes") . ")"
                        : " (" . $lng->txt("no") . ")";
                    $rad_auto = new ilRadioGroupInputGUI(
                        $lng->txt("cont_autostart"),
                        "full_derive_parameters"
                    );
                    $op1 = new ilRadioOption($lng->txt("cont_default") . $def_str, "y");
                    $rad_auto->addOption($op1);
                    $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
                    $auto = new ilCheckboxInputGUI($lng->txt("enabled"), "full_autostart");
                    $op2->addSubItem($auto);
                    $rad_auto->addOption($op2);
                    $this->form_gui->addItem($rad_auto);*/
                } else {							// parameters
                    $rad_parameters = new ilRadioGroupInputGUI($lng->txt("cont_parameter"), "full_derive_parameters");
                    $op1 = new ilRadioOption($lng->txt("cont_default"), "y");
                    $def_par = new ilNonEditableValueGUI("", "full_def_parameters");
                    $op1->addSubItem($def_par);
                    $rad_parameters->addOption($op1);
                    $op2 = new ilRadioOption($lng->txt("cont_custom"), "n");
                    $par = new ilTextAreaInputGUI("", "full_parameters");
                    $par->setRows(5);
                    $par->setCols(50);
                    $op2->addSubItem($par);
                    $rad_parameters->addOption($op2);
                    $this->form_gui->addItem($rad_parameters);
                }
            }
        }

        $this->form_gui->setTitle($lng->txt("cont_edit_mob_alias_prop"));
        $this->form_gui->addCommandButton("saveAliasProperties", $lng->txt("save"));
        $lm_set = new ilSetting("lm");
        if ($lm_set->get("replace_mob_feature")) {
            $this->form_gui->addCommandButton("changeObjectReference", $lng->txt("cont_change_object_reference"));
        }
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
    }


    /**
     * Put alias values into form
     */
    public function getAliasValues() : void
    {
        $lng = $this->lng;
        
        // standard view resource
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");

        $values["title"] = $this->content_obj->getMediaObject()->getTitle();
        $values["st_location"] = $std_item->getLocation();
        $values["st_format"] = $std_item->getFormat();
        
        // size
        $values["st_width_height"]["width"] = $std_alias_item->getWidth();
        $values["st_width_height"]["height"] = $std_alias_item->getHeight();
        $values["st_width_height"]["constr_prop"] = true;
        
        // caption
        $values["st_caption"] = $std_alias_item->getCaption();
        if (trim($std_item->getCaption()) == "") {
            $values["def_caption"] = $lng->txt("cont_no_caption");
        } else {
            $values["def_caption"] = $std_item->getCaption();
        }

        // text representation
        $values["st_text_representation"] = $std_alias_item->getTextRepresentation();
        if (trim($std_item->getTextRepresentation()) == "") {
            $values["def_text_representation"] = $lng->txt("cont_no_text");
        } else {
            $values["def_text_representation"] = $std_item->getTextRepresentation();
        }
        
        // parameters / autostart
        if ($this->media_type->usesAutoStartParameterOnly(
            $std_item->getLocation(),
            $std_item->getFormat()
        )) {	// autostart
            /*
            $par = $std_alias_item->getParameters();
            if ($par["autostart"] == "true") {
                $values["st_autostart"] = true;
            }*/
        } else {				// parameters
            $values["st_parameters"] = $std_alias_item->getParameterString();
        }
        
        // size
        $values["st_derive_size"] = $std_alias_item->definesSize()
            ? "n"
            : "y";
        if ($values["st_derive_size"] == "y") {
            $values["st_width_height"]["width"] = $std_item->getWidth();
            $values["st_width_height"]["height"] = $std_item->getHeight();
        }
        $values["st_derive_caption"] = $std_alias_item->definesCaption()
            ? "n"
            : "y";
        $values["st_derive_text_representation"] = $std_alias_item->definesTextRepresentation()
            ? "n"
            : "y";
        $values["st_derive_parameters"] = $std_alias_item->definesParameters()
            ? "n"
            : "y";
        if (trim($std_item->getParameterString()) == "") {
            $values["def_parameters"] = "<i>" . $lng->txt("cont_no_parameters") . "</i>";
        } else {
            $values["def_parameters"] = $std_item->getParameterString();
        }
            
        // fullscreen properties
        if ($this->content_obj->getMediaObject()->hasFullScreenItem()) {
            $full_alias_item = $this->content_obj->getFullscreenMediaAliasItem();
            $full_item = $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");

            $values["fullscreen"] = "n";
            if ($full_alias_item->exists()) {
                $values["fullscreen"] = "y";
            }

            $values["full_location"] = $full_item->getLocation();
            $values["full_format"] = $full_item->getFormat();
            $values["full_width_height"]["width"] = $full_alias_item->getWidth();
            $values["full_width_height"]["height"] = $full_alias_item->getHeight();
            $values["full_width_height"]["constr_prop"] = true;
            $values["full_caption"] = $full_alias_item->getCaption();
            if (trim($full_item->getCaption()) == "") {
                $values["full_def_caption"] = $lng->txt("cont_no_caption");
            } else {
                $values["full_def_caption"] = $full_item->getCaption();
            }
            $values["full_text_representation"] = $full_alias_item->getTextRepresentation();
            if (trim($full_item->getTextRepresentation()) == "") {
                $values["full_def_text_representation"] = $lng->txt("cont_no_text");
            } else {
                $values["full_def_text_representation"] = $full_item->getTextRepresentation();
            }
            $values["full_parameters"] = $full_alias_item->getParameterString();
            $values["full_derive_size"] = $full_alias_item->definesSize()
                ? "n"
                : "y";
            if ($values["full_derive_size"] == "y") {
                $values["full_width_height"]["width"] = $full_item->getWidth();
                $values["full_width_height"]["height"] = $full_item->getHeight();
            }
            $values["full_derive_caption"] = $full_alias_item->definesCaption()
                ? "n"
                : "y";
            $values["full_derive_text_representation"] = $full_alias_item->definesTextRepresentation()
                ? "n"
                : "y";
                
            // parameters
            if ($this->media_type->usesAutoStartParameterOnly(
                $full_item->getLocation(),
                $full_item->getFormat()
            )) {	// autostart
                /*
                $par = $full_alias_item->getParameters();
                if ($par["autostart"] == "true") {
                    $values["full_autostart"] = true;
                }*/
            } else {				// parameters
                $values["full_parameters"] = $full_alias_item->getParameterString();
            }

            $values["full_derive_parameters"] = $full_alias_item->definesParameters()
                ? "n"
                : "y";
            if (trim($full_item->getParameterString()) == "") {
                $values["full_def_parameters"] = "<i>" . $lng->txt("cont_no_parameters") . "</i>";
            } else {
                $values["full_def_parameters"] = $full_item->getParameterString();
            }
        }

        $this->form_gui->setValuesByArray($values);
    }

    /**
    * save table properties in db and return to page edit screen
    */
    public function saveAliasProperties() : void
    {
        $this->initAliasForm();
        $form = $this->form_gui;
        $form->checkInput();

        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $full_alias_item = $this->content_obj->getFullscreenMediaAliasItem();
        $std_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");
        $full_item = $this->content_obj->getMediaObject()->getMediaItem("Fullscreen");

        // standard size
        if ($form->getInput("st_derive_size") == "y") {
            $std_alias_item->deriveSize();
        } else {
            $size = $this->request->getStringArray("st_width_height");
            $std_alias_item->setWidth($size["width"]);
            $std_alias_item->setHeight($size["height"]);
        }

        // standard caption
        if ($form->getInput("st_derive_caption") == "y") {
            $std_alias_item->deriveCaption();
        } else {
            $std_alias_item->setCaption($form->getInput("st_caption"));
        }

        // text representation
        if ($form->getInput("st_derive_text_representation") == "y") {
            $std_alias_item->deriveTextRepresentation();
        } else {
            $std_alias_item->setTextRepresentation(
                $form->getInput("st_text_representation")
            );
        }

        // standard parameters
        if ($form->getInput("st_derive_parameters") == "y") {
            $std_alias_item->deriveParameters();
        } else {
            if ($this->media_type->usesAutoStartParameterOnly(
                $std_item->getLocation(),
                $std_item->getFormat()
            )) {	// autostart
                //
            } else {				// parameters
                $std_alias_item->setParameters(
                    ilUtil::extractParameterString(
                        utf8_decode($form->getInput("st_parameters"))
                    )
                );
            }
        }

        if ($this->content_obj->getMediaObject()->hasFullscreenItem()) {
            if ($form->getInput("fullscreen") == "y") {
                if (!$full_alias_item->exists()) {
                    $full_alias_item->insert();
                }

                // fullscreen size
                if ($form->getInput("full_derive_size") == "y") {
                    $full_alias_item->deriveSize();
                } else {
                    $full_size = $this->request->getStringArray("full_width_height");
                    $full_alias_item->setWidth($full_size["width"]);
                    $full_alias_item->setHeight($full_size["height"]);
                }

                // fullscreen caption
                if ($form->getInput("full_derive_caption") == "y") {
                    $full_alias_item->deriveCaption();
                } else {
                    $full_alias_item->setCaption($form->getInput("full_caption"));
                }

                // fullscreen text representation
                if ($form->getInput("full_derive_text_representation") == "y") {
                    $full_alias_item->deriveTextRepresentation();
                } else {
                    $full_alias_item->setTextRepresentation(
                        $form->getInput("full_text_representation")
                    );
                }

                // fullscreen parameters
                if ($form->getInput("full_derive_parameters") == "y") {
                    $full_alias_item->deriveParameters();
                } else {
                    if ($this->media_type->usesAutoStartParameterOnly(
                        $full_item->getLocation(),
                        $full_item->getFormat()
                    )) {	// autostart
                        //
                    } else {
                        $full_alias_item->setParameters(ilUtil::extractParameterString(utf8_decode($form->getInput("full_parameters"))));
                    }
                }
            } else {
                if ($full_alias_item->exists()) {
                    $full_alias_item->delete();
                }
            }
        }

        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editAlias");
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->editAlias();
        }
    }

    /**
     * copy media object to clipboard
     */
    public function copyToClipboard() : void
    {
        $ilUser = $this->user;

        $ilUser->addObjectToClipboard($this->content_obj->getMediaObject()->getId(), $this->content_obj->getMediaObject()->getType(), $this->content_obj->getMediaObject()->getTitle());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("copied_to_clipboard"), true);
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * align media object to center
     */
    public function centerAlign() : void
    {
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_alias_item->setHorizontalAlign("Center");
        $this->updateAndReturn();
    }

    /**
     * align media object to left
     */
    public function leftAlign() : void
    {
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_alias_item->setHorizontalAlign("Left");
        $this->updateAndReturn();
    }

    /**
     * align media object to right
     */
    public function rightAlign() : void
    {
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_alias_item->setHorizontalAlign("Right");
        $this->updateAndReturn();
    }

    /**
     * align media object to left, floating text
     */
    public function leftFloatAlign() : void
    {
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_alias_item->setHorizontalAlign("LeftFloat");
        $this->updateAndReturn();
    }

    /**
     * align media object to right, floating text
     */
    public function rightFloatAlign() : void
    {
        $std_alias_item = $this->content_obj->getStandardMediaAliasItem();
        $std_alias_item->setHorizontalAlign("RightFloat");
        $this->updateAndReturn();
    }

    /**
     * Checks whether style selection shoudl be available or not
     */
    public function checkStyleSelection() : bool
    {
        // check whether there is more than one style class
        $chars = $this->getCharacteristics();

        if (count($chars) > 1 ||
            ($this->content_obj->getClass() != "" && $this->content_obj->getClass() != "Media")) {
            return true;
        }
        return false;
    }

    /**
     * Edit Style
     */
    public function editStyle() : void
    {
        $a_seleted_value = "";

        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $this->displayValidationError();
        
        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_edit_style"));
        
        // characteristic selection
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_characteristic"),
            "characteristic"
        );
            
        $chars = $this->getCharacteristics();
        if (is_object($this->content_obj)) {
            if ($chars[$a_seleted_value] == "" && ($this->content_obj->getClass() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getClass() => $this->content_obj->getClass()),
                    $chars
                );
            }
        }

        $selected = $this->content_obj->getClass();
        if ($selected == "") {
            $selected = "MediaContainer";
        }
            
        foreach ($chars as $k => $char) {
            $html = '<div class="ilCOPgEditStyleSelectionItem">' .
                $char . '</div>';
            $char_prop->addOption($k, $char, $html);
        }

        $char_prop->setValue($selected);
        $form->addItem($char_prop);


        // caption style
        $cap_style = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_caption_style"),
            "caption_style"
        );
        //$this->setBasicTableCellStyles();
        $this->setCharacteristics([]);
        $this->getCharacteristicsOfCurrentStyle(["media_caption"]);
        $chars = $this->getCharacteristics();
        $options = $chars;
        //$options = array_merge(array("" => $this->lng->txt("none")), $chars);
        foreach ($options as $k => $option) {
            $html = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="ilc_table_cell_' . $k . '">' .
                $option . '</td></tr></table>';
            $cap_style->addOption($k, $option, $html);
        }

        if (count($options) > 0) {
            $current_value = $this->content_obj->getCaptionClass() ?: "MediaCaption";
            $cap_style->setValue($current_value);
            $form->addItem($cap_style);
        }



        // save button
        $form->addCommandButton("saveStyle", $lng->txt("save"));

        $html = $form->getHTML();
        $tpl->setContent($html);
    }

    public function getStyleInput() : ilAdvSelectInputGUI
    {
        // characteristic selection
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_characteristic"),
            "characteristic"
        );

        $selected = $this->content_obj->getClass();
        if ($selected == "") {
            $selected = "MediaContainer";
        }

        $chars = $this->getCharacteristics();
        if (is_object($this->content_obj)) {
            if ($chars[$selected] == "" && ($this->content_obj->getClass() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getClass() => $this->content_obj->getClass()),
                    $chars
                );
            }
        }

        foreach ($chars as $k => $char) {
            $html = '<div class="ilCOPgEditStyleSelectionItem">' .
                $char . '</div>';
            $char_prop->addOption($k, $char, $html);
        }

        $char_prop->setValue($selected);

        return $char_prop;
    }

    /**
     * Save Style
     */
    public function saveStyle() : void
    {
        $this->content_obj->setClass(
            $this->request->getString("characteristic")
        );
        $this->content_obj->setCaptionClass(
            $this->request->getString("caption_style")
        );

        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->editStyle();
        }
    }

    public function getTabs(
        bool $a_create = false,
        bool $a_change_obj_ref = false
    ) : void {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        if (!$a_create) {
            if ($this->checkStyleSelection()) {
                $ilTabs->addTarget(
                    "cont_style",
                    $ilCtrl->getLinkTarget($this, "editStyle"),
                    "editStyle",
                    get_class($this)
                );
            }

            if ($this->content_obj->checkInstanceEditing()) {
                $ilTabs->addTarget(
                    "cont_mob_inst_prop",
                    $ilCtrl->getLinkTarget($this, "editAlias"),
                    "editAlias",
                    get_class($this)
                );

                if ($this->getEnabledMapAreas()) {
                    $st_item = $this->content_obj->getMediaObject()->getMediaItem("Standard");
                    if (is_object($st_item)) {
                        $format = $st_item->getFormat();
                        if (substr($format, 0, 5) == "image" && !is_int(strpos($format, "svg"))) {
                            $ilTabs->addTarget(
                                "cont_inst_map_areas",
                                $ilCtrl->getLinkTargetByClass("ilpcimagemapeditorgui", "editMapAreas"),
                                array(),
                                "ilpcimagemapeditorgui"
                            );
                        }
                    }
                }
            }
        } else {
            if ($a_change_obj_ref) {
                $cmd = "changeObjectReference";
            } else {
                $cmd = "insert";
            }

            if ($a_change_obj_ref) {
                $ilCtrl->setParameter($this, "subCmd", "insertNew");
                $ilTabs->addSubTabTarget(
                    "cont_new_mob",
                    $ilCtrl->getLinkTarget($this, $cmd),
                    $cmd
                );

                $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
                $ilTabs->addSubTabTarget(
                    "cont_mob_from_media_pool",
                    $ilCtrl->getLinkTarget($this, $cmd),
                    $cmd
                );
                $ilCtrl->setParameter($this, "subCmd", "");
            }
        }
    }

    /**
     * Get characteristics
     */
    public static function _getStandardCharacteristics() : array
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $lng = $DIC->language();

        return array(
            "MediaContainer" => $lng->txt("cont_Media"),
            "MediaContainerMax50" => "MediaContainerMax50",
            "MediaContainerFull100" => "MediaContainerFull100",
            "MediaContainerHighlighted" => "MediaContainerHighlighted",
            "MediaContainerSeparated" => "MediaContainerSeparated"
        );
    }

    /**
     * Get characteristics
     */
    public static function _getCharacteristics(int $a_style_id) : array
    {
        $chars = self::_getStandardCharacteristics();
        if ($a_style_id > 0 &&
            ilObject::_lookupType($a_style_id) == "sty") {
            $style = new ilObjStyleSheet($a_style_id);
            $chars = $style->getCharacteristics("media_cont");
            $new_chars = array();
            foreach ($chars as $char) {
                if (($chars[$char] ?? "") != "") {	// keep lang vars for standard chars
                    $new_chars[$char] = $chars[$char];
                } else {
                    $new_chars[$char] = $char;
                }
                asort($new_chars);
            }
            $chars = $new_chars;
        }
        return $chars;
    }
}
