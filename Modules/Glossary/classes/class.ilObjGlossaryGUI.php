<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilGlossaryGUI
 *
 * GUI class for ilGlossary
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilObjGlossaryGUI: ilGlossaryTermGUI, ilMDEditorGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilInfoScreenGUI, ilCommonActionDispatcherGUI, ilObjStyleSheetGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilObjTaxonomyGUI, ilExportGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilObjectMetaDataGUI, ilGlossaryForeignTermCollectorGUI
 */
class ilObjGlossaryGUI extends ilObjectGUI
{
    /**
     * @var ilErrorHandling
     */
    protected $error;

    public $admin_tabs;
    public $mode;
    public $term;

    /**
     * @var int
     */
    protected $term_id;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilHelp
     */
    protected $help;

    /**
     * @var ilGlossaryTermPermission
     */
    protected $term_perm;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->error = $DIC["ilErr"];
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
        $this->setting = $DIC["ilSetting"];
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->help = $DIC["ilHelp"];

        $this->log = ilLoggerFactory::getLogger('glo');

        $this->term_perm = ilGlossaryTermPermission::getInstance();

        $this->ctrl->saveParameter($this, array("ref_id", "offset"));

        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("glo");
        
        $this->type = "glo";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        
        // determine term id and check whether it is valid (belongs to
        // current glossary)
        $this->term_id = (int) $_GET["term_id"];
        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
        if ($this->term_id > 0 && $term_glo_id != $this->object->getId()
            && !ilGlossaryTermReferences::isReferenced($this->object->getId(), $this->term_id)) {
            $this->term_id = "";
        }

        $this->tax_id = $this->object->getTaxonomyId();
        if ($this->tax_id > 0) {
            $this->ctrl->saveParameter($this, array("show_tax", "tax_node"));

            $this->tax = new ilObjTaxonomy($this->tax_id);
        }
        if ((int) $_GET["tax_node"] > 1 && $this->tax->getTree()->readRootId() != $_GET["tax_node"]) {
            $this->tax_node = (int) $_GET["tax_node"];
        }
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $this->log->debug("glossary term, next class " . $next_class . ", cmd: " . $cmd);

        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->checkPermission("write");
                
                $this->getTemplate();
                $this->setTabs();
                $this->setLocator();
                $this->addHeaderAction();

                $this->tabs_gui->activateTab('meta_data');
                $md_gui = new ilObjectMetaDataGUI($this->object, 'term');
                $this->ctrl->forwardCommand($md_gui);
                break;
            
            case "ilglossarytermgui":
                if (!$this->term_perm->checkPermission("edit_content", $this->term_id) &&
                    !$this->term_perm->checkPermission("write", $this->term_id)) {
                    throw new ilGlossaryException("No permission.");
                }
                $this->getTemplate();
//				$this->quickList();
                $this->ctrl->setReturn($this, "listTerms");
                $term_gui = new ilGlossaryTermGUI($this->term_id);
                $term_gui->setGlossary($this->object);
                //$ret = $term_gui->executeCommand();
                $ret = $this->ctrl->forwardCommand($term_gui);
                break;
                
            case "ilinfoscreengui":
                $this->addHeaderAction();
                $this->showInfoScreen();
                break;
                
            case "ilobjstylesheetgui":
                $this->ctrl->setReturn($this, "editStyleProperties");
                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
                $style_gui->omitLocator();
                if ($cmd == "create" || $_GET["new_type"] == "sty") {
                    $style_gui->setCreationMode(true);
                }

                if ($cmd == "confirmedDelete") {
                    $this->object->setStyleSheetId(0);
                    $this->object->update();
                }

