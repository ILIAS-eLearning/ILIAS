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

use ILIAS\Glossary\Settings\SettingsGUI;

/**
 * GUI class for ilGlossary
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjGlossaryGUI: ilGlossaryTermGUI, ilMDEditorGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilInfoScreenGUI, ilCommonActionDispatcherGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilTaxonomySettingsGUI, ilExportGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilObjectMetaDataGUI, ilGlossaryForeignTermCollectorGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ilTermDefinitionBulkCreationGUI
 * @ilCtrl_Calls ilObjGlossaryGUI: ILIAS\Glossary\Settings\SettingsGUI
 */
class ilObjGlossaryGUI extends ilObjectGUI implements \ILIAS\Taxonomy\Settings\ModifierGUIInterface
{
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected ?\ILIAS\Glossary\Taxonomy\TaxonomyManager $tax_manager = null;
    protected \ILIAS\Glossary\InternalDomainService $domain;
    protected \ILIAS\Glossary\InternalGUIService $gui;
    protected \ILIAS\DI\UIServices $ui;
    protected \ILIAS\Taxonomy\Service $taxonomy;
    protected ilRbacSystem $rbacsystem;
    protected ilPropertyFormGUI $form;
    protected int $tax_node = 0;
    protected ilObjTaxonomy $tax;
    protected $tax_id;
    protected bool $in_administration = false;
    protected \ILIAS\Glossary\Presentation\GUIService $gui_presentation_service;
    protected ilTermDefinitionBulkCreationGUI $term_def_bulk_gui;
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
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;
    protected array $modals_to_render = [];
    protected string $requested_table_glossary_term_list_action = "";
    /**
     * @var string[]
     */
    protected array $requested_table_glossary_term_list_ids = [];

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $service = $DIC->glossary()->internal();
        $this->gui = $gui = $service->gui();
        $this->domain = $domain = $service->domain();

        $this->lng = $domain->lng();
        $this->user = $domain->user();
        $this->setting = $domain->settings();
        $this->access = $domain->access();
        $this->rbacsystem = $domain->rbac()->system();
        $this->log = $domain->log();

        $this->ctrl = $gui->ctrl();
        $this->toolbar = $gui->toolbar();
        $this->tabs = $gui->tabs();
        $this->help = $gui->help();
        $this->ui = $gui->ui();
        $this->ui_fac = $gui->ui()->factory();
        $this->ui_ren = $gui->ui()->renderer();
        $this->global_screen = $gui->globalScreen();
        $this->gui_presentation_service = $gui->presentation();

        $this->edit_request = $gui->editing()->request();
        $this->term_perm = ilGlossaryTermPermission::getInstance();
        $this->requested_table_glossary_term_list_action = $this->edit_request->getTableGlossaryTermListAction();
        $this->requested_table_glossary_term_list_ids = $this->edit_request->getTableGlossaryTermListIds();

        $this->ctrl->saveParameter($this, array("ref_id"));
        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("glo");

