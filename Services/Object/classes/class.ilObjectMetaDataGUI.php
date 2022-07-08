<?php declare(strict_types=1);

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
 * Class ilObjectMetaDataGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
*
* @ilCtrl_Calls ilObjectMetaDataGUI: ilMDEditorGUI, ilAdvancedMDSettingsGUI, ilPropertyFormGUI, ilTaxMDGUI, ilObjTaxonomyGUI
*/
class ilObjectMetaDataGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ?ilLogger $logger = null;

    protected bool $in_workspace;
    /** @var string|string[]|null */
    protected $sub_type;
    protected ?int $sub_id;
    /**
     * @var bool false, e.g. for portfolios
     */
    protected bool $in_repository = true;
    protected ilObject $object;
    protected int $obj_id = 0;
    protected string $obj_type = "";
    protected int $ref_id = 0;
    protected ?array $md_observers = null;
    protected ?ilTaxMDGUI $tax_md_gui = null;
    protected ?ilObjTaxonomyGUI $tax_obj_gui = null;
    protected ?Closure $taxonomy_settings_form_manipulator = null;
    protected ?Closure $taxonomy_settings_form_saver = null;
    protected ?ilAdvancedMDRecordGUI $record_gui = null;

    // $adv_id - $adv_type - $adv_subtype:
    // Object, that defines the adv md records being used. Default is $this->object, but the
    // context may set another object (e.g. media pool for media objects)
    /**
     * @var ?int ref id or obj id, depending on $in_repository
     */
    protected ?int $adv_id = null;
    protected ?string $adv_type = null;
    protected ?string $adv_subtype = null;

    /**
     * @var ?int[] id filter for adv records
     */
    protected ?array $record_filter = null;
    private ilObjectRequestRetriever $retriever;
    
    public function __construct(
        ilObject $object = null,
        $sub_type = null,
        int $sub_id = null,
        bool $in_repository = true
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->logger = $GLOBALS['DIC']->logger()->obj();
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $DIC->refinery());
    
        $this->in_workspace = ((int) $this->retriever->getMaybeInt("wsp_id")) > 0;

        $this->sub_type = $sub_type;
        $this->sub_id = $sub_id;
        $this->in_repository = $in_repository;

        if (!$this->sub_type) {
            $this->sub_type = "-";
        }

        if ($object) {
            $this->object = $object;
            $this->obj_id = $object->getId();
            $this->obj_type = $object->getType();
            if ($in_repository) {
                $this->ref_id = $object->getRefId();
                if (!$object->withReferences()) {
                    $this->logger->logStack(ilLogLevel::WARNING);
                    $this->logger->warning('ObjectMetaDataGUI called without valid reference id.');
                }

                if (!$this->ref_id) {
                    $this->logger->logStack(ilLogLevel::WARNING);
                    $this->logger->warning('ObjectMetaDataGUI called without valid reference id.');
                }
            }

            $md_obj = new ilMD($this->obj_id, (int) $this->sub_id, $this->getLOMType());

            if (!$this->in_workspace && $in_repository) {
                // (parent) container taxonomies?
                $this->tax_md_gui = new ilTaxMDGUI(
                    $md_obj->getRBACId(),
                    $md_obj->getObjId(),
                    $md_obj->getObjType(),
                    $this->ref_id
                );
                $tax_ids = $this->tax_md_gui->getSelectableTaxonomies();
                if (!is_array($tax_ids) || count($tax_ids) == 0) {
                    $this->tax_md_gui = null;
                }
            }
        }

        $this->lng->loadLanguageModule("meta");
        $this->lng->loadLanguageModule("tax");
    }
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("edit");
        
        switch ($next_class) {
            case 'ilmdeditorgui':
                $this->setSubTabs("lom");
                $md_gui = new ilMDEditorGUI($this->obj_id, (int) $this->sub_id, $this->getLOMType());
                // custom observers?
                if (is_array($this->md_observers)) {
                    foreach ($this->md_observers as $observer) {
                        $md_gui->addObserver($observer["class"], $observer["method"], $observer["section"]);
                    }
                }
                // "default" repository object observer
                elseif (!$this->sub_id && $this->object) {
                    $md_gui->addObserver($this->object, 'MDUpdateListener', 'General');
                }
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'iladvancedmdsettingsgui':
                if ($this->in_repository) { // currently needs ref id
                    $this->setSubTabs("advmddef");
                    // TODO: needs ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION or
                    // ilAdvancedMDSettingsGUI::CONTEXT_OBJECT as first parameter
                    // fill in the weaker context for the moment
                    $advmdgui = new ilAdvancedMDSettingsGUI(
                        ilAdvancedMDSettingsGUI::CONTEXT_OBJECT,
                        $this->ref_id,
                        $this->obj_type,
                        $this->sub_type
                    );
                    $this->ctrl->forwardCommand($advmdgui);
                }
                break;

            case 'iltaxmdgui':
                $this->setSubTabs("tax_assignment");
                $this->ctrl->forwardCommand($this->tax_md_gui);
                break;

            case 'ilobjtaxonomygui':
                $this->setSubTabs("tax_definition");
                $this->ctrl->forwardCommand($this->tax_obj_gui);
                break;

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initEditForm();
                $this->ctrl->forwardCommand($form);
                break;

            default:
                $this->setSubTabs("advmd");
                $this->$cmd();
                break;
        }
    }

    public function setTaxonomySettings(Closure $form_manipulator, Closure $form_saver) : void
    {
        $this->taxonomy_settings_form_manipulator = $form_manipulator;
        $this->taxonomy_settings_form_saver = $form_saver;
    }

    /**
     * Set advanced record filter
     * @param ?int[] $filter
     */
    public function setRecordFilter(?array $filter = null) : void
    {
        $this->record_filter = $filter;
    }

    /**
     * Enable taxonomy definition
     */
    public function enableTaxonomyDefinition(bool $enable) : void
    {
        if ($enable) {
            $this->tax_obj_gui = new ilObjTaxonomyGUI();
            $this->tax_obj_gui->setAssignedObject($this->object->getId());
        } else {
            $this->tax_obj_gui = null;
        }
    }

    public function getTaxonomyObjGUI() : ?ilObjTaxonomyGUI
    {
        return $this->tax_obj_gui;
    }

    public function addMDObserver(object $class, string $method, string $section) : void
    {
        $this->md_observers[] = [
            "class" => $class,
            "method" => $method,
            "section" => $section
        ];
    }
    
    protected function getLOMType() : string
    {
        if ($this->sub_type != "-" && $this->sub_id) {
            return $this->sub_type;
        }
        return $this->obj_type;
    }

    /**
     * Set object, that defines the adv md records being used. Default is $this->object, but the
     * context may set another object (e.g. media pool for media objects)
     */
    public function setAdvMdRecordObject(int $adv_id, string $adv_type, string $adv_subtype = "-") : void
    {
        $this->adv_id = $adv_id;
        $this->adv_type = $adv_type;
        $this->adv_subtype = $adv_subtype;
    }

    /**
     * Get adv md record type
     */
    public function getAdvMdRecordObject() : array
    {
        if ($this->adv_type == null) {
            if ($this->in_repository) {
                return [$this->ref_id, $this->obj_type, $this->sub_type];
            } else {
                return [$this->obj_id, $this->obj_type, $this->sub_type];
            }
        }
        return [$this->adv_id, $this->adv_type, $this->adv_subtype];
    }

    protected function isAdvMDAvailable() : bool
    {
        foreach (ilAdvancedMDRecord::_getAssignableObjectTypes() as $item) {
            list(, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();

            if ($item["obj_type"] == $adv_type) {
                if ((!$item["sub_type"] && $adv_subtype == "-") ||
                    ($item["sub_type"] == $adv_subtype) ||
                    (is_array($adv_subtype) && in_array($item["sub_type"], $adv_subtype))
                ) {
                    return true;
                }
            }
        }
        return false;
    }
    
    protected function isLOMAvailable() : bool
    {
        $type = $this->getLOMType();
        if ($type == $this->sub_type) {
            $type = $this->obj_type . ":" . $type;
        }

        return (
            ($this->obj_id || !$this->obj_type) &&
            in_array($type, [
                "crs",
                'grp',
                "file",
                "glo",
                "glo:gdf",
                "svy",
                "spl",
                "tst",
                "qpl",
                ":mob",
                "webr",
                "htlm",
                "lm",
                "lm:st",
                "lm:pg",
                "sahs",
                "sahs:sco",
                "sahs:page",
                'sess',
                "iass",
                'exc',
                'lti',
                'cmix',
                'mep:mpg'
            ])
        );
    }
    
    protected function hasAdvancedMDSettings() : bool
    {
        if ($this->sub_id) {
            return false;
        }
        
        return (bool) ilContainer::_lookupContainerSetting(
            $this->obj_id,
            ilObjectServiceSettingsGUI::CUSTOM_METADATA
        );
    }
    
    /**
     * check if active records exist in current path anf for object type
     */
    protected function hasActiveRecords() : bool
    {
        list($adv_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();

        return (bool) sizeof(ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $adv_type,
            $adv_id,
            $adv_subtype,
            $this->in_repository
        ));
    }
    
    protected function canEdit() : bool
    {
        if (is_array($this->sub_type)) {        // only settings
            return false;
        }

        if ($this->hasActiveRecords()) {
            if ($this->sub_type == "-" || $this->sub_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get tab link if available
     */
    public function getTab(string $base_class = null) : ?string
    {
        if (!$base_class) {
            $path = [];
        } else {
            $path = [$base_class];
        }
        $path[] = "ilobjectmetadatagui";
        
        $link = null;
        if ($this->isLOMAvailable()) {
            $path[] = "ilmdeditorgui";
            $link = $this->ctrl->getLinkTargetByClass($path, "listSection");
        } elseif ($this->isAdvMDAvailable()) {
            if ($this->canEdit()) {
                $link = $this->ctrl->getLinkTarget($this, "edit");
            } elseif ($this->hasAdvancedMDSettings()) {
                $path[] = "iladvancedmdsettingsgui";
                $link = $this->ctrl->getLinkTargetByClass($path, "showRecords");
            }
        }
        if ($link == null && is_object($this->tax_obj_gui)) {		// taxonomy definition available?
            $path[] = "ilobjtaxonomygui";
            $link = $this->ctrl->getLinkTargetByClass($path, "");
        }
        return $link;
    }

    public function setSubTabs(string $active) : void
    {
        if ($this->isLOMAvailable()) {
            $this->tabs->addSubTab(
                "lom",
                $this->lng->txt("meta_tab_lom"),
                $this->ctrl->getLinkTargetByClass("ilmdeditorgui", "listSection")
            );
        }
        if ($this->isAdvMDAvailable()) {
            if ($this->canEdit()) {
                $this->tabs->addSubTab(
                    "advmd",
                    $this->lng->txt("meta_tab_advmd"),
                    $this->ctrl->getLinkTarget($this, "edit")
                );
            }
            if ($this->hasAdvancedMDSettings()) {
                $this->tabs->addSubTab(
                    "advmddef",
                    $this->lng->txt("meta_tab_advmd_def"),
                    $this->ctrl->getLinkTargetByClass("iladvancedmdsettingsgui", "showRecords")
                );
                                
                $this->tabs->addSubTab(
                    "md_adv_file_list",
                    $this->lng->txt("md_adv_file_list"),
                    $this->ctrl->getLinkTargetByClass("iladvancedmdsettingsgui", "showFiles")
                );
            }
        }

        if ($this->tax_md_gui != null) {
            $this->tax_md_gui->addSubTab();
        }

        if ($this->tax_obj_gui != null) {
            $this->tabs->addSubTab(
                "tax_definition",
                $this->lng->txt("cntr_taxonomy_definitions"),
                $this->ctrl->getLinkTargetByClass("ilobjtaxonomygui", "")
            );
        }

        if ($this->taxonomy_settings_form_manipulator != null) {
            $this->tabs->addSubTab(
                "tax_settings",
                $this->lng->txt("tax_tax_settings"),
                $this->ctrl->getLinkTarget($this, "editTaxonomySettings")
            );
        }

        $this->tabs->activateSubTab($active);
    }
    
    protected function initEditForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt("meta_tab_advmd"));
        
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            $this->obj_type,
            $this->obj_id,
            $this->sub_type,
            (int) $this->sub_id,
            $this->in_repository
        );

        if ($this->adv_type != "") {
            $this->record_gui->setAdvMdRecordObject($this->adv_id, $this->adv_type, $this->adv_subtype);
        }

        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();
        
        $form->addCommandButton("update", $this->lng->txt("save"));
        
        return $form;
    }
    
    protected function edit(ilPropertyFormGUI $a_form = null) : void
    {
        if (!$a_form) {
            $a_form = $this->initEditForm();
        }

        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function update() : void
    {
        $form = $this->initEditForm();
        if ($form->checkInput() && $this->record_gui->importEditFormPostValues()) {
            $this->record_gui->writeEditForm();
            
            // Update ECS content
            if ($this->obj_type == "crs") {
                $ecs = new ilECSCourseSettings($this->object);
                $ecs->handleContentUpdate();
            }
            
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "edit");
        }
        
        $form->setValuesByPost();
        $this->edit($form);
    }

    protected function checkFilter(int $record_id) : bool
    {
        return !(is_array($this->record_filter) && !in_array($record_id, $this->record_filter));
    }
    
    public function getBlockHTML(array $commands = null, $callback = null) : string
    {
        $html = "";
        
        list($adv_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();
        $advanced_md_records = ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $adv_type,
            $adv_id,
            $adv_subtype,
            $this->in_repository
        );
        foreach ($advanced_md_records as $record) {
            if (!$this->checkFilter($record->getRecordId())) {
                continue;
            }
            $block = new ilObjectMetaDataBlockGUI($record, $callback);
            $block->setValues(new ilAdvancedMDValues(
                $record->getRecordId(),
                $this->obj_id,
                $this->sub_type,
                (int) $this->sub_id
            ));
            if ($commands) {
                foreach ($commands as $caption => $url) {
                    $block->addBlockCommand($url, $this->lng->txt($caption));
                }
            }
            $html .= $block->getHTML();
        }
        
        return $html;
    }

    public function getKeyValueList() : string
    {
        $html = "";
        $sep = "";

        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        list($adv_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();
        $advanced_md_records = ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $adv_type,
            $adv_id,
            $adv_subtype,
            $this->in_repository
        );
        foreach ($advanced_md_records as $record) {
            $vals = new ilAdvancedMDValues($record->getRecordId(), $this->obj_id, $this->sub_type, (int) $this->sub_id);

            // this correctly binds group and definitions
            $vals->read();

            $defs = $vals->getDefinitions();
            foreach ($vals->getADTGroup()->getElements() as $element_id => $element) {
                if ($element instanceof ilADTLocation) {
                    continue;
                }

                $html .= $sep . $defs[$element_id]->getTitle() . ": ";

                if ($element->isNull()) {
                    $value = "-";
                } else {
                    $value = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);

                    $value = $value->getHTML();
                }
                $html .= $value;
                $sep = ",&nbsp;&nbsp;&nbsp; ";
            }
        }

        ilDatePresentation::setUseRelativeDates($old_dt);
        return $html;
    }

    protected function editTaxonomySettings() : void
    {
        $this->tabs->activateSubTab("tax_settings");
        $form = $this->initTaxonomySettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function initTaxonomySettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("tax_tax_settings"));
        $this->taxonomy_settings_form_manipulator->bindTo($this);
        call_user_func_array($this->taxonomy_settings_form_manipulator, [$form]);
        $form->addCommandButton("saveTaxonomySettings", $this->lng->txt("save"));

        return $form;
    }

    protected function saveTaxonomySettings() : void
    {
        $form = $this->initTaxonomySettingsForm();
        if ($form->checkInput()) {
            $this->taxonomy_settings_form_saver->bindTo($this);
            call_user_func_array($this->taxonomy_settings_form_saver, [$form]);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editTaxonomySettings");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
