<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $object; // [ilObject]
    protected $ref_id;
    protected $obj_id; // [int]
    protected $obj_type; // [string]
    protected $sub_type; // [string]
    protected $sub_id; // [int]
    protected $md_observers; // [array]
    
    /**
     * @var ilLogger
     */
    private $logger = null;

    protected $tax_md_gui = null;
    protected $tax_obj_gui = null;
    protected $taxonomy_settings_form_manipulator = null;
    protected $taxonomy_settings_form_saver = null;

    // $adv_ref_id - $adv_type - $adv_subtype:
    // Object, that defines the adv md records being used. Default is $this->object, but the
    // context may set another object (e.g. media pool for media objects)
    /**
     * @var int
     */
    protected $adv_ref_id = null;
    /**
     * @var string
     */
    protected $adv_type = null;
    /**
     * @var string
     */
    protected $adv_subtype = null;

    /**
     * Construct
     *
     * @param ilObject $a_object
     * @param string $a_sub_type
     * @return self
     */
    public function __construct(ilObject $a_object = null, $a_sub_type = null, $a_sub_id = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();

        
        $this->logger = $GLOBALS['DIC']->logger()->obj();

        $this->in_workspace = (bool) $_REQUEST["wsp_id"];

        $this->sub_type = $a_sub_type;
        $this->sub_id = $a_sub_id;



        if (!$this->sub_type) {
            $this->sub_type = "-";
        }

        if ($a_object) {
            $this->object = $a_object;
            $this->obj_id = $a_object->getId();
            $this->obj_type = $a_object->getType();
            $this->ref_id = $a_object->getRefId();
            
            if (!$a_object->withReferences()) {
                $this->logger->logStack(ilLogLevel::WARNING);
                $this->logger->warning('ObjectMetaDataGUI called without valid reference id.');
            }
            
            if (!$this->ref_id) {
                $this->logger->logStack(ilLogLevel::WARNING);
                $this->logger->warning('ObjectMetaDataGUI called without valid reference id.');
            }

            $this->md_obj = new ilMD((int) $this->obj_id, (int) $this->sub_id, $this->getLOMType());

            if (!$this->in_workspace) {
                // (parent) container taxonomies?
                include_once "Services/Taxonomy/classes/class.ilTaxMDGUI.php";
                $this->tax_md_gui = new ilTaxMDGUI($this->md_obj->getRBACId(), $this->md_obj->getObjId(), $this->md_obj->getObjType(), $this->ref_id);
                $tax_ids = $this->tax_md_gui->getSelectableTaxonomies();
                if (!is_array($tax_ids) || count($tax_ids) == 0) {
                    $this->tax_md_gui = null;
                }
            }
        }

        $this->lng->loadLanguageModule("meta");
        $this->lng->loadLanguageModule("tax");
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("edit");
        
        switch ($next_class) {
            case 'ilmdeditorgui':
                $this->setSubTabs("lom");
                include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
                $md_gui = new ilMDEditorGUI((int) $this->obj_id, (int) $this->sub_id, $this->getLOMType());
                // custom observers?
                if (is_array($this->md_observers)) {
                    foreach ($this->md_observers as $observer) {
                        $md_gui->addObserver($observer["class"], $observer["method"], $observer["section"]);
                    }
                }
                // "default" repository object observer
                elseif (!$this->sub_id &&
                    $this->object) {
                    $md_gui->addObserver($this->object, 'MDUpdateListener', 'General');
                }
                $ilCtrl->forwardCommand($md_gui);
                break;

            case 'iladvancedmdsettingsgui':
                $this->setSubTabs("advmddef");
                include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDSettingsGUI.php';
                $advmdgui = new ilAdvancedMDSettingsGUI($this->ref_id, $this->obj_type, $this->sub_type);
                $ilCtrl->forwardCommand($advmdgui);
                break;

            case 'iltaxmdgui':
                $this->setSubTabs("tax_assignment");
                $ilCtrl->forwardCommand($this->tax_md_gui);
                break;

            case 'ilobjtaxonomygui':
                $this->setSubTabs("tax_definition");
                include_once("./Services/Taxonomy/classes/class.ilObjTaxonomyGUI.php");
                $ilCtrl->forwardCommand($this->tax_obj_gui);
                break;

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initEditForm();
                $ilCtrl->forwardCommand($form);
                break;

            default:
                $this->setSubTabs("advmd");
                $this->$cmd();
                break;
        }
    }

    /**
     * Set taxonomy settings
     *
     * @param string $a_link link traget
     */
    public function setTaxonomySettings(closure $a_form_manipulator, closure $a_form_saver)
    {
        $this->taxonomy_settings_form_manipulator = $a_form_manipulator;
        $this->taxonomy_settings_form_saver = $a_form_saver;
    }

    /**
     * Get taxonomy settings
     *
     * @return string link traget
     */
    public function getTaxonomySettings()
    {
        return $this->taxonomy_settings;
    }

    /**
     * Enable taxonomy definition
     *
     * @param
     * @return
     */
    public function enableTaxonomyDefinition($a_enable)
    {
        if ($a_enable) {
            $this->tax_obj_gui = new ilObjTaxonomyGUI();
            $this->tax_obj_gui->setAssignedObject($this->object->getId());
        } else {
            $this->tax_obj_gui = null;
        }
    }

    /**
     * Get taxonomy obj gui
     *
     * @return ilObjTaxonomyGUI|null
     */
    public function getTaxonomyObjGUI()
    {
        return $this->tax_obj_gui;
    }


    public function addMDObserver($a_class, $a_method, $a_section)
    {
        $this->md_observers[] = array(
            "class" => $a_class,
            "method" => $a_method,
            "section" => $a_section
        );
    }
    
    protected function getLOMType()
    {
        if ($this->sub_type != "-" &&
            $this->sub_id) {
            return $this->sub_type;
        } else {
            return $this->obj_type;
        }
    }

    /**
     * Set object, that defines the adv md records being used. Default is $this->object, but the
     * context may set another object (e.g. media pool for media objects)
     *
     * @param string $a_val adv type
     */
    public function setAdvMdRecordObject($a_adv_ref_id, $a_adv_type, $a_adv_subtype = "-")
    {
        $this->adv_ref_id = $a_adv_ref_id;
        $this->adv_type = $a_adv_type;
        $this->adv_subtype = $a_adv_subtype;
    }

    /**
     * Get adv md record type
     *
     * @return array adv type
     */
    public function getAdvMdRecordObject()
    {
        if ($this->adv_type == null) {
            return [$this->ref_id, $this->obj_type, $this->sub_type];
        }
        return [$this->adv_ref_id, $this->adv_type, $this->adv_subtype];
    }

    
    protected function isAdvMDAvailable()
    {
        //		$this->setAdvMdRecordObject(70,"mep", "mob");
        include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';
        foreach (ilAdvancedMDRecord::_getAssignableObjectTypes(false) as $item) {
            list($adv_ref_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();

            //			echo ("<br>".$item["obj_type"]."-".$adv_type."-".$adv_subtype);
            if ($item["obj_type"] == $adv_type) {
                //				 ("<br>-- ".$adv_type."-".$adv_subtype);
                //				exit;
                return ((!$item["sub_type"] && $adv_subtype == "-") ||
                    ($item["sub_type"] == $adv_subtype));
            }
        }
        //		exit;
        return false;
    }
    
    protected function isLOMAvailable()
    {
        $type = $this->getLOMType();
        if ($type == $this->sub_type) {
            $type = $this->obj_type . ":" . $type;
        }
        
        return (($this->obj_id || !$this->obj_type) &&
            in_array($type, array(
                "crs",
                'grp',
                "file",
                "glo", "glo:gdf",
                "svy", "spl",
                "tst", "qpl",
                ":mob",
                "webr",
                "htlm",
                "lm", "lm:st", "lm:pg",
                "sahs", "sahs:sco", "sahs:page",
                'sess', "iass"
        )));
    }
    
    protected function hasAdvancedMDSettings()
    {
        if ($this->sub_id) {
            return false;
        }
        
        include_once 'Services/Container/classes/class.ilContainer.php';
        include_once 'Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
        
        return ilContainer::_lookupContainerSetting(
            $this->obj_id,
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            false
        );
    }
    
    /**
     * check if active records exist in current path anf for object type
     * @return type
     */
    protected function hasActiveRecords()
    {
        include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';

        list($adv_ref_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();

        return
        (bool) sizeof(ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $adv_type,
            $adv_ref_id,
            $adv_subtype
        ));
    }
    
    protected function canEdit()
    {
        //		if($this->hasActiveRecords() &&
        //			$this->obj_id)
        //		{
        if ($this->hasActiveRecords()) {
            if ($this->sub_type == "-" ||
                $this->sub_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get tab link if available
     *
     * @param null $a_base_class
     * @return null|string
     */
    public function getTab($a_base_class = null)
    {
        $ilCtrl = $this->ctrl;
        
        if (!$a_base_class) {
            $path = array();
        } else {
            $path = array($a_base_class);
        }
        $path[] = "ilobjectmetadatagui";
        
        $link = null;
        if ($this->isLOMAvailable()) {
            $path[] = "ilmdeditorgui";
            $link = $ilCtrl->getLinkTargetByClass($path, "listSection");
        } elseif ($this->isAdvMDAvailable()) {
            if ($this->canEdit()) {
                $link = $ilCtrl->getLinkTarget($this, "edit");
            } elseif ($this->hasAdvancedMDSettings()) {
                $path[] = "iladvancedmdsettingsgui";
                $link = $ilCtrl->getLinkTargetByClass($path, "showRecords");
            }
        }
        if ($link == null && is_object($this->tax_obj_gui)) {		// taxonomy definition available?
            $path[] = "ilobjtaxonomygui";
            $link = $ilCtrl->getLinkTargetByClass($path, "");
        }
        return $link;
    }

    public function setSubTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
                
        if ($this->isLOMAvailable()) {
            $ilTabs->addSubTab(
                "lom",
                $lng->txt("meta_tab_lom"),
                $ilCtrl->getLinkTargetByClass("ilmdeditorgui", "listSection")
            );
        }
        if ($this->isAdvMDAvailable()) {
            if ($this->canEdit()) {
                $ilTabs->addSubTab(
                    "advmd",
                    $lng->txt("meta_tab_advmd"),
                    $ilCtrl->getLinkTarget($this, "edit")
                );
            }
            if ($this->hasAdvancedMDSettings()) {
                $ilTabs->addSubTab(
                    "advmddef",
                    $lng->txt("meta_tab_advmd_def"),
                    $ilCtrl->getLinkTargetByClass("iladvancedmdsettingsgui", "showRecords")
                );
                                
                $ilTabs->addSubTab(
                    "md_adv_file_list",
                    $lng->txt("md_adv_file_list"),
                    $ilCtrl->getLinkTargetByClass("iladvancedmdsettingsgui", "showFiles")
                );
            }
        }

        if ($this->tax_md_gui != null) {
            $this->tax_md_gui->addSubTab();
        }

        if ($this->tax_obj_gui != null) {
            $ilTabs->addSubTab(
                "tax_definition",
                $lng->txt("cntr_taxonomy_definitions"),
                $ilCtrl->getLinkTargetByClass("ilobjtaxonomygui", "")
            );
        }

        if ($this->taxonomy_settings_form_manipulator != null) {
            $ilTabs->addSubTab(
                "tax_settings",
                $lng->txt("tax_tax_settings"),
                $ilCtrl->getLinkTarget($this, "editTaxonomySettings")
            );
        }

        $ilTabs->activateSubTab($a_active);
    }
    
    
    //
    // (VALUES) EDITOR
    //
    
    protected function initEditForm()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "update"));
        $form->setTitle($lng->txt("meta_tab_advmd"));
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            $this->obj_type,
            $this->obj_id,
            $this->sub_type,
            $this->sub_id
        );

        if ($this->adv_type != "") {
            $this->record_gui->setAdvMdRecordObject($this->adv_ref_id, $this->adv_type, $this->adv_subtype);
        }

        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();
        
        $form->addCommandButton("update", $lng->txt("save"));
        
        return $form;
    }
    
    protected function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        
        if (!$a_form) {
            $a_form = $this->initEditForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function update()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $form = $this->initEditForm();
        if (
            $form->checkInput() &&
            $this->record_gui->importEditFormPostValues()) {
            $this->record_gui->writeEditForm();
            
            // Update ECS content
            if ($this->obj_type == "crs") {
                include_once "Modules/Course/classes/class.ilECSCourseSettings.php";
                $ecs = new ilECSCourseSettings($this->object);
                $ecs->handleContentUpdate();
            }
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "edit");
        }
        
        $form->setValuesByPost();
        $this->edit($form);
    }
    
    
    //
    // BLOCK
    //
    
    public function getBlockHTML(array $a_cmds = null, $a_callback = null)
    {
        $lng = $this->lng;
        
        $html = "";
        
        include_once "Services/Object/classes/class.ilObjectMetaDataBlockGUI.php";
        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php";
        list($adv_ref_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();
        foreach (ilAdvancedMDRecord::_getSelectedRecordsByObject($adv_type, $adv_ref_id, $adv_subtype) as $record) {
            $block = new ilObjectMetaDataBlockGUI($record, $a_callback);
            $block->setValues(new ilAdvancedMDValues($record->getRecordId(), $this->obj_id, $this->sub_type, $this->sub_id));
            if ($a_cmds) {
                foreach ($a_cmds as $caption => $url) {
                    $block->addBlockCommand($url, $lng->txt($caption), "_top");
                }
            }
            $html .= $block->getHTML();
        }
        
        return $html;
    }


    //
    // Key/value list
    //


    public function getKeyValueList()
    {
        $html = "";
        $sep = "";

        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php";
        list($adv_ref_id, $adv_type, $adv_subtype) = $this->getAdvMdRecordObject();
        foreach (ilAdvancedMDRecord::_getSelectedRecordsByObject($adv_type, $adv_ref_id, $adv_subtype) as $record) {
            $vals = new ilAdvancedMDValues($record->getRecordId(), $this->obj_id, $this->sub_type, $this->sub_id);


            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
            include_once('Services/ADT/classes/class.ilADTFactory.php');

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

    protected function editTaxonomySettings()
    {
        $this->tabs->activateSubTab("tax_settings");

        $form = $this->initTaxonomySettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init taxonomy settings form.
     */
    protected function initTaxonomySettingsForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this), "saveTaxonomySettings");
        $form->setTitle($this->lng->txt("tax_tax_settings"));
        $this->taxonomy_settings_form_manipulator->bindTo($this);
        call_user_func_array($this->taxonomy_settings_form_manipulator, [$form]);
        $form->addCommandButton("saveTaxonomySettings", $this->lng->txt("save"));

        return $form;
    }

    /**
     * Save taxonomy settings form
     */
    protected function saveTaxonomySettings()
    {
        $form = $this->initTaxonomySettingsForm();
        if ($form->checkInput()) {
            $this->taxonomy_settings_form_saver->bindTo($this);
            call_user_func_array($this->taxonomy_settings_form_saver, [$form]);
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editTaxonomySettings");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
        }
    }
}