                $ret = $this->ctrl->forwardCommand($style_gui);

                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
                    $style_id = $ret;
                    $this->object->setStyleSheetId($style_id);
                    $this->object->update();
                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
                }
                break;

                
            case 'ilpermissiongui':
                if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
                    $this->prepareOutput();
                } else {
                    $this->getTemplate();
                    $this->setTabs();
                    $this->setLocator();
                    $this->addHeaderAction();
                }
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;
                
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->prepareOutput();
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilobjtaxonomygui":
                $this->getTemplate();
                $this->setTabs();
                $this->setLocator();
                $this->addHeaderAction();
                $this->tabs->activateTab("settings");
                $this->setSettingsSubTabs("taxonomy");

                $this->ctrl->setReturn($this, "properties");
                $tax_gui = new ilObjTaxonomyGUI();
                $tax_gui->setMultiple(false);

                $tax_gui->setAssignedObject($this->object->getId());
                $ret = $this->ctrl->forwardCommand($tax_gui);
                break;

            case "ilexportgui":
                $this->getTemplate();
                $this->setTabs();
                $this->tabs->activateTab("export");
                $this->setLocator();
                $exp_gui = new ilExportGUI($this);
                //$exp_gui->addFormat("xml", "", $this, "export");
                $exp_gui->addFormat("xml");
                $exp_gui->addFormat("html", "", $this, "exportHTML");
                $exp_gui->addCustomColumn(
                    $this->lng->txt("cont_public_access"),
                    $this,
                    "getPublicAccessColValue"
                );
                $exp_gui->addCustomMultiCommand(
                    $this->lng->txt("cont_public_access"),
                    $this,
                    "publishExportFile"
                );
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('glo');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilglossaryforeigntermcollectorgui":
                $this->ctrl->setReturn($this, "");
                $this->getTemplate();
                $this->setTabs();
                $this->setLocator();
                $this->addHeaderAction();
                $coll = ilGlossaryForeignTermCollectorGUI::getInstance($this);
                $this->ctrl->forwardCommand($coll);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listTerms");

                if (($cmd == "create") && ($_POST["new_type"] == "term")) {
                    $this->ctrl->setCmd("create");
                    $this->ctrl->setCmdClass("ilGlossaryTermGUI");
                    $ret = $this->executeCommand();
                    return;
                } else {
                    if (!in_array($cmd, array("quickList"))) {
                        if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
                            $this->getCreationMode() == true) {
                            $this->prepareOutput();
                            $cmd .= "Object";
                        } else {
                            $this->getTemplate();
                            $this->setTabs();
                            $this->setLocator();
                            $this->addHeaderAction();
                            
                            if ($cmd == "redrawHeaderAction") {
                                $cmd = "redrawHeaderActionObject";
                            }
                        }
                    }
                    $ret = $this->$cmd();
                }
                break;
        }

        if (!in_array($cmd, array("quickList"))) {
            if (strtolower($_GET["baseClass"]) != "iladministrationgui") {
                if (!$this->getCreationMode()) {
                    $this->tpl->printToStdout();
                }
            }
        } else {
            $this->tpl->printToStdout(false);
        }
    }

    public function assignObject()
    {
        $this->object = new ilObjGlossary($this->id, true);
    }

    public function initCreateForm($a_new_type)
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt($a_new_type . "_new"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        // mode
        $stati = array(
                        "none" => $this->lng->txt("glo_mode_normal"),
                        "level" => $this->lng->txt("glo_mode_level"),
                        "subtree" => $this->lng->txt("glo_mode_subtree")
                        );
        $tm = new ilSelectInputGUI($this->lng->txt("glo_mode"), "glo_mode");
        $tm->setOptions($stati);
        $tm->setInfo($this->lng->txt("glo_mode_desc"));
        $tm->setRequired(true);
        $form->addItem($tm);

        // didactic template
        $form = $this->initDidacticTemplate($form);

        $form->addCommandButton("save", $this->lng->txt($a_new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }
    
    public function importObject()
    {
        $this->createObject();
    }

    /**
    * save new content object to db
    */
    public function saveObject()
    {
        $ilErr = $this->error;

        $new_type = $_REQUEST["new_type"];

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"), $ilErr->MESSAGE);
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, "new_type", $new_type);

        $form = $this->initCreateForm($new_type);
        if ($form->checkInput()) {
            $this->ctrl->setParameter($this, "new_type", "");

            $newObj = new ilObjGlossary();
            $newObj->setType($new_type);
            $newObj->setTitle($form->getInput("title"));
            $newObj->setDescription($form->getInput("desc"));
            $newObj->setVirtualMode($form->getInput("glo_mode"));
            $newObj->create();
            
            $this->putObjectInTree($newObj);

            // apply didactic template?
            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }

            // always send a message
            ilUtil::sendSuccess($this->lng->txt("glo_added"), true);
            ilUtil::redirect("ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=" . $newObj->getRefId());
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Show info screen
     *
     * @param
     * @return
     */
    public function showInfoScreen()
    {
        $this->getTemplate();
        $this->setTabs();
        $this->setLocator();
        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableNews();
        if ($this->access->checkAccess("write", "", $_GET["ref_id"])) {
            $info->enableNewsEditing();
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        ilObjGlossaryGUI::addUsagesToInfo($info, $this->object->getId());
        
        $this->ctrl->forwardCommand($info);
    }
    
    /**
     * Add usages to info
     *
     * @param
     * @return
     */
    public static function addUsagesToInfo($info, $glo_id)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilAccess = $DIC->access();

        $info->addSection($lng->txt("glo_usages"));
        $sms = ilObjSAHSLearningModule::getScormModulesForGlossary($glo_id);
        foreach ($sms as $sm) {
            $link = false;
            $refs = ilObject::_getAllReferences($sm);
            foreach ($refs as $ref) {
                if ($link === false) {
                    if ($ilAccess->checkAccess("write", "", $ref)) {
                        $link = ilLink::_getLink($ref, 'sahs');
                    }
                }
            }
            
            $entry = ilObject::_lookupTitle($sm);
            if ($link !== false) {
                $entry = "<a href='" . $link . "' target='_top'>" . $entry . "</a>";
            }
            
            $info->addProperty($lng->txt("obj_sahs"), $entry);
        }
    }
    
    
    public function viewObject()
    {
        $ilErr = $this->error;

        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
            return;
        }

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
    }

    /**
    * edit properties of object (admin form)
    *
    * @access	public
    */
    public function properties()
    {
        $this->checkPermission("write");

        $this->setSettingsSubTabs("general_settings");
        
        $this->initSettingsForm();
        
        // Edit ecs export settings
        $ecs = new ilECSGlossarySettings($this->object);
        $ecs->addSettingsToForm($this->form, 'glo');

        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Init settings form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initSettingsForm($a_mode = "edit")
    {
        $obj_service = $this->getObjectService();


        $this->form = new ilPropertyFormGUI();

        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setRequired(true);
        $this->form->addItem($title);

        // description
        $desc = new ilTextAreaInputGUI($this->lng->txt("desc"), "description");
        $this->form->addItem($desc);

        $this->lng->loadLanguageModule("rep");
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $this->form->addItem($section);

        // online
        $online = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
        $online->setValue("y");
        $online->setInfo($this->lng->txt("glo_online_info"));
        $this->form->addItem($online);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('glo_content_settings'));
        $this->form->addItem($section);

        // glossary mode
        $glo_mode = new ilRadioGroupInputGUI($this->lng->txt("glo_mode"), "glo_mode");
        //$glo_mode->setInfo($this->lng->txt("glo_mode_desc"));
        $op1 = new ilRadioOption($this->lng->txt("glo_mode_normal"), "none", $this->lng->txt("glo_mode_normal_info"));
        $glo_mode->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("glo_mode_level"), "level", $this->lng->txt("glo_mode_level_info"));
        $glo_mode->addOption($op2);
        $op3 = new ilRadioOption($this->lng->txt("glo_mode_subtree"), "subtree", $this->lng->txt("glo_mode_subtree_info"));
        $glo_mode->addOption($op3);
        $this->form->addItem($glo_mode);

        // glossary mode
        /*$options = array(
            "none"=>$this->lng->txt("glo_mode_normal"),
            "level"=>$this->lng->txt("glo_mode_level"),
            "subtree"=>$this->lng->txt("glo_mode_subtree")
            );
        $glo_mode = new ilSelectInputGUI($this->lng->txt("glo_mode"), "glo_mode");
        $glo_mode->setOptions($options);
        $glo_mode->setInfo($this->lng->txt("glo_mode_desc"));
        $this->form->addItem($glo_mode);*/


        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('cont_presentation'));
        $this->form->addItem($section);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

        // presentation mode
        $pres_mode = new ilRadioGroupInputGUI($this->lng->txt("glo_presentation_mode"), "pres_mode");
        $pres_mode->setValue("table");
        $op1 = new ilRadioOption($this->lng->txt("glo_table_form"), "table", $this->lng->txt("glo_table_form_info"));

        // short text length
        $snl = new ilNumberInputGUI($this->lng->txt("glo_text_snippet_length"), "snippet_length");
        $snl->setMaxValue(3000);
        $snl->setMinValue(100);
        $snl->setMaxLength(4);
        $snl->setSize(4);
        $snl->setInfo($this->lng->txt("glo_text_snippet_length_info"));
        $snl->setValue(200);
        $op1->addSubItem($snl);

        $pres_mode->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("glo_full_definitions"), "full_def", $this->lng->txt("glo_full_definitions_info"));
        $pres_mode->addOption($op2);
        $this->form->addItem($pres_mode);

        // show taxonomy
        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->object->getId());
        if (count($tax_ids) > 0) {
            $show_tax = new ilCheckboxInputGUI($this->lng->txt("glo_show_taxonomy"), "show_tax");
            $show_tax->setInfo($this->lng->txt("glo_show_taxonomy_info"));
            $this->form->addItem($show_tax);
        }
        
        // downloads
        $down = new ilCheckboxInputGUI($this->lng->txt("cont_downloads"), "glo_act_downloads");
        $down->setValue("y");
        $down->setInfo($this->lng->txt("cont_downloads_desc"));
        $this->form->addItem($down);
        
        if ($a_mode == "edit") {
            $title->setValue($this->object->getTitle());
            $desc->setValue($this->object->getDescription());
            $online->setChecked($this->object->getOnline());
            $glo_mode->setValue($this->object->getVirtualMode());
            $pres_mode->setValue($this->object->getPresentationMode());
            $snl->setValue($this->object->getSnippetLength());
            if (count($tax_ids) > 0) {
                $show_tax->setChecked($this->object->getShowTaxonomy());
            }
            
            $down->setChecked($this->object->isActiveDownloads());
            
            // additional features
            $feat = new ilFormSectionHeaderGUI();
            $feat->setTitle($this->lng->txt('obj_features'));
            $this->form->addItem($feat);

            ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $this->object->getId(),
                $this->form,
                array(
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA
                    )
            );
        }
        
        // sort columns, if adv fields are given
        $adv_ap = new ilGlossaryAdvMetaDataAdapter($this->object->getRefId());
        $cols = $adv_ap->getColumnOrder();
        if (count($cols) > 1) {
            $ti = new ilGloAdvColSortInputGUI($this->lng->txt("cont_col_ordering"), "field_order");
            $this->form->addItem($ti);
            $ti->setValue($cols);
        }
    
        // save and cancel commands
        $this->form->addCommandButton("saveProperties", $this->lng->txt("save"));
                    
        $this->form->setTitle($this->lng->txt("cont_glo_properties"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }


    /**
    * save properties
    */
    public function saveProperties()
    {
        $obj_service = $this->getObjectService();

        $this->initSettingsForm();
        if ($this->form->checkInput()) {
            $this->object->setTitle($_POST['title']);
            $this->object->setDescription($_POST['description']);
            $this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
            $this->object->setVirtualMode($_POST["glo_mode"]);
            //			$this->object->setActiveGlossaryMenu(ilUtil::yn2tf($_POST["glo_act_menu"]));
            $this->object->setActiveDownloads(ilUtil::yn2tf($_POST["glo_act_downloads"]));
            $this->object->setPresentationMode($_POST["pres_mode"]);
            $this->object->setSnippetLength($_POST["snippet_length"]);
            $this->object->setShowTaxonomy($_POST["show_tax"]);
            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

            // field order of advanced metadata
            $adv_ap = new ilGlossaryAdvMetaDataAdapter($this->object->getRefId());
            $cols = $adv_ap->getColumnOrder();
            if (count($cols) > 1) {
                $adv_ap->saveColumnOrder($_POST["field_order"]);
            }
            
            // set definition short texts dirty
            ilGlossaryDefinition::setShortTextsDirty($this->object->getId());

            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $this->form,
                array(
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA
                )
            );
            
            // Update ecs export settings
            $ecs = new ilECSGlossarySettings($this->object);
            if ($ecs->handleSettingsUpdate()) {
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, "properties");
            }
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }


    /**
    * list terms
    */
    public function listTerms()
    {
        //if ($_GET["show_tax"]) {
        $this->showTaxonomy();
        //}
        
        // term
        $ti = new ilTextInputGUI($this->lng->txt("cont_new_term"), "new_term");
        $ti->setMaxLength(80);
        $ti->setSize(20);
        $this->toolbar->addInputItem($ti, true);
        
        // language
        $this->lng->loadLanguageModule("meta");
        $lang = ilMDLanguageItem::_getLanguages();
        if ($_SESSION["il_text_lang_" . $_GET["ref_id"]] != "") {
            $s_lang = $_SESSION["il_text_lang_" . $_GET["ref_id"]];
        } else {
            $s_lang = $this->user->getLanguage();
        }
        $si = new ilSelectInputGUI($this->lng->txt("language"), "term_language");
        $si->setOptions($lang);
        $si->setValue($s_lang);
        $this->toolbar->addInputItem($si, true);

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->addFormButton($this->lng->txt("glo_add_new_term"), "addTerm");

        $this->toolbar->addSeparator();

        //ilEditClipboard::getAction() == "copy"
        if ($this->user->clipboardHasObjectsOfType("term")) {
            $this->toolbar->addButton(
                $this->lng->txt("paste"),
                $this->ctrl->getLinkTarget($this, "pasteTerms")
            );
            $this->toolbar->addButton(
                $this->lng->txt("clear_clipboard"),
                $this->ctrl->getLinkTarget($this, "clearClipboard")
            );
        } else {
            $this->toolbar->addButton(
                $this->lng->txt("glo_add_from_other"),
                $this->ctrl->getLinkTargetByClass("ilglossaryforeigntermcollectorgui", "")
            );
        }

        /* this is done by collapsing the tool now
        if (is_object($this->tax)) {
            $this->toolbar->addSeparator();
            if ($_GET["show_tax"]) {
                $this->toolbar->addButton(
                    $this->lng->txt("glo_hide_taxonomy"),
                    $this->ctrl->getLinkTarget($this, "deactTaxonomy")
                );
            } else {
                $this->toolbar->addButton(
                    $this->lng->txt("glo_show_taxonomy"),
                    $this->ctrl->getLinkTarget($this, "actTaxonomy")
                );
            }
        }*/

        $tab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Show Taxonomy
     *
     * @param
     * @return
     */
    public function actTaxonomy()
    {
        $this->ctrl->setParameter($this, "show_tax", 1);
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * Hide Taxonomy
     *
     * @param
     * @return
     */
    public function deactTaxonomy()
    {
        $this->ctrl->setParameter($this, "show_tax", "");
        $this->ctrl->redirect($this, "listTerms");
    }

    
    /**
     * show possible action (form buttons)
     *
     * @access	public
     */
    public function showActions($a_actions)
    {
        foreach ($a_actions as $name => $lng) {
            $d[$name] = array("name" => $name, "lng" => $lng);
        }

        $notoperations = array();
        $operations = array();

        $operations = $d;

        if (count($operations) > 0) {
            foreach ($operations as $val) {
                $this->tpl->setCurrentBlock("tbl_action_btn");
                $this->tpl->setVariable("BTN_NAME", $val["name"]);
                $this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("tbl_action_row");
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * add term
    */
    public function addTerm()
    {
        if (trim($_POST["new_term"]) == "") {
            ilUtil::sendFailure($this->lng->txt("cont_please_enter_a_term"), true);
            $this->ctrl->redirect($this, "listTerms");
        }
        
        // add term
        $term = new ilGlossaryTerm();
        $term->setGlossary($this->object);
        $term->setTerm(ilUtil::stripSlashes($_POST["new_term"]));
        $term->setLanguage($_POST["term_language"]);
        $_SESSION["il_text_lang_" . $_GET["ref_id"]] = $_POST["term_language"];
        $term->create();

        // add first definition
        $def = new ilGlossaryDefinition();
        $def->setTermId($term->getId());
        $def->setTitle(ilUtil::stripSlashes($_POST["new_term"]));
        $def->create();

        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term->getId());
        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", $def->getId());
        $this->ctrl->redirectByClass(array("ilglossarytermgui",
            "iltermdefinitioneditorgui", "ilglossarydefpagegui"), "edit");
    }

    /**
    * move a definiton up
    */
    public function moveDefinitionUp()
    {
        $definition = new ilGlossaryDefinition($_GET["def"]);
        $definition->moveUp();

        $this->ctrl->redirect($this, "listTerms");
    }

    /**
    * move a definiton down
    */
    public function moveDefinitionDown()
    {
        $definition = new ilGlossaryDefinition($_GET["def"]);
        $definition->moveDown();

        $this->ctrl->redirect($this, "listTerms");
    }

    /**
    * deletion confirmation screen
    */
    public function confirmDefinitionDeletion()
    {
        //$this->getTemplate();
        //$this->displayLocator();
        //$this->setTabs();

        $term = new ilGlossaryTerm($this->term_id);
        
        $add = "";
        $nr = ilGlossaryTerm::getNumberOfUsages($this->term_id);
        if ($nr > 0) {
            $this->ctrl->setParameterByClass(
                "ilglossarytermgui",
                "term_id",
                $this->term_id
            );
            $link = "[<a href='" .
                $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listUsages") .
                "'>" . $this->lng->txt("glo_list_usages") . "</a>]";
            $add = "<br/>" . sprintf($this->lng->txt("glo_term_is_used_n_times"), $nr) . " " . $link;
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("info_delete_sure") . $add);

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDefinitionDeletion");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteDefinition");
        
        // content style
        $this->setContentStyleSheet($this->tpl);

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        $definition = new ilGlossaryDefinition($_GET["def"]);
        $page_gui = new ilGlossaryDefPageGUI($definition->getId());
        $page_gui->setTemplateOutput(false);
        $page_gui->setStyleId($this->object->getStyleSheetId());
        $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
        $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
        $page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
        $output = $page_gui->preview();
        
        $cgui->addItem("def", $_GET["def"], $term->getTerm() . $output);
        
        $this->tpl->setContent($cgui->getHTML());
    }
    
    public function cancelDefinitionDeletion()
    {
        $this->ctrl->redirect($this, "listTerms");
    }


    public function deleteDefinition()
    {
        $definition = new ilGlossaryDefinition($_REQUEST["def"]);
        $definition->delete();
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
    * edit term
    */
    public function editTerm()
    {
        // deprecated
    }


    /**
    * update term
    */
    public function updateTerm()
    {
        $term = new ilGlossaryTerm($this->term_id);

        $term->setTerm(ilUtil::stripSlashes($_POST["term"]));
        $term->setLanguage($_POST["term_language"]);
        $term->update();
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTerms");
    }



    /**
    * export content object
    */
    public function export()
    {
        $this->checkPermission("write");

        $glo_exp = new ilGlossaryExport($this->object);
        $glo_exp->buildExportFile();
        $this->ctrl->redirectByClass("ilexportgui", "");
    }
    
    /**
    * create html package
    */
    public function exportHTML()
    {
        $glo_exp = new ilGlossaryExport($this->object, "html");
        $glo_exp->buildExportFile();
        //echo $this->tpl->get();
        $this->ctrl->redirectByClass("ilexportgui", "");
    }

    /**
    * download export file
    */
    public function publishExportFile()
    {
        $ilErr = $this->error;

        if (!isset($_POST["file"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }
        if (count($_POST["file"]) > 1) {
            $ilErr->raiseError($this->lng->txt("cont_select_max_one_item"), $ilErr->MESSAGE);
        }
        
        $file = explode(":", $_POST["file"][0]);
        $export_dir = $this->object->getExportDirectory($file[0]);
        
        if ($this->object->getPublicExportFile($file[0]) ==
            $file[1]) {
            $this->object->setPublicExportFile($file[0], "");
        } else {
            $this->object->setPublicExportFile($file[0], $file[1]);
        }
        $this->object->update();
        $this->ctrl->redirectByClass("ilexportgui", "");
    }

    /*
    * list all export files
    */
    public function viewExportLog()
    {
    }

    /**
    * confirm term deletion
    */
    public function confirmTermDeletion()
    {
        //$this->prepareOutput();
        if (!isset($_POST["id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }
        
        // check ids
        foreach ($_POST["id"] as $term_id) {
            $term_glo_id = ilGlossaryTerm::_lookGlossaryID((int) $term_id);
            if ($term_glo_id != $this->object->getId() && !ilGlossaryTermReferences::isReferenced($this->object->getId(), $term_id)) {
                ilUtil::sendFailure($this->lng->txt("glo_term_must_belong_to_glo"), true);
                $this->ctrl->redirect($this, "listTerms");
            }
        }
        
        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelTermDeletion");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteTerms");

        foreach ($_POST["id"] as $id) {
            $term = new ilGlossaryTerm($id);

            $add = "";
            $nr = ilGlossaryTerm::getNumberOfUsages($id);
            if ($nr > 0) {
                $this->ctrl->setParameterByClass(
                    "ilglossarytermgui",
                    "term_id",
                    $id
                );

                if (ilGlossaryTermReferences::isReferenced($this->object->getId(), $id)) {
                    $add = " (" . $this->lng->txt("glo_term_reference") . ")";
                } else {
                    $link = "[<a href='" .
                        $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listUsages") .
                        "'>" . $this->lng->txt("glo_list_usages") . "</a>]";
                    $add = "<div class='small'>" .
                        sprintf($this->lng->txt("glo_term_is_used_n_times"), $nr) . " " . $link . "</div>";
                }
            }
            
            $cgui->addItem("id[]", $id, $term->getTerm() . $add);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
    * cancel deletion of object
    *
    * @access	public
    */
    public function cancelTermDeletion()
    {
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
    * delete selected terms
    */
    public function deleteTerms()
    {
        foreach ($_POST["id"] as $id) {
            if (ilGlossaryTermReferences::isReferenced($this->object->getId(), $id)) {
                $refs = new ilGlossaryTermReferences($this->object->getId());
                $refs->deleteTerm($id);
                $refs->update();
            } else {
                $term = new ilGlossaryTerm($id);
                $term->delete();
            }
        }
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
    * set Locator
    *
    * @param	object	tree object
    * @param	integer	reference id
    * @access	public
    */
    public function setLocator($a_tree = "", $a_id = "")
    {
        if (strtolower($_GET["baseClass"]) != "ilglossaryeditorgui") {
            parent::setLocator($a_tree, $a_id);
        } else {
            if (is_object($this->object)) {
                $gloss_loc = new ilGlossaryLocatorGUI();
                if (is_object($this->term)) {
                    $gloss_loc->setTerm($this->term);
                }
                $gloss_loc->setGlossary($this->object);
                //$gloss_loc->setDefinition($this->definition);
                $gloss_loc->display();
            }
        }
    }

    /**
    * view content
    */
    public function view()
    {
        //$this->prepareOutput();
        $this->viewObject();
    }

    /**
    * create new (subobject) in glossary
    */
    public function create()
    {
        switch ($_POST["new_type"]) {
            case "term":
                $term_gui = new ilGlossaryTermGUI();
                $term_gui->create();
                break;
        }
    }

    public function saveTerm()
    {
        $term_gui = new ilGlossaryTermGUI();
        $term_gui->setGlossary($this->object);
        $term_gui->save();

        ilUtil::sendSuccess($this->lng->txt("cont_added_term"), true);

        //ilUtil::redirect("glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms");
        $this->ctrl->redirect($this, "listTerms");
    }


    /**
    * add definition
    */
    public function addDefinition()
    {
        $term_id = (int) $_GET["term_id"];

        $term_glo_id = ilGlossaryTerm::_lookGlossaryID((int) $term_id);
        if ($term_glo_id != $this->object->getId()) {
            ilUtil::sendFailure($this->lng->txt("glo_term_must_belong_to_glo"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        // add term
        $term = new ilGlossaryTerm($term_id);

        // add first definition
        $def = new ilGlossaryDefinition();
        $def->setTermId($term->getId());
        $def->setTitle(ilUtil::stripSlashes($term->getTerm()));
        $def->create();

        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term->getId());
        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", $def->getId());
        $this->ctrl->redirectByClass(array("ilglossarytermgui",
            "iltermdefinitioneditorgui", "ilglossarydefpagegui"), "edit");
    }

    public function getTemplate()
    {
        $this->tpl->loadStandardTemplate();

        $title = $this->object->getTitle();


        if ($this->term_id > 0) {
            $this->tpl->setTitle($this->lng->txt("term") . ": " .
                ilGlossaryTerm::_lookGlossaryTerm($this->term_id));
        } else {
            parent::setTitleAndDescription();
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
            $this->tpl->setTitle($this->lng->txt("glo") . ": " . $title);
        }
    }

    /**
    * output tabs
    */
    public function setTabs()
    {
        $this->getTabs();
    }

    /**
    * get tabs
    */
    public function getTabs()
    {
        $this->help->setScreenIdComponent("glo");

        // list terms
        $force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "listTerms")
                ? true
                : false;
        $this->tabs_gui->addTarget(
            "cont_terms",
            $this->ctrl->getLinkTarget($this, "listTerms"),
            array("listTerms", ""),
            get_class($this),
            "",
            $force_active
        );
            
        $force_active = false;
        if ($this->ctrl->getCmd() == "showSummary" ||
            strtolower($this->ctrl->getNextClass()) == "ilinfoscreengui") {
            $force_active = true;
        }
        $this->tabs_gui->addTarget(
            "info_short",
            $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"),
            "",
            "ilInfoScreenGUI",
            "",
            $force_active
        );

        // properties
        if ($this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "properties"),
                "properties",
                get_class($this)
            );
            
            // meta data
            $mdgui = new ilObjectMetaDataGUI($this->object, "term");
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilobjectmetadatagui"
                );
            }
            
            // export
            /*$tabs_gui->addTarget("export",
                 $this->ctrl->getLinkTarget($this, "exportList"),
                 array("exportList", "viewExportLog"), get_class($this));*/

            // export
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTargetByClass("ilexportgui", ""),
                "",
                "ilexportgui"
            );
        }

        // permissions
        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            /*$tabs_gui->addTarget("permission_settings",
                $this->ctrl->getLinkTarget($this, "perm"),
                array("perm", "info"),
                get_class($this));
                */
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
        
        $this->tabs_gui->addNonTabbedLink(
            "presentation_view",
            $this->lng->txt("glo_presentation_view"),
            "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->object->getRefID(),
            "_top"
        );
    }
    
    /**
     * Set sub tabs
     */
    public function setSettingsSubTabs($a_active)
    {
        if (in_array(
            $a_active,
            array("general_settings", "style", "taxonomy", "glossaries")
        )) {
            // general properties
            $this->tabs->addSubTab(
                "general_settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, 'properties')
            );
                
            // style properties
            $this->tabs->addSubTab(
                "style",
                $this->lng->txt("obj_sty"),
                $this->ctrl->getLinkTarget($this, 'editStyleProperties')
            );

            // taxonomy
            ilObjTaxonomy::loadLanguageModule();
            $this->tabs->addSubTab(
                "taxonomy",
                $this->lng->txt("tax_taxonomy"),
                $this->ctrl->getLinkTargetByClass("ilobjtaxonomygui", '')
            );

            // style properties
            $this->tabs->addSubTab(
                "glossaries",
                $this->lng->txt("cont_auto_glossaries"),
                $this->ctrl->getLinkTarget($this, 'editGlossaries')
            );

            $this->tabs->activateSubTab($a_active);
        }
    }

    
    /**
    * redirect script
    *
    * @param	string		$a_target
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $_GET["ref_id"] = $a_target;
            $_GET["baseClass"] = "ilGlossaryPresentationGUI";
            include("ilias.php");
            exit;
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            $_GET["ref_id"] = $a_target;
            $_GET["cmd"] = "infoScreen";
            $_GET["baseClass"] = "ilGlossaryPresentationGUI";
            include("ilias.php");
            exit;
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    /**
     * Apply filter
     */
    public function applyFilter()
    {
        $prtab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
        $prtab->resetOffset();
        $prtab->writeFilterToSession();
        $this->listTerms();
    }
    
    /**
     * Reset filter
     * (note: this function existed before data table filter has been introduced
     */
    public function resetFilter()
    {
        $prtab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
        $prtab->resetOffset();
        $prtab->resetFilter();
        $this->listTerms();
    }


    ////
    //// Style related functions
    ////
    
    /**
     * Set content style sheet
     */
    public function setContentStyleSheet($a_tpl = null)
    {
        if ($a_tpl != null) {
            $ctpl = $a_tpl;
        } else {
            $ctpl = $this->tpl;
        }

        $ctpl->setCurrentBlock("ContentStyle");
        $ctpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId())
        );
        $ctpl->parseCurrentBlock();
    }
    
    
    /**
     * Edit style properties
     */
    public function editStyleProperties()
    {
        $this->checkPermission("write");
        
        $this->initStylePropertiesForm();
        $this->tpl->setContent($this->form->getHTML());

        $this->tabs->activateTab("settings");
        $this->setSettingsSubTabs("style");
    }
    
    /**
     * Init style properties form
     */
    public function initStylePropertiesForm()
    {
        $this->lng->loadLanguageModule("style");

        $this->form = new ilPropertyFormGUI();
        
        $fixed_style = $this->setting->get("fixed_content_style_id");
        $style_id = $this->object->getStyleSheetId();

        if ($fixed_style > 0) {
            $st = new ilNonEditableValueGUI($this->lng->txt("style_current_style"));
            $st->setValue(ilObject::_lookupTitle($fixed_style) . " (" .
                $this->lng->txt("global_fixed") . ")");
            $this->form->addItem($st);
        } else {
            $st_styles = ilObjStyleSheet::_getStandardStyles(
                true,
                false,
                $_GET["ref_id"]
            );

            $st_styles[0] = $this->lng->txt("default");
            ksort($st_styles);

            if ($style_id > 0) {
                // individual style
                if (!ilObjStyleSheet::_lookupStandard($style_id)) {
                    $st = new ilNonEditableValueGUI($this->lng->txt("style_current_style"));
                    $st->setValue(ilObject::_lookupTitle($style_id));
                    $this->form->addItem($st);

                    //$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "edit"));

                    // delete command
                    $this->form->addCommandButton(
                        "editStyle",
                        $this->lng->txt("style_edit_style")
                    );
                    $this->form->addCommandButton(
                        "deleteStyle",
                        $this->lng->txt("style_delete_style")
                    );
                    //$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "delete"));
                }
            }

            if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id)) {
                $style_sel = ilUtil::formSelect(
                    $style_id,
                    "style_id",
                    $st_styles,
                    false,
                    true
                );
                $style_sel = new ilSelectInputGUI($this->lng->txt("style_current_style"), "style_id");
                $style_sel->setOptions($st_styles);
                $style_sel->setValue($style_id);
                $this->form->addItem($style_sel);
                //$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "create"));
                $this->form->addCommandButton(
                    "saveStyleSettings",
                    $this->lng->txt("save")
                );
                $this->form->addCommandButton(
                    "createStyle",
                    $this->lng->txt("sty_create_ind_style")
                );
            }
        }
        $this->form->setTitle($this->lng->txt("glo_style"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    /**
     * Create Style
     */
    public function createStyle()
    {
        $this->ctrl->redirectByClass("ilobjstylesheetgui", "create");
    }
    
    /**
     * Edit Style
     */
    public function editStyle()
    {
        $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
    }

    /**
     * Delete Style
     */
    public function deleteStyle()
    {
        $this->ctrl->redirectByClass("ilobjstylesheetgui", "delete");
    }

    /**
     * Save style settings
     */
    public function saveStyleSettings()
    {
        if ($this->setting->get("fixed_content_style_id") <= 0 &&
            (ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
            || $this->object->getStyleSheetId() == 0)) {
            $this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
            $this->object->update();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        }
        $this->ctrl->redirect($this, "editStyleProperties");
    }
    
    /**
     * Get public access value for export table
     */
    public function getPublicAccessColValue($a_type, $a_file)
    {
        if ($this->object->getPublicExportFile($a_type) == $a_file) {
            return $this->lng->txt("yes");
        }
    
        return " ";
    }

    /**
     * Show taxonomy
     *
     * @throws ilCtrlException
     */
    public function showTaxonomy()
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->object->getId());
        if (count($tax_ids) > 0) {
            $tax_id = $tax_ids[0];
            $DIC->globalScreen()->tool()->context()->current()
                ->addAdditionalData(
                    ilTaxonomyGSToolProvider::SHOW_TAX_TREE,
                    true
                );
            $DIC->globalScreen()->tool()->context()->current()
                ->addAdditionalData(
                    ilTaxonomyGSToolProvider::TAX_TREE_GUI_PATH,
                    $ctrl->getCurrentClassPath()
                );
            $DIC->globalScreen()->tool()->context()->current()
                ->addAdditionalData(
                    ilTaxonomyGSToolProvider::TAX_ID,
                    $tax_id
                );
            $DIC->globalScreen()->tool()->context()->current()
                ->addAdditionalData(
                    ilTaxonomyGSToolProvider::TAX_TREE_CMD,
                    "listTerms"
                );
            $DIC->globalScreen()->tool()->context()->current()
                ->addAdditionalData(
                    ilTaxonomyGSToolProvider::TAX_TREE_PARENT_CMD,
                    "showTaxonomy"
                );


            $tax_exp = new ilTaxonomyExplorerGUI(
                get_class($this),
                "showTaxonomy",
                $tax_ids[0],
                "ilobjglossarygui",
                "listTerms"
            );
            if (!$tax_exp->handleCommand()) {
                //$this->tpl->setLeftNavContent($tax_exp->getHTML());
                //$this->tpl->setLeftNavContent($tax_exp->getHTML() . "&nbsp;");
            }
        }
    }

    //
    // Auto glossaries
    //

    /**
     * Edit automatically linked glossaries
     *
     * @param
     * @return
     */
    public function editGlossaries()
    {
        $this->tabs->setTabActive("settings");
        $this->setSettingsSubTabs("glossaries");

        $this->toolbar->addButton(
            $this->lng->txt("add"),
            $this->ctrl->getLinkTarget($this, "showGlossarySelector")
        );

        $tab = new ilGlossaryAutoLinkTableGUI($this->object, $this, "editGlossaries");

        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Select LM Glossary
     *
     * @param
     * @return
     */
    public function showGlossarySelector()
    {
        $this->tabs->setTabActive("settings");
        $this->setSettingsSubTabs("glossaries");

        $exp = new ilSearchRootSelector($this->ctrl->getLinkTarget($this, 'showGlossarySelector'));
        $exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $this->tree->readRootId());
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showGlossarySelector'));
        $exp->setTargetClass(get_class($this));
        $exp->setCmd('confirmGlossarySelection');
        $exp->setClickableTypes(array("glo"));
        $exp->addFilter("glo");

        // build html-output
        $exp->setOutput(0);
        $this->tpl->setContent($exp->getOutput());
    }

    /**
     * Confirm glossary selection
     */
    public function confirmGlossarySelection()
    {
        $cgui = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, "glo_ref_id", $_GET["root_id"]);
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("glo_link_glo_in_glo"));
        $cgui->setCancel($this->lng->txt("no"), "selectGlossary");
        $cgui->setConfirm($this->lng->txt("yes"), "selectGlossaryLink");
        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * Select a glossary and link all its terms
     *
     * @param
     * @return
     */
    public function selectGlossaryLink()
    {
        $glo_ref_id = (int) $_GET["glo_ref_id"];
        $glo_id = ilObject::_lookupObjId($glo_ref_id);
        $this->object->autoLinkGlossaryTerms($glo_ref_id);
        $this->selectGlossary();
    }


    /**
     * Select lm glossary
     *
     * @param
     * @return
     */
    public function selectGlossary()
    {
        $glos = $this->object->getAutoGlossaries();
        $glo_ref_id = (int) $_GET["glo_ref_id"];
        $glo_id = ilObject::_lookupObjId($glo_ref_id);
        if (!in_array($glo_id, $glos)) {
            $glos[] = $glo_id;
        }
        $this->object->setAutoGlossaries($glos);
        $this->object->update();

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editGlossaries");
    }

    /**
     * Remove lm glossary
     *
     * @param
     * @return
     */
    public function removeGlossary()
    {
        $this->object->removeAutoGlossary((int) $_GET["glo_id"]);
        $this->object->update();

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editGlossaries");
    }
    
    /**
     * Copy terms
     *
     * @param
     * @return
     */
    public function copyTerms()
    {
        $items = ilUtil::stripSlashesArray($_POST["id"]);
        if (!is_array($items)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        $this->user->clipboardDeleteObjectsOfType("term");

        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        $order = 0;
        foreach ($items as $id) {
            $this->user->addObjectToClipboard(
                $id,
                "term",
                ilGlossaryTerm::_lookGlossaryTerm($id),
                0,
                $time,
                $order
            );
        }

        ilEditClipboard::setAction("copy");
        ilUtil::sendInfo($this->lng->txt("glo_selected_terms_have_been_copied"), true);
        $this->ctrl->redirect($this, "listTerms");
    }
    
    /**
     * Reference terms
     *
     * @param
     * @return
     */
    public function referenceTerms()
    {
        $items = ilUtil::stripSlashesArray($_POST["id"]);
        if (!is_array($items)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        $this->user->clipboardDeleteObjectsOfType("term");

        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        $order = 0;
        foreach ($items as $id) {
            $this->user->addObjectToClipboard(
                $id,
                "term",
                ilGlossaryTerm::_lookGlossaryTerm($id),
                0,
                $time,
                $order
            );
        }

        ilEditClipboard::setAction("link");
        ilUtil::sendInfo($this->lng->txt("glo_selected_terms_have_been_copied"), true);
        $this->ctrl->redirect($this, "listTerms");
    }


    /**
     * Clear clipboard
     *
     * @param
     * @return
     */
    public function clearClipboard()
    {
        $this->user->clipboardDeleteObjectsOfType("term");
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * Paste Terms
     */
    public function pasteTerms()
    {
        if (ilEditClipboard::getAction() == "copy") {
            foreach ($this->user->getClipboardObjects("term") as $item) {
                ilGlossaryTerm::_copyTerm($item["id"], $this->object->getId());
            }
        }
        if (ilEditClipboard::getAction() == "link") {
            $refs = new ilGlossaryTermReferences($this->object->getId());
            foreach ($this->user->getClipboardObjects("term") as $item) {
                $refs->addTerm($item["id"]);
            }
            $refs->update();
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTerms");
    }
}