        $this->type = "glo";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        // determine term id and check whether it is valid (belongs to
        // current glossary)
        if (($this->requested_table_glossary_term_list_action == "editTerm"
            || $this->requested_table_glossary_term_list_action == "editDefinition")
            && !empty($this->requested_table_glossary_term_list_ids)) {
            $this->term_id = $this->requested_table_glossary_term_list_ids[0];
        } else {
            $this->term_id = $this->edit_request->getTermId();
        }
        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
        if ($this->term_id > 0 && $term_glo_id != $this->object->getId()
            && !ilGlossaryTermReferences::isReferenced([$this->object->getId()], $this->term_id)) {
            $this->term_id = 0;
        }
        $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $this->term_id);

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
            $this->term_manager = $domain->term(
                $this->getGlossary(),
                $this->user->getId()
            );
            $this->tax_manager = $domain->taxonomy(
                $this->getGlossary()
            );
        }

        $this->term_def_bulk_gui = $this->gui_presentation_service
            ->TermDefinitionBulkCreationGUI($this->getGlossary());

        $this->in_administration =
            (strtolower($this->edit_request->getBaseClass()) == "iladministrationgui");
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        if (is_object($this->object)) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
            $this->taxonomy = $DIC->taxonomy();
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
                $this->ctrl->setReturn($this, "listTerms");
                $term_gui = new ilGlossaryTermGUI($this->term_id);
                $term_gui->setGlossary($this->getGlossary());
                $this->ctrl->forwardCommand($term_gui);
                break;

            case "ilinfoscreengui":
                $this->addHeaderAction();
                $this->showInfoScreen();
                $this->tabs->activateTab("info_short");
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
                $this->prepareOutput();
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilTaxonomySettingsGUI::class):
                $this->getTemplate();
                $this->setTabs();
                $this->setLocator();
                $this->addHeaderAction();
                $this->tabs->activateTab("settings");
                $this->setSettingsSubTabs("taxonomy");

                $this->ctrl->setReturn($this, "properties");
                $tax_gui = $this->taxonomy->gui()->getSettingsGUI(
                    $this->object->getId(),
                    $this->lng->txt("glo_tax_info"),
                    false,
                    $this
                );
                $ret = $this->ctrl->forwardCommand($tax_gui);
                break;

            case "ilexportgui":
                $this->getTemplate();
                $this->setTabs();
                $this->tabs->activateTab("export");
                $this->setLocator();
                $exp_gui = new ilExportGUI($this);
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

            case "iltermdefinitionbulkcreationgui":
                $this->ctrl->setReturn($this, "listTerms");
                $this->ctrl->forwardCommand($this->term_def_bulk_gui);
                break;

            case strtolower(SettingsGUI::class):
                $this->getTemplate();
                $this->setTabs();
                $this->tabs->activateTab("settings");
                $this->setLocator();
                $this->setSettingsSubTabs("general_settings");
                $this->checkPermission("write");
                $gui = $this->gui->settings()->settingsGUI(
                    $this->object->getId(),
                    $this->requested_ref_id
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listTerms");

                if (($cmd == "create") && ($this->edit_request->getNewType() == "term")) {
                    $this->ctrl->redirectByClass(ilGlossaryTermGUI::class, "create");
                } else {
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
                    $this->$cmd();
                }
                break;
        }

        if (!$this->in_administration && !$this->getCreationMode()) {
            $this->tpl->printToStdout();
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
        $this->ctrl->redirectByClass(SettingsGUI::class);
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
        $op2 = new ilRadioOption($this->lng->txt("glo_collection"), "coll", $this->lng->txt("glo_collection_info"));
        if (!empty($this->object->getGlossariesForCollection()) && $this->object->isVirtual()) {
            $op1->setDisabled(true);
            $op2->setDisabled(true);
            $glo_mode->setInfo($this->lng->txt("glo_change_to_standard_unavailable_info"));
        }
        if (!empty(ilGlossaryTerm::getTermsOfGlossary($this->object->getId())) && !$this->object->isVirtual()) {
            $op1->setDisabled(true);
            $op2->setDisabled(true);
            $glo_mode->setInfo($this->lng->txt("glo_change_to_collection_unavailable_info"));
        }
        $glo_mode->addOption($op1);
        $glo_mode->addOption($op2);
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

        // flashcard training
        $flash_active = new ilCheckboxInputGUI($this->lng->txt("glo_flashcard_training"), "flash_active");
        $flash_active->setValue("y");
        $flash_active->setInfo($this->lng->txt("glo_flashcard_training_info"));

        //flashcard training mode
        $flash_mode = new ilRadioGroupInputGUI($this->lng->txt("glo_mode"), "flash_mode");
        $op1 = new ilRadioOption($this->lng->txt("glo_term_vs_def"), "term", $this->lng->txt("glo_term_vs_def_info"));
        $flash_mode->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("glo_def_vs_term"), "def", $this->lng->txt("glo_def_vs_term_info"));
        $flash_mode->addOption($op2);
        $flash_active->addSubItem($flash_mode);
        $this->form->addItem($flash_active);

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
            $glo_mode->setValue($mode1);
            $pres_mode->setValue($this->object->getPresentationMode());
            $snl->setValue($this->object->getSnippetLength());

            $down->setChecked($this->object->isActiveDownloads());
            $flash_active->setChecked($this->object->isActiveFlashcards());
            $flash_mode->setValue($this->object->getFlashcardsMode());

            // additional features
            $feat = new ilFormSectionHeaderGUI();
            $feat->setTitle($this->lng->txt('obj_features'));
            $this->form->addItem($feat);

            ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $this->object->getId(),
                $this->form,
                array(
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                        ilObjectServiceSettingsGUI::TAXONOMIES
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
            $glo_mode = $this->form->getInput("glo_mode") ?: $this->object->getVirtualMode();
            $this->object->setVirtualMode($glo_mode);
            $this->object->setActiveDownloads(ilUtil::yn2tf($this->form->getInput("glo_act_downloads")));
            $this->object->setPresentationMode($this->form->getInput("pres_mode"));
            $this->object->setSnippetLength($this->form->getInput("snippet_length"));
            $this->object->setActiveFlashcards(ilUtil::yn2tf($this->form->getInput("flash_active")));
            $this->object->setFlashcardsMode($this->form->getInput("flash_mode"));
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
            ilGlossaryTerm::setShortTextsDirty($this->object->getId());

            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $this->form,
                array(
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                    ilObjectServiceSettingsGUI::TAXONOMIES
                )
            );

            // Update ecs export settings
            $ecs = new ilECSGlossarySettings($this->object);
            if ($ecs->handleSettingsUpdate($this->form)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, "properties");
            }
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function getProperties(
        int $tax_id
    ): array {
        $active = $this->object->getShowTaxonomy();
        $value = $active
            ? $this->lng->txt("yes")
            : $this->lng->txt("no");

        return [
            $this->lng->txt("glo_show_in_presentation") => $value
        ];
    }

    public function getActions(
        int $tax_id
    ): array {
        $actions = [];
        $this->ctrl->setParameterByClass(self::class, "glo_tax_id", $tax_id);
        $active = $this->object->getShowTaxonomy();
        if (!$active) {
            $actions[] = $this->ui->factory()->button()->shy(
                $this->lng->txt("glo_show_in_presentation_on"),
                $this->ctrl->getLinkTargetByClass(
                    self::class,
                    "showTaxInPresentation"
                )
            );
        } else {
            $actions[] = $this->ui->factory()->button()->shy(
                $this->lng->txt("glo_show_in_presentation_off"),
                $this->ctrl->getLinkTargetByClass(
                    self::class,
                    "hideTaxInPresentation"
                )
            );
        }
        $this->ctrl->setParameterByClass(self::class, "glo_tax_id", null);

        return $actions;
    }

    protected function showTaxInPresentation(): void
    {
        $this->object->setShowTaxonomy(true);
        $this->object->update();
        $this->ctrl->redirectByClass(ilTaxonomySettingsGUI::class);
    }

    protected function hideTaxInPresentation(): void
    {
        $this->object->setShowTaxonomy(false);
        $this->object->update();
        $this->ctrl->redirectByClass(ilTaxonomySettingsGUI::class);
    }

    public function listTerms(): void
    {
        $this->tabs->activateTab("content");

        $this->showTaxonomy();

        $panel_html = "";
        $modals = "";
        $tab_html = "";
        if ($this->object->isVirtual()) {
            $this->showToolbarForCollection();
            $panel = $this->showSelectedGlossariesForCollection();
            $panel_html = $this->ui_ren->render($panel);
            $modals = $this->ui_ren->render($this->getModalsToRender());
        } else {
            $this->showToolbarForStandard();
            $table = $this->domain->table()->getTermListTable($this->getGlossary(), $this->tax_node)->getComponent();
            $tab_html = $this->ui_ren->render($table);
        }

        $this->tabs->activateTab("content");

        $this->tpl->setContent($panel_html . $modals . $tab_html);
    }

    /**
     * @return \ILIAS\UI\Component\Modal\Interruptive[]
     */
    protected function getModalsToRender(): array
    {
        return $this->modals_to_render;
    }

    public function showToolbarForStandard(): void
    {
        // term
        $ti = new ilTextInputGUI($this->lng->txt("cont_new_term"), "new_term");
        $ti->setMaxLength(80);
        $ti->setSize(20);
        $this->toolbar->addInputItem($ti, true);

        // language
        $this->lng->loadLanguageModule("meta");
        $lang = $this->domain->metadata()->getLOMLanguagesForSelectInputs();
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

        $this->term_def_bulk_gui->modifyToolbar($this->toolbar);
    }

    public function showToolbarForCollection(): void
    {
        $modal = $this->showModalForCollection();
        $button = $this->ui_fac->button()->standard($this->lng->txt("glo_add_glossary"), "")->withOnClick($modal->getShowSignal());
        $this->modals_to_render[] = $modal;
        $this->toolbar->addComponent($button);
    }

    /**
     * @return \ILIAS\UI\Component\Component[]
     */
    public function showSelectedGlossariesForCollection(): array
    {
        $items = [];
        $glo_ids = $this->object->getAllGlossaryIds(true);
        $at_least_one_glossary = false;
        foreach ($glo_ids as $glo_id) {
            if ($this->object->getId() === $glo_id) {
                continue;
            }
            $glossary = new ilObjGlossary($glo_id, false);
            $glo_ref_id = current(ilObject::_getAllReferences($glossary->getId()));
            $glo_link = $this->ui_fac->link()->standard($glossary->getTitle(), ilLink::_getLink($glo_ref_id));
            $glo_item = $this->ui_fac->item()->standard($glo_link);
            $glo_item = $glo_item->withDescription($glossary->getDescription());
            $form_action = $this->ctrl->getFormActionByClass(ilObjGlossaryGUI::class, "removeGlossaryFromCollection");
            $delete_modal = $this->ui_fac->modal()->interruptive(
                "",
                $this->lng->txt("glo_really_remove_from_collection"),
                $form_action
            )->withAffectedItems([
                $this->ui_fac->modal()->interruptiveItem()->standard(
                    $glossary->getId(),
                    $glossary->getTitle(),
                    $this->ui_fac->image()->standard(
                        ilObject::_getIcon($glossary->getId(), "small", $glossary->getType()),
                        $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $glossary->getType())
                    )
                )
            ]);
            $actions = $this->ui_fac->dropdown()->standard([
                $this->ui_fac->button()->shy($this->lng->txt("remove"), "")->withOnClick($delete_modal->getShowSignal()),
            ]);
            $glo_item = $glo_item->withActions($actions);

            $items[] = $glo_item;
            $this->modals_to_render[] = $delete_modal;
            $at_least_one_glossary = true;
        }

        $components = [];
        if (!$at_least_one_glossary) {
            $message_box = $this->ui_fac->messageBox()->info($this->lng->txt("glo_collection_empty_info"));
            $components[] = $message_box;
        }
        if (!empty($items)) {
            $item_group = $this->ui_fac->item()->group($this->lng->txt("glo_selected_glossaries_info"), $items);
            $panel = $this->ui_fac->panel()->listing()->standard(
                $this->lng->txt("glo_selected_glossaries"),
                [$item_group]
            );
            $components[] = $panel;
        }

        return $components;
    }

    public function showModalForCollection(): ILIAS\UI\Component\Modal\RoundTrip
    {
        $exp = new ilStandardGlossarySelectorGUI(
            $this,
            "showModalForCollection",
            $this,
            "saveGlossaryForCollection",
            "sel_glo_ref_id"
        );
        $modal = $this->ui_fac->modal()->roundtrip(
            $this->lng->txt("glo_add_to_collection"),
            $this->ui_fac->legacy(!$exp->handleCommand() ? $exp->getHTML(true) : "")
        );

        return $modal;
    }

    public function saveGlossaryForCollection(): void
    {
        $selected_glo = new ilObjGlossary($this->edit_request->getSelectedGlossaryRefId(), true);
        if ($selected_glo->getId() === $this->object->getId()) {
            $this->tpl->setOnScreenMessage("info", $this->lng->txt("glo_selected_glossary_is_current_info"), true);
        } else {
            $this->object->addGlossaryForCollection($selected_glo->getId());
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("glo_added_to_collection_info"), true);
        }
        $this->ctrl->redirect($this, "listTerms");
    }

    public function removeGlossaryFromCollection(): void
    {
        $glo_id = $this->edit_request->getGlossaryIdInModal();
        $this->object->removeGlossaryFromCollection($glo_id);
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("glo_removed_from_collection_info"), true);
        $this->ctrl->redirect($this, "listTerms");
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

        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term->getId());
        $this->ctrl->redirectByClass(array("ilglossarytermgui",
            "iltermdefinitioneditorgui", "ilglossarydefpagegui"), "edit");
    }

    /**
     * create html package
     */
    public function exportHTML(): void
    {
        $glo_exp = new ilGlossaryExport($this->getGlossary(), "html");
        $glo_exp->buildExportFileHTML();
        $this->ctrl->redirectByClass("ilexportgui", "");
    }

    /**
     * download export file
     */
    public function publishExportFile(): void
    {
        $files = $this->edit_request->getFiles();
        if (count($files) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirectByClass("ilexportgui", "");
        }
        if (count($files) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_max_one_item"), true);
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

    public function deleteTerms(): void
    {
        if (!empty($this->edit_request->getTermIdsInModal())
            && $ids = $this->edit_request->getTermIdsInModal()) {
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
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
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

    public function getTemplate(): void
    {
        $this->tpl->loadStandardTemplate();

        $title = $this->object->getTitle();


        if ($this->term_id > 0) {
            $this->tpl->setTitle($this->lng->txt("term") . ": " .
                ilGlossaryTerm::_lookGlossaryTerm($this->term_id));
        } else {
            parent::setTitleAndDescription();
            $this->tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_glo.svg"));
            $this->tpl->setTitle($this->lng->txt("glo") . ": " . $title);
        }
    }

    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent("glo");

        // list terms
        $cmd = $this->ctrl->getCmd();
        $force_active = ($cmd == "" || $cmd == "listTerms");
        $this->tabs_gui->addTab(
            "content",
            $this->lng->txt("content"),
            $this->ctrl->getLinkTarget($this, "listTerms")
        );

        $this->tabs_gui->addTab(
            "info_short",
            $this->lng->txt("info_short"),
            $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
        );

        // properties
        if ($this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings") . " new",
                $this->ctrl->getLinkTargetByClass(SettingsGUI::class)
            );

            // meta data
            $mdgui = new ilObjectMetaDataGUI($this->object, "term");
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "meta_data",
                    $this->lng->txt("meta_data"),
                    $mdtab
                );
            }

            // export
            $this->tabs_gui->addTab(
                "export",
                $this->lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        // permissions
        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
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

            $this->taxonomy->gui()->addSettingsSubTab($this->getObject()->getId());

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
        $ctrl = $this->ctrl;

        if (is_null($this->tax_manager) || !$this->tax_manager->showInEditing()) {
            return;
        }

        $tool_context = $this->global_screen->tool()->context()->current();

        $tax_id = $this->tax_manager->getTaxonomyId();

        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::SHOW_TAX_TREE,
            true
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_TREE_GUI_PATH,
            $ctrl->getCurrentClassPath()
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_ID,
            $tax_id
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_TREE_CMD,
            "listTerms"
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_TREE_PARENT_CMD,
            "showTaxonomy"
        );
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

        $table = $this->domain->table()->getGlossaryAutoLinkTable($this->getGlossary())->getComponent();

        $this->tpl->setContent($this->ui_ren->render($table));
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
        $this->object->removeAutoGlossary($this->edit_request->getGlossaryIdInModal());
        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editGlossaries");
    }

    /**
     * Copy terms to clipboard
     */
    public function copyTerms(): void
    {
        $this->putTermsIntoClipBoard();

        ilEditClipboard::setAction("copy");
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("glo_selected_terms_have_been_copied"), true);
        $this->ctrl->redirect($this, "listTerms");
    }

    /**
     * Add terms to be referenced to clipboard
     */
    public function referenceTerms(): void
    {
        $this->putTermsIntoClipBoard();

        ilEditClipboard::setAction("link");
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("glo_selected_terms_have_been_copied"), true);
        $this->ctrl->redirect($this, "listTerms");
    }

    protected function putTermsIntoClipBoard(): void
    {
        $this->user->clipboardDeleteObjectsOfType("term");
        $time = date("Y-m-d H:i:s");
        $order = 0;
        if (($this->requested_table_glossary_term_list_action === "copyTerms"
                || $this->requested_table_glossary_term_list_action === "referenceTerms")
            && !empty($this->requested_table_glossary_term_list_ids)
            && $this->requested_table_glossary_term_list_ids[0] === "ALL_OBJECTS"
        ) {
            $terms = $this->object->getTermList(
                "",
                "",
                "",
                $this->tax_node,
                true,
                true,
                null,
                false,
                true
            );
            foreach ($terms as $term) {
                $this->user->addObjectToClipboard(
                    (int) $term["id"],
                    "term",
                    ilGlossaryTerm::_lookGlossaryTerm((int) $term["id"]),
                    0,
                    $time,
                    $order
                );
            }
        } elseif ($this->requested_table_glossary_term_list_action === "copyTerms"
            || $this->requested_table_glossary_term_list_action === "referenceTerms") {
            foreach ($this->requested_table_glossary_term_list_ids as $term_id) {
                $this->user->addObjectToClipboard(
                    (int) $term_id,
                    "term",
                    ilGlossaryTerm::_lookGlossaryTerm((int) $term_id),
                    0,
                    $time,
                    $order
                );
            }
        }
        if (empty($this->requested_table_glossary_term_list_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTerms");
        }
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
