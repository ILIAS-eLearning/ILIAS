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
 * GUI class for ilGlossary
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjGlossaryGUI: ilGlossaryTermGUI, ilMDEditorGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilInfoScreenGUI, ilCommonActionDispatcherGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilObjTaxonomyGUI, ilExportGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilObjectMetaDataGUI, ilGlossaryForeignTermCollectorGUI
 */
class ilObjGlossaryGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ilPropertyFormGUI $form;
    protected int $tax_node = 0;
    protected ilObjTaxonomy $tax;
    protected $tax_id;
    protected bool $in_administration = false;
    protected \ILIAS\Glossary\Editing\EditingGUIRequest $edit_request;
    protected ?\ILIAS\Glossary\Term\TermManager $term_manager;
    public ?ilGlossaryTerm $term = null;
    protected int $term_id = 0;
    protected ilTabsGUI $tabs;
    protected ilSetting $setting;
    protected ilHelpGUI $help;
    protected ilGlossaryTermPermission $term_perm;
    protected ilLogger $log;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
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

        $this->edit_request = $DIC->glossary()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->log = ilLoggerFactory::getLogger('glo');

        $this->term_perm = ilGlossaryTermPermission::getInstance();

        $this->ctrl->saveParameter($this, array("ref_id"));
        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("glo");

        $this->type = "glo";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        // determine term id and check whether it is valid (belongs to
        // current glossary)
        $this->term_id = $this->edit_request->getTermId();
        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
        if ($this->term_id > 0 && $term_glo_id != $this->object->getId()
            && !ilGlossaryTermReferences::isReferenced([$this->object->getId()], $this->term_id)) {
            $this->term_id = 0;
        }

        $this->tax_id = $this->object->getTaxonomyId();
        if ($this->tax_id > 0) {
            $this->ctrl->saveParameter($this, array("show_tax", "tax_node"));

            $this->tax = new ilObjTaxonomy($this->tax_id);
        }
        $tax_node = $this->edit_request->getTaxNode();
        if ($tax_node > 1 && $this->tax->getTree()->readRootId() != $tax_node) {
            $this->tax_node = $tax_node;
        }

        if ($this->getGlossary()) {
            $this->term_manager = $DIC->glossary()
                  ->internal()
                  ->domain()
                  ->term(
                      $this->getGlossary(),
                      $this->user->getId()
                  );
        }

        $this->in_administration =
            (strtolower($this->edit_request->getBaseClass()) == "iladministrationgui");
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        if (is_object($this->object)) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
        }
    }

    public function executeCommand(): void
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
                $term_gui->setGlossary($this->getGlossary());
                $this->ctrl->forwardCommand($term_gui);
                break;

            case "ilinfoscreengui":
                $this->addHeaderAction();
                $this->showInfoScreen();
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab("settings");
                $this->setSettingsSubTabs("style");
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;


            case 'ilpermissiongui':
                if ($this->in_administration) {
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

                if (($cmd == "create") && ($this->edit_request->getNewType() == "term")) {
                    $this->ctrl->setCmd("create");
                    $this->ctrl->setCmdClass("ilGlossaryTermGUI");
                    $this->executeCommand();
                    return;
                } else {
                    if ($cmd != "quickList") {
                        if ($this->in_administration ||
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
                    $this->$cmd();
                }
                break;
        }

        if ($cmd != "quickList") {
            if (!$this->in_administration && !$this->getCreationMode()) {
                $this->tpl->printToStdout();
            }
        } else {
            $this->tpl->printToStdout(false);
        }
    }

    /**
     * Get glossary
     */
    public function getGlossary(): ?ilObjGlossary
    {
        /** @var ilObjGlossary $glossary */
        $glossary = $this->object;
        if (isset($glossary) && $glossary->getType() == "glo") {
            return $glossary;
        }
        return null;
    }

    protected function assignObject(): void
    {
        $this->object = new ilObjGlossary($this->id, true);
    }

    protected function initCreateForm(string $new_type): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt($new_type . "_new"));

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

        // didactic template
        $form = $this->initDidacticTemplate($form);

        $form->addCommandButton("save", $this->lng->txt($new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    public function importObject(): void
    {
        $this->createObject();
    }

    public function saveObject(): void
    {
        $new_type = $this->edit_request->getNewType();

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            throw new ilPermissionException($this->lng->txt("no_create_permission"));
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
            $newObj->setVirtualMode("none");
            $newObj->create();

            $this->putObjectInTree($newObj);

            // apply didactic template?
            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }

            // always send a message
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("glo_added"), true);
            $this->ctrl->setParameterByClass(
                ilObjGlossaryGUI::class,
                "ref_id",
                $newObj->getRefId()
            );
            $this->ctrl->redirectByClass(
                [ilGlossaryEditorGUI::class, ilObjGlossaryGUI::class],
                "properties"
            );
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function showInfoScreen(): void
    {
        $this->getTemplate();
        $this->setTabs();
        $this->setLocator();
        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableNews();
        if ($this->access->checkAccess("write", "", $this->requested_ref_id)) {
            $info->enableNewsEditing();
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        self::addUsagesToInfo($info, $this->object->getId());

        $this->ctrl->forwardCommand($info);
    }

    /**
     * Add usages to info screen
     */
    public static function addUsagesToInfo(
        ilInfoScreenGUI $info,
        int $glo_id
    ): void {
        global $DIC;

        $lng = $DIC->language();
        $ilAccess = $DIC->access();

        $info->addSection($lng->txt("glo_usages"));
        $sms = ilObjSAHSLearningModule::getScormModulesForGlossary($glo_id);
        foreach ($sms as $sm) {
            $link = false;
            $refs = ilObject::_getAllReferences($sm);
            foreach ($refs as $ref) {
                if ($link === false && $ilAccess->checkAccess("write", "", $ref)) {
                    $link = ilLink::_getLink($ref, 'sahs');
                }
            }

            $entry = ilObject::_lookupTitle($sm);
            if ($link !== false) {
                $entry = "<a href='" . $link . "' target='_top'>" . $entry . "</a>";
            }

            $info->addProperty($lng->txt("obj_sahs"), $entry);
        }
    }


    public function viewObject(): void
    {
        if ($this->in_administration) {
            parent::viewObject();
            return;
        }

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt("permission_denied"));
        }
    }

    public function properties(): void
    {
        $this->checkPermission("write");

        $this->setSettingsSubTabs("general_settings");

        $this->initSettingsForm();

        // Edit ecs export settings
        $ecs = new ilECSGlossarySettings($this->object);
        $ecs->addSettingsToForm($this->form, 'glo');

        $this->tpl->setContent($this->form->getHTML());
    }

    public function initSettingsForm(
        string $a_mode = "edit"
    ): void {
        $obj_service = $this->getObjectService();

        $this->form = new ilPropertyFormGUI();

        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setRequired(true);
        $this->form->addItem($title);

        // description
        $desc = new ilTextAreaInputGUI($this->lng->txt("desc"), "description");
        $this->form->addItem($desc);

        // glossary mode
        // for layout of this property see https://mantis.ilias.de/view.php?id=31833
        $glo_mode = new ilRadioGroupInputGUI($this->lng->txt("glo_content_assembly"), "glo_mode");
        //$glo_mode->setInfo($this->lng->txt("glo_mode_desc"));
        $op1 = new ilRadioOption($this->lng->txt("glo_mode_normal"), "none", $this->lng->txt("glo_mode_normal_info"));
        $glo_mode->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("glo_collection"), "coll", $this->lng->txt("glo_collection_info"));
        $glo_mode->addOption($op2);

        $glo_mode2 = new ilRadioGroupInputGUI("", "glo_mode2");
        $glo_mode2->setValue("level");
        $op3 = new ilRadioOption($this->lng->txt("glo_mode_level"), "level", $this->lng->txt("glo_mode_level_info"));
        $glo_mode2->addOption($op3);
        $op4 = new ilRadioOption($this->lng->txt("glo_mode_subtree"), "subtree", $this->lng->txt("glo_mode_subtree_info"));
        $glo_mode2->addOption($op4);
        $op2->addSubItem($glo_mode2);
        $this->form->addItem($glo_mode);


        $this->lng->loadLanguageModule("rep");
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $this->form->addItem($section);

        // online
        $online = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
        $online->setValue("y");
        $online->setInfo($this->lng->txt("glo_online_info"));
        $this->form->addItem($online);

        /*
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('glo_content_settings'));
        $this->form->addItem($section);*/


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
        $snl->setSuffix($this->lng->txt("characters"));
        $op1->addSubItem($snl);

        $pres_mode->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("glo_full_definitions"), "full_def", $this->lng->txt("glo_full_definitions_info"));
        $pres_mode->addOption($op2);
        $this->form->addItem($pres_mode);

        // show taxonomy
        $show_tax = null;
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
            $mode1 = $this->object->getVirtualMode() === "none"
                ? "none"
                : "coll";
            $mode2 = $this->object->getVirtualMode() !== "none"
                ? $this->object->getVirtualMode()
                : "level";
            $glo_mode->setValue($mode1);
            $glo_mode2->setValue($mode2);
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
            $ti->setInfo($this->lng->txt("glo_col_ordering_info"));
        }

        // save and cancel commands
        $this->form->addCommandButton("saveProperties", $this->lng->txt("save"));

        $this->form->setTitle($this->lng->txt("cont_glo_properties"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }


    public function saveProperties(): void
    {
        $obj_service = $this->getObjectService();

        $this->initSettingsForm();
        if ($this->form->checkInput()) {
            $this->object->setTitle($this->form->getInput("title"));
            $this->object->setDescription($this->form->getInput("description"));
            $this->object->setOnline(ilUtil::yn2tf($this->form->getInput("cobj_online")));
            $glo_mode = $this->form->getInput("glo_mode") === "none"
                ? $this->form->getInput("glo_mode")
                : $this->form->getInput("glo_mode2");
            $this->object->setVirtualMode($glo_mode);
            $this->object->setActiveDownloads(ilUtil::yn2tf($this->form->getInput("glo_act_downloads")));
            $this->object->setPresentationMode($this->form->getInput("pres_mode"));
            $this->object->setSnippetLength($this->form->getInput("snippet_length"));
            $this->object->setShowTaxonomy($this->form->getInput("show_tax"));
            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

            // field order of advanced metadata
            $adv_ap = new ilGlossaryAdvMetaDataAdapter($this->object->getRefId());
            $cols = $adv_ap->getColumnOrder();
            if (count($cols) > 1) {
                $adv_ap->saveColumnOrder($this->form->getInput("field_order"));
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
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, "properties");
            }
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function listTerms(): void
    {
        $this->showTaxonomy();

        // term
        $ti = new ilTextInputGUI($this->lng->txt("cont_new_term"), "new_term");
        $ti->setMaxLength(80);
        $ti->setSize(20);
        $this->toolbar->addInputItem($ti, true);

        // language
        $this->lng->loadLanguageModule("meta");
        $lang = ilMDLanguageItem::_getLanguages();
        $session_lang = $this->term_manager->getSessionLang();
        if ($session_lang != "") {
            $s_lang = $session_lang;
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

        $tab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
        $this->tpl->setContent($tab->getHTML());
    }

    public function actTaxonomy(): void
    {
        $this->ctrl->setParameter($this, "show_tax", 1);
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * Hide Taxonomy
     */
    public function deactTaxonomy(): void
    {
        $this->ctrl->setParameter($this, "show_tax", "");
        $this->ctrl->redirect($this, "listTerms");
    }


    /**
     * add term
     */
    public function addTerm(): void
    {
        $new_term = $this->edit_request->getNewTerm();
        if ($new_term == "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_please_enter_a_term"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        // add term
        $lang = $this->edit_request->getTermLanguage();
        $term = new ilGlossaryTerm();
        $term->setGlossary($this->getGlossary());
        $term->setTerm($new_term);
        $term->setLanguage($lang);
        $term->create();

        $this->term_manager->setSessionLang($lang);

        // add first definition
        $def = new ilGlossaryDefinition();
        $def->setTermId($term->getId());
        $def->setTitle($new_term);
        $def->create();

        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term->getId());
        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", $def->getId());
        $this->ctrl->redirectByClass(array("ilglossarytermgui",
            "iltermdefinitioneditorgui", "ilglossarydefpagegui"), "edit");
    }

    /**
     * move a definition up
     */
    public function moveDefinitionUp(): void
    {
        $definition = new ilGlossaryDefinition(
            $this->edit_request->getDefinitionId()
        );
        $definition->moveUp();

        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * move a definition down
     */
    public function moveDefinitionDown(): void
    {
        $definition = new ilGlossaryDefinition(
            $this->edit_request->getDefinitionId()
        );
        $definition->moveDown();

        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * deletion confirmation screen
     */
    public function confirmDefinitionDeletion(): void
    {
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

        $definition = new ilGlossaryDefinition(
            $this->edit_request->getDefinitionId()
        );
        $page_gui = new ilGlossaryDefPageGUI($definition->getId());
        $page_gui->setTemplateOutput(false);
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->requested_ref_id);
        $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->requested_ref_id);
        $page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->requested_ref_id);
        $output = $page_gui->preview();

        $cgui->addItem(
            "def",
            $this->edit_request->getDefinitionId(),
            $term->getTerm() . $output
        );

        $this->tpl->setContent($cgui->getHTML());
    }

    public function cancelDefinitionDeletion(): void
    {
        $this->ctrl->redirect($this, "listTerms");
    }

    public function deleteDefinition(): void
    {
        $definition = new ilGlossaryDefinition($this->edit_request->getDefinitionId());
        $definition->delete();
        $this->ctrl->redirect($this, "listTerms");
    }

    public function export(): void
    {
        $this->checkPermission("write");
        $glo_exp = new ilGlossaryExport($this->getGlossary());
        $glo_exp->buildExportFile();
        $this->ctrl->redirectByClass("ilexportgui", "");
    }

    /**
     * create html package
     */
    public function exportHTML(): void
    {
        $glo_exp = new ilGlossaryExport($this->getGlossary(), "html");
        $glo_exp->buildExportFile();
        $this->ctrl->redirectByClass("ilexportgui", "");
    }

    /**
     * download export file
     */
    public function publishExportFile(): void
    {
        $files = $this->edit_request->getFiles();
        if (count($files) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            $this->ctrl->redirectByClass("ilexportgui", "");
        }
        if (count($files) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_max_one_item"));
            $this->ctrl->redirectByClass("ilexportgui", "");
        }

        $file = explode(":", $files[0]);
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

    public function confirmTermDeletion(): void
    {
        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        // check ids
        foreach ($ids as $term_id) {
            $term_glo_id = ilGlossaryTerm::_lookGlossaryID($term_id);
            if ($term_glo_id != $this->object->getId() && !ilGlossaryTermReferences::isReferenced([$this->object->getId()], $term_id)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("glo_term_must_belong_to_glo"), true);
                $this->ctrl->redirect($this, "listTerms");
            }
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelTermDeletion");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteTerms");

        foreach ($ids as $id) {
            $term = new ilGlossaryTerm($id);

            $add = "";
            $nr = ilGlossaryTerm::getNumberOfUsages($id);
            if ($nr > 0) {
                $this->ctrl->setParameterByClass(
                    "ilglossarytermgui",
                    "term_id",
                    $id
                );

                if (ilGlossaryTermReferences::isReferenced([$this->object->getId()], $id)) {
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

    public function cancelTermDeletion(): void
    {
        $this->ctrl->redirect($this, "listTerms");
    }

    public function deleteTerms(): void
    {
        $ids = $this->edit_request->getIds();
        foreach ($ids as $id) {
            if (ilGlossaryTermReferences::isReferenced([$this->object->getId()], $id)) {
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

    protected function setLocator(): void
    {
        if (strtolower($this->edit_request->getBaseClass()) != "ilglossaryeditorgui") {
            parent::setLocator();
        } elseif (is_object($this->object)) {
            $gloss_loc = new ilGlossaryLocatorGUI();
            if (is_object($this->term)) {
                $gloss_loc->setTerm($this->term);
            }
            $gloss_loc->setGlossary($this->getGlossary());
            $gloss_loc->display();
        }
    }

    public function view(): void
    {
        $this->viewObject();
    }

    public function addDefinition(): void
    {
        $term_id = $this->edit_request->getTermId();

        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($term_id);
        if ($term_glo_id != $this->object->getId()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("glo_term_must_belong_to_glo"), true);
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

    public function getTemplate(): void
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

    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent("glo");

        // list terms
        $cmd = $this->ctrl->getCmd();
        $force_active = ($cmd == "" || $cmd == "listTerms");
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
            "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->object->getRefId(),
            "_top"
        );
    }

    public function setSettingsSubTabs(string $a_active): void
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
                $this->ctrl->getLinkTargetByClass("ilobjectcontentstylesettingsgui", '')
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


    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ctrl = $DIC->ctrl();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $ctrl->setParameterByClass("ilGlossaryPresentationGUI", "ref_id", $a_target);
            $ctrl->redirectByClass("ilGlossaryPresentationGUI", "");
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            $ctrl->setParameterByClass("ilGlossaryPresentationGUI", "ref_id", $a_target);
            $ctrl->redirectByClass("ilGlossaryPresentationGUI", "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("no_permission"));
    }

    public function applyFilter(): void
    {
        $prtab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
        $prtab->resetOffset();
        $prtab->writeFilterToSession();
        $this->listTerms();
    }

    public function resetFilter(): void
    {
        $prtab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
        $prtab->resetOffset();
        $prtab->resetFilter();
        $this->listTerms();
    }


    ////
    //// Style related functions
    ////

    public function setContentStyleSheet(
        ilGlobalTemplateInterface $a_tpl = null
    ): void {
        if ($a_tpl != null) {
            $ctpl = $a_tpl;
        } else {
            $ctpl = $this->tpl;
        }

        $this->content_style_gui->addCss($ctpl, $this->object->getRefId());
    }

    /**
     * Get public access value for export table
     */
    public function getPublicAccessColValue(
        string $a_type,
        string $a_file
    ): string {
        if ($this->object->getPublicExportFile($a_type) == $a_file) {
            return $this->lng->txt("yes");
        }

        return " ";
    }

    /**
     * Show taxonomy
     */
    public function showTaxonomy(): void
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
     */
    public function editGlossaries(): void
    {
        $this->tabs->setTabActive("settings");
        $this->setSettingsSubTabs("glossaries");

        $this->toolbar->addButton(
            $this->lng->txt("add"),
            $this->ctrl->getLinkTarget($this, "showGlossarySelector")
        );

        $tab = new ilGlossaryAutoLinkTableGUI($this->getGlossary(), $this, "editGlossaries");

        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Show auto glossary selection
     */
    public function showGlossarySelector(): void
    {
        $this->tabs->setTabActive("settings");
        $this->setSettingsSubTabs("glossaries");

        $exp = new ilSearchRootSelector($this->ctrl->getLinkTarget($this, 'showGlossarySelector'));
        $search_root_expand = $this->edit_request->getSearchRootExpand();
        $exp->setExpand($search_root_expand ?: $this->tree->readRootId());
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showGlossarySelector'));
        $exp->setTargetClass(get_class($this));
        $exp->setCmd('confirmGlossarySelection');
        $exp->setClickableTypes(array("glo"));
        $exp->addFilter("glo");

        // build html-output
        $exp->setOutput(0);
        $this->tpl->setContent($exp->getOutput());
    }

    public function confirmGlossarySelection(): void
    {
        $cgui = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, "glo_ref_id", $this->edit_request->getGlossaryRefId());
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("glo_link_glo_in_glo"));
        $cgui->setCancel($this->lng->txt("no"), "selectGlossary");
        $cgui->setConfirm($this->lng->txt("yes"), "selectGlossaryLink");
        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * Select a glossary and link all its terms
     */
    public function selectGlossaryLink(): void
    {
        $glo_ref_id = $this->edit_request->getGlossaryRefId();
        $this->object->autoLinkGlossaryTerms($glo_ref_id);
        $this->selectGlossary();
    }


    /**
     * Select auto glossary
     */
    public function selectGlossary(): void
    {
        $glos = $this->object->getAutoGlossaries();
        $glo_ref_id = $this->edit_request->getGlossaryRefId();
        $glo_id = ilObject::_lookupObjId($glo_ref_id);
        if (!in_array($glo_id, $glos)) {
            $glos[] = $glo_id;
        }
        $this->object->setAutoGlossaries($glos);
        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editGlossaries");
    }

    public function removeGlossary(): void
    {
        $this->object->removeAutoGlossary($this->edit_request->getGlossaryId());
        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editGlossaries");
    }

    /**
     * Copy terms to clipboard
     */
    public function copyTerms(): void
    {
        $items = $this->edit_request->getIds();
        if (count($items) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        $this->user->clipboardDeleteObjectsOfType("term");

        // put them into the clipboard
        $time = date("Y-m-d H:i:s");
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
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("glo_selected_terms_have_been_copied"), true);
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * Add terms to be referenced to clipboard
     */
    public function referenceTerms(): void
    {
        $items = $this->edit_request->getIds();
        if (count($items) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }

        $this->user->clipboardDeleteObjectsOfType("term");

        // put them into the clipboard
        $time = date("Y-m-d H:i:s");
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
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("glo_selected_terms_have_been_copied"), true);
        $this->ctrl->redirect($this, "listTerms");
    }


    public function clearClipboard(): void
    {
        $this->user->clipboardDeleteObjectsOfType("term");
        $this->ctrl->redirect($this, "listTerms");
    }

    public function pasteTerms(): void
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
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTerms");
    }
}
