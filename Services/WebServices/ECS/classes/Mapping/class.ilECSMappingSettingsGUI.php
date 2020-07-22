<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';

/*
 * Class for ECS node and directory mapping settings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $ID$
 *
 * @ingroup ServicesWebServicesECS
 * @ilCtrl_isCalledBy ilECSMappingSettingsGUI: ilECSSettingsGUI
 */
class ilECSMappingSettingsGUI
{
    const TAB_DIRECTORY = 1;
    const TAB_COURSE = 2;
    
    /**
     * @var ilLogger
     */
    protected $log;

    private $container = null;
    private $server = null;
    private $mid = null;

    protected $lng = null;
    protected $ctrl = null;

    /**
     * Constructor
     * @param ilObjectGUI $settingsContainer
     */
    public function __construct($settingsContainer, $server_id, $mid)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->log = $GLOBALS['DIC']->logger()->wsrv();

        $this->container = $settingsContainer;
        $this->server = ilECSSetting::getInstanceByServerId($server_id);
        $this->mid = $mid;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('ecs');
        $this->ctrl = $ilCtrl;
    }

    /**
     * Get container object
     * @return ilObjectGUI
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     *
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Get mid
     * @return int Get mid
     */
    public function getMid()
    {
        return $this->mid;
    }
    
    /**
     * ilCtrl executeCommand
     */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $GLOBALS['DIC']['tpl']->setTitle($this->lng->txt('ecs_campus_connect_title'));

        $this->ctrl->saveParameter($this, 'server_id');
        $this->ctrl->saveParameter($this, 'mid');
        $this->ctrl->saveParameter($this, 'tid');

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->setTabs();
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "cStart";
                }
                $this->$cmd();
                break;
        }

        $GLOBALS['DIC']['tpl']->setTitle($this->getServer()->getTitle());
        $GLOBALS['DIC']['tpl']->setDescription('');

        return true;
    }

    /**
     * return to parent container
     */
    public function cancel()
    {
        $GLOBALS['DIC']['ilCtrl']->returnToParent($this);
    }
    
    
    
    
    

    /**
     * Goto default page
     * @return <type>
     */
    protected function cStart()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
        if (ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isCourseAllocationEnabled()) {
            return $this->cInitOverview();
        }
        return $this->cSettings();
    }

    /**
     * Goto default page
     * @return <type>
     */
    protected function dStart()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
        if (ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isDirectoryMappingEnabled()) {
            return $this->dTrees();
        }
        return $this->dSettings();
    }
    
    /**
     * Show overview page
     */
    protected function cInitOverview($form = null, $current_attribute = null)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $current_node = (array) (($_REQUEST['lnodes']) ? $_REQUEST['lnodes'] : ROOT_FOLDER_ID);
        $current_node = end($current_node);
        
        $this->ctrl->setParameter($this, 'lnodes', $current_node);
        
        $this->setSubTabs(self::TAB_COURSE);
        $ilTabs->activateTab('ecs_crs_allocation');
        $ilTabs->activateSubTab('cInitTree');
        
        $GLOBALS['DIC']['tpl']->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ecs_cmap_overview.html', 'Services/WebServices/ECS');
        
        $explorer = $this->cShowLocalExplorer();
        if (!$form instanceof ilPropertyFormGUI) {
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMappingRule.php';
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
            
            if ($current_attribute === null) {
                // check request
                $current_attribute = (string) $_REQUEST['ecs_ca'];
                if (!$current_attribute) {
                    $existing = ilECSCourseMappingRule::lookupLastExistingAttribute(
                        $this->getServer()->getServerId(),
                        $this->getMid(),
                        $current_node
                    );

                    $current_attribute =
                        $existing ?
                        $existing :
                        '';
                    /*
                        ilECSCourseAttributes::getInstance(
                                $this->getServer()->getServerId(),
                                $this->getMid())->getFirstAttributeName()
                        );
                    */
                }
            }
            $form = $this->cInitMappingForm($current_node, $current_attribute);
        }
        
        $GLOBALS['DIC']['tpl']->setVariable('TFORM_ACTION', $this->ctrl->getFormAction($this));
        $GLOBALS['DIC']['tpl']->setVariable('LOCAL_EXPLORER', $explorer->getOutput());
        $GLOBALS['DIC']['tpl']->setVariable('MAPPING_FORM', $form->getHTML());
    }
    
    /**
     * Add one attribute in form
     */
    protected function cAddAttribute()
    {
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
        $next_attribute = ilECSCourseAttributes::getInstance($this->getServer()->getServerId(), $this->getMid())->getNextAttributeName((string) $_REQUEST['ecs_ca']);
        $this->cInitOverview(null, $next_attribute);
    }
    
    /**
     * Delete last attribute in form
     */
    protected function cDeleteAttribute()
    {
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
        $prev_attribute = ilECSCourseAttributes::getInstance($this->getServer()->getServerId(), $this->getMid())->getPreviousAttributeName((string) $_REQUEST['ecs_ca']);
        $this->cInitOverview(null, $prev_attribute);
    }
    
    /**
     * Show local explorer
     */
    protected function cShowLocalExplorer()
    {
        global $DIC;

        $tree = $DIC['tree'];

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingLocalExplorer.php';
        $explorer = new ilECSNodeMappingLocalExplorer(
            $this->ctrl->getLinkTarget($this, 'cInitOverview'),
            $this->getServer()->getServerId(),
            $this->getMid()
        );
        $explorer->setPostVar('lnodes[]');

        $lnodes = (array) $_REQUEST['lnodes'];
        $checked_node = array_pop($lnodes);
        if ((int) $_REQUEST['lid']) {
            $checked_node = (int) $_REQUEST['lid'];
        }

        if ($checked_node) {
            $explorer->setCheckedItems(array($checked_node));
        } else {
            $explorer->setCheckedItems(array(ROOT_FOLDER_ID));
        }
        $explorer->setTargetGet('lref_id');
        $explorer->setSessionExpandVariable('lexpand');
        $explorer->setExpand((int) $_GET['lexpand']);
        $explorer->setExpandTarget($this->ctrl->getLinkTarget($this, 'cInitOverview'));
        $explorer->setOutput(0);
        return $explorer;
    }
    
    /**
     * Init the mapping form
     */
    protected function cInitMappingForm($current_node, $current_attribute)
    {
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
        $attributes_obj = ilECSCourseAttributes::getInstance($this->getServer()->getServerId(), $this->getMid());
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setTableWidth("100%");
        $this->ctrl->setParameter($this, 'ecs_ca', $current_attribute);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $this->ctrl->setParameter($this, 'ecs_ca', '');
        
        $form->setTitle($this->lng->txt('ecs_cmap_mapping_form_title') . ' ' . ilObject::_lookupTitle(ilObject::_lookupObjId($current_node)));
        
        // Iterate through all current attributes
        $attributes = $attributes_obj->getAttributeSequence($current_attribute);
        foreach ($attributes as $att_name) {
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMappingRule.php';
            $rule = ilECSCourseMappingRule::getInstanceByAttribute($this->getServer()->getServerId(), $this->getMid(), $current_node, $att_name);
            
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('ecs_cmap_att_' . $att_name));
            
            // Filter
            $form->addItem($section);
            
            $isfilter = new ilRadioGroupInputGUI($this->lng->txt('ecs_cmap_form_filter'), $att_name . '_is_filter');
            $isfilter->setValue($rule->isFilterEnabled() ? 1 : 0);
            
            $all_values = new ilRadioOption($this->lng->txt('ecs_cmap_form_all_values'), 0);
            $isfilter->addOption($all_values);
            
            $use_filter = new ilRadioOption($this->lng->txt('ecs_cmap_form_filter_by_values'), 1);
            $filter = new ilTextInputGUI('', $att_name . '_filter');
            $filter->setInfo($this->lng->txt('ecs_cmap_form_filter_info'));
            $filter->setSize(50);
            $filter->setMaxLength(512);
            $filter->setRequired(true);
            $filter->setValue($rule->getFilter());
            $use_filter->addSubItem($filter);
            
            $isfilter->addOption($use_filter);
            
            $form->addItem($isfilter);

            // Create subdirs
            $subdirs = new ilCheckboxInputGUI($this->lng->txt('ecs_cmap_form_create_subdirs'), $att_name . '_subdirs');
            $subdirs->setChecked($rule->isSubdirCreationEnabled());
            $subdirs->setValue(1);
            
            // Subdir types (disabled in spec)
            /*
            $subdir_type = new ilRadioGroupInputGUI($this->lng->txt('ecs_cmap_form_subdir_type'), $att_name.'_subdir_type');
            $subdir_type->setValue($rule->getSubDirectoryType());

            $value = new ilRadioOption($this->lng->txt('ecs_cmap_form_subdir_value'),  ilECSCourseMappingRule::SUBDIR_VALUE);
            $subdir_type->addOption($value);

            $name = new ilRadioOption($this->lng->txt('ecs_cmap_form_subdir_name'),  ilECSCourseMappingRule::SUBDIR_ATTRIBUTE_NAME);
            $subdir_type->addOption($name);

            $subdirs->addSubItem($subdir_type);
            */
            $form->addItem($subdirs);
            
            // Directory relations
            /*
            $upper_attributes = ilECSCourseAttributes::getInstance(
                    $this->getServer()->getServerId(),
                    $this->getMid())->getUpperAttributes($att_name);

            if($upper_attributes)
            {
                $dir_relation = new ilRadioGroupInputGUI($this->lng->txt('ecs_cmap_form_dir_relation'),$att_name.'_dir_relation');

                $current_dir = new ilRadioOption($this->lng->txt('ecs_cmap_form_current_dir'),'');
                $dir_relation->addOption($current_dir);
            }
            foreach($upper_attributes as $subdir_name)
            {
                $subdir = new ilRadioOption($this->lng->txt('ecs_cmap_att_'.$subdir_name),$subdir_name);
                $dir_relation->addOption($subdir);
            }
            if($upper_attributes)
            {
                $dir_relation->setValue((string) $rule->getDirectory());
                $form->addItem($dir_relation);
            }
            */
        }
        
        // add list of attributes
        $hidden_atts = new ilHiddenInputGUI('attributes');
        $hidden_atts->setValue(implode(',', $attributes));
        $form->addItem($hidden_atts);
                

        if ($current_attribute) {
            $form->addCommandButton('cSaveOverview', $this->lng->txt('save'));
        }
        
        if ($attributes_obj->getNextAttributeName($current_attribute)) {
            $form->addCommandButton('cAddAttribute', $this->lng->txt('ecs_cmap_add_attribute_btn'));
        }
        if ($attributes_obj->getPreviousAttributeName($current_attribute)) {
            $form->addCommandButton('cDeleteAttribute', $this->lng->txt('ecs_cmap_delete_attribute_btn'));
        }
        if (ilECSCourseMappingRule::hasRules(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $current_node
        )) {
            $form->addCommandButton('cDeleteRulesOfNode', $this->lng->txt('ecs_cmap_delete_rule'));
        }
        
        #$form->addCommandButton('cInitOverview', $this->lng->txt('cancel'));
        
        $form->setShowTopButtons(false);

        return $form;
    }
    
    /**
     * Save overview
     */
    protected function cSaveOverview()
    {
        $current_node = (int) $_REQUEST['lnodes'];
        $current_att = (string) $_REQUEST['ecs_ca'];
        $form = $this->cInitMappingForm($current_node, $current_att);
                
        if ($form->checkInput()) {
            // save ...
            $all_attributes = explode(',', $form->getInput('attributes'));
            foreach ((array) $all_attributes as $att_name) {
                $rule = ilECSCourseMappingRule::getInstanceByAttribute(
                    $this->getServer()->getServerId(),
                    $this->getMid(),
                    $current_node,
                    $att_name
                );
                $rule->setServerId($this->getServer()->getServerId());
                $rule->setMid($this->getMid());
                $rule->setRefId($current_node);
                $rule->setAttribute($att_name);
                $rule->enableFilter($form->getInput($att_name . '_is_filter'));
                $rule->setFilter($form->getInput($att_name . '_filter'));
                $rule->enableSubdirCreation($form->getInput($att_name . '_subdirs'));
                //$rule->setSubDirectoryType($form->getInput($att_name.'_subdir_type'));
                //$rule->setDirectory($form->getInput($att_name.'_dir_relation'));
                
                if ($rule->getRuleId()) {
                    $rule->update();
                } else {
                    $rule->save();
                }
            }
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->setParameter($this, 'lnodes', $current_node);
            $this->ctrl->redirect($this, 'cInitOverview');
        }
        
        $form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->cInitOverview($form, $current_att);
    }
    
    protected function cDeleteRulesOfNode()
    {
        $current_node = (int) $_REQUEST['lnodes'];
        
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMappingRule.php';
        $rules = ilECSCourseMappingRule::getRulesOfRefId(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $current_node
        );
        
        foreach ($rules as $rid) {
            $rule = new ilECSCourseMappingRule($rid);
            $rule->delete();
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'cInitOverview');
    }


    

    /**
     * Show course allocation
     * @global ilTabsGUI $ilTabs
     * @return bool
     */
    protected function cSettings(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $this->setSubTabs(self::TAB_COURSE);
        $ilTabs->activateTab('ecs_crs_allocation');
        $ilTabs->activateSubTab('cSettings');

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormCSettings();
        }

        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());

        return true;
    }

    /**
     * Init settings form
     */
    protected function initFormCSettings()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('settings'));

        // individual course allocation
        $check = new ilCheckboxInputGUI($this->lng->txt('ecs_cmap_enable'), 'enabled');
        $check->setChecked(ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isCourseAllocationEnabled());
        $form->addItem($check);
        
        
        // add default container
        $imp = new ilCustomInputGUI($this->lng->txt('ecs_cmap_def_cat'), 'default_cat');
        $imp->setRequired(true);

        $tpl = new ilTemplate('tpl.ecs_import_id_form.html', true, true, 'Services/WebServices/ECS');
        $tpl->setVariable('SIZE', 5);
        $tpl->setVariable('MAXLENGTH', 11);
        $tpl->setVariable('POST_VAR', 'default_cat');
        
        $default = ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->getDefaultCourseCategory();
        $tpl->setVariable('PROPERTY_VALUE', $default);

        if ($default) {
            include_once './Services/Tree/classes/class.ilPathGUI.php';
            $path = new ilPathGUI();
            $path->enableTextOnly(false);
            $path->enableHideLeaf(false);
            $tpl->setVariable('COMPLETE_PATH', $path->getPath(ROOT_FOLDER_ID, $default));
        }

        $imp->setHtml($tpl->get());
        $imp->setInfo($this->lng->txt('ecs_cmap_def_cat_info'));
        $form->addItem($imp);

        // all in one category
        $allinone = new ilCheckboxInputGUI($this->lng->txt('ecs_cmap_all_in_one'), 'allinone');
        $allinone->setChecked(ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isAllInOneCategoryEnabled());
        $allinone->setInfo($this->lng->txt('ecs_cmap_all_in_one_info'));
        
        $allinone_cat = new ilCustomInputGUI($this->lng->txt('ecs_cmap_all_in_one_cat'), 'allinone_cat');
        $allinone_cat->setRequired(true);

        $tpl = new ilTemplate('tpl.ecs_import_id_form.html', true, true, 'Services/WebServices/ECS');
        $tpl->setVariable('SIZE', 5);
        $tpl->setVariable('MAXLENGTH', 11);
        $tpl->setVariable('POST_VAR', 'allinone_cat');
        
        $cat = ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->getAllInOneCategory();
        $tpl->setVariable('PROPERTY_VALUE', $cat);
        if ($cat) {
            include_once './Services/Tree/classes/class.ilPathGUI.php';
            $path = new ilPathGUI();
            $path->enableTextOnly(false);
            $path->enableHideLeaf(false);
            $tpl->setVariable('COMPLETE_PATH', $path->getPath(ROOT_FOLDER_ID, $default));
        }
        
        $allinone_cat->setHtml($tpl->get());
        $allinone->addSubItem($allinone_cat);
        $form->addItem($allinone);
        
        // multiple attributes
        $multiple = new ilCheckboxInputGUI($this->lng->txt('ecs_cmap_multiple_atts'), 'multiple');
        $multiple->setChecked(ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isAttributeMappingEnabled());

        // attribute selection
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
        $attributes = new ilSelectInputGUI($this->lng->txt('ecs_cmap_attributes'), 'atts');
        $attributes->setMulti(true);
        $attributes->setValue(
            ilECSCourseAttributes::getInstance(
                $this->getServer()->getServerId(),
                $this->getMid()
            )->getAttributeValues()
        );
        $attributes->setRequired(true);
        $attributes->setOptions(ilECSMappingUtils::getCourseMappingFieldSelectOptions());
        $multiple->addSubItem($attributes);

        $form->addItem($multiple);
        
        // role mapping
        $rm = new ilFormSectionHeaderGUI();
        $rm->setTitle($this->lng->txt('ecs_role_mappings'));
        $form->addItem($rm);
        
        // auth type
        $auth_type = new ilSelectInputGUI($this->lng->txt('ecs_member_auth_type'), 'auth_mode');
        $auth_type->setOptions(ilECSMappingUtils::getAuthModeSelection());
        $auth_type->setRequired(true);
        $auth_type->setValue(ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->getAuthMode());
        $form->addItem($auth_type);
        
        $mapping_defs = ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->getRoleMappings();
        
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
        foreach (ilECSMappingUtils::getRoleMappingInfo() as $name => $info) {
            $role_map = new ilTextInputGUI($this->lng->txt($info['lang']), $name);
            $role_map->setValue($mapping_defs[$name]);
            $role_map->setSize(32);
            $role_map->setMaxLength(64);
            $role_map->setRequired($info['required']);
            $form->addItem($role_map);
        }
        
        $form->addCommandButton('cUpdateSettings', $this->lng->txt('save'));
        $form->addCommandButton('cSettings', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * Show directory allocation
     * @global ilTabsGUI $ilTabs
     */
    protected function dSettings(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
        $this->setSubTabs(self::TAB_DIRECTORY);
        $ilTabs->activateTab('ecs_dir_allocation');
        $ilTabs->activateSubTab('dSettings');

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormDSettings();
        }

        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());

        return true;
    }
    
    /**
     * Update course settings
     */
    protected function cUpdateSettings()
    {
        $form = $this->initFormCSettings();
        if ($form->checkInput()) {
            include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
            $settings = ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid());
            $settings->enableCourseAllocation($form->getInput('enabled'));
            $settings->setDefaultCourseCategory($form->getInput('default_cat'));
            $settings->enableAllInOne($form->getInput('allinone'));
            $settings->setAllInOneCategory($form->getInput('allinone_cat'));
            $settings->enableAttributeMapping($form->getInput('multiple'));
            $settings->setAuthMode($form->getInput('auth_mode'));
            
            
            $role_mappings = array();
            foreach (ilECSMappingUtils::getRoleMappingInfo() as $name => $info) {
                $role_mappings[$name] = $form->getInput($name);
            }
            $settings->setRoleMappings($role_mappings);
            $settings->update();
            
            // store attribute settings
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
            $attributes = new ilECSCourseAttributes($this->getServer()->getServerId(), $this->getMid());
            $attributes->delete();
            
            $form_atts = $form->getInput('atts');
            
            foreach ($form_atts as $name) {
                if (!$name) {
                    continue;
                }
                
                $att = new ilECSCourseAttribute();
                $att->setServerId($this->getServer()->getServerId());
                $att->setMid($this->getMid());
                $att->setName($name);
                $att->save();
            }
            
            //$att = new ilECSCourseAttribute();
            //$att->setName($a_name)
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'cSettings');
        }
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->cSettings($form);
    }
    
    /**
     * Show active attributes
     * @global ilTabsGUI $ilTabs
     */
    protected function cAttributes()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $this->setSubTabs(self::TAB_COURSE);
        $ilTabs->setTabActive('ecs_crs_allocation');
        $ilTabs->setSubTabActive('cAttributes');
        
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributesTableGUI.php';
        $table = new ilECSCourseAttributesTableGUI(
            $this,
            'attributes',
            $this->getServer()->getServerId(),
            $this->getMid()
        );
        $table->init();
        $table->parse(
            ilECSCourseAttributes::getInstance(
                $this->getServer()->getServerId(),
                $this->getMid()
            )->getAttributes()
        );
        
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
    }
    
    

    /**
     * Update node mapping settings
     */
    protected function dUpdateSettings()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $form = $this->initFormDSettings();
        if ($form->checkInput()) {
            include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
            $settings = ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid());
            $settings->enableDirectoryMapping((bool) $form->getInput('active'));
            $settings->enableEmptyContainerCreation(!$form->getInput('empty'));
            $settings->update();
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        } else {
            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
            $form->setValuesByPost();
        }
        $ilCtrl->redirect($this, 'dSettings');
    }

    /**
     *
     */
    protected function initFormDSettings()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('general_settings'));

        $active = new ilCheckboxInputGUI($this->lng->txt('ecs_node_mapping_activate'), 'active');
        $active->setChecked(ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isDirectoryMappingEnabled());
        $form->addItem($active);

        $create_empty = new ilCheckboxInputGUI($this->lng->txt('ecs_node_mapping_create_empty'), 'empty');
        $create_empty->setChecked(!ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isEmptyContainerCreationEnabled());
        $create_empty->setInfo($this->lng->txt('ecs_node_mapping_create_empty_info'));
        $form->addItem($create_empty);

        $form->addCommandButton('dUpdateSettings', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * Show directory trees
     */
    protected function dTrees()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        
        $this->setSubTabs(self::TAB_DIRECTORY);
        $GLOBALS['DIC']['ilTabs']->activateSubTab('dTrees');
        $GLOBALS['DIC']['ilTabs']->activateTab('ecs_dir_allocation');
        
        $ilToolbar->addButton(
            $this->lng->txt('ecs_sync_trees'),
            $this->ctrl->getLinkTarget($this, 'dSynchronizeTrees')
        );

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingTreeTableGUI.php';

        $dtreeTable = new ilECSNodeMappingTreeTableGUI(
            $this->getServer()->getServerId(),
            $this->getMid(),
            $this,
            'dtree'
        );


        $dtreeTable->parse();
        $GLOBALS['DIC']['tpl']->setContent($dtreeTable->getHTML());
        return true;
    }

    /**
     * Delete tree settings
     */
    protected function dConfirmDeleteTree()
    {
        $this->setSubTabs(self::TAB_DIRECTORY);
        $GLOBALS['DIC']['ilTabs']->activateSubTab('dTrees');
        $GLOBALS['DIC']['ilTabs']->activateTab('ecs_dir_allocation');

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('ecs_confirm_delete_tree'));

        $confirm->addItem(
            'tid',
            (int) $_REQUEST['tid'],
            ilECSCmsData::lookupTitle(
                $this->getServer()->getServerId(),
                $this->getMid(),
                (int) $_REQUEST['tid']
            )
        );
        $confirm->setConfirm($this->lng->txt('delete'), 'dDeleteTree');
        $confirm->setCancel($this->lng->txt('cancel'), 'dTrees');

        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
    }

    /**
     * Delete tree
     */
    protected function dDeleteTree()
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

        $GLOBALS['DIC']['ilLog']->write('Deleting tree');

        $tree = new ilECSCmsTree((int) $_REQUEST['tid']);
        $tree->deleteTree($tree->getNodeData(ilECSCmsTree::lookupRootId((int) $_REQUEST['tid'])));

        
        
        // also delete import information
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        ilECSImport::deleteRessources(
            $this->getServer()->getServerId(),
            $this->getMid(),
            ilECSCmsData::lookupCmsIdsOfTree(
                $this->getServer()->getServerId(),
                $this->getMid(),
                (int) $_REQUEST['tid']
            )
        );
                
        $data = new ilECSCmsData();
        $data->setServerId($this->getServer()->getServerId());
        $data->setMid($this->getMid());
        $data->setTreeId((int) $_REQUEST['tid']);
        $data->deleteTree();


        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
        ilECSNodeMappingAssignments::deleteMappings(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid']
        );

        ilUtil::sendSuccess($this->lng->txt('ecs_cms_tree_deleted'), true);
        $this->ctrl->redirect($this, 'dTrees');
    }

    /**
     * Edit directory tree assignments
     */
    protected function dEditTree(ilPropertyFormGUI $form = null)
    {
        $GLOBALS['DIC']['tpl']->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ecs_edit_tree.html', 'Services/WebServices/ECS');

        $this->ctrl->saveParameter($this, 'cid');

        $GLOBALS['DIC']['ilTabs']->clearTargets();
        $GLOBALS['DIC']['ilTabs']->setBack2Target(
            $this->lng->txt('ecs_back_settings'),
            $this->ctrl->getLinkTarget($this, 'cancel')
        );
        $GLOBALS['DIC']['ilTabs']->setBackTarget(
            $this->lng->txt('ecs_cms_dir_tree'),
            $this->ctrl->getLinkTarget($this, 'dTrees')
        );
        
        $GLOBALS['DIC']['tpl']->setVariable('LEGEND', $GLOBALS['DIC']['lng']->txt('ecs_status_legend'));
        $GLOBALS['DIC']['tpl']->setVariable('PENDING_UNMAPPED', $GLOBALS['DIC']['lng']->txt('ecs_status_pending_unmapped'));
        $GLOBALS['DIC']['tpl']->setVariable('PENDING_UNMAPPED_DISCON', $GLOBALS['DIC']['lng']->txt('ecs_status_pending_unmapped_discon'));
        $GLOBALS['DIC']['tpl']->setVariable('PENDING_UNMAPPED_NONDISCON', $GLOBALS['DIC']['lng']->txt('ecs_status_pending_unmapped_nondiscon'));
        $GLOBALS['DIC']['tpl']->setVariable('MAPPED', $GLOBALS['DIC']['lng']->txt('ecs_status_mapped'));
        $GLOBALS['DIC']['tpl']->setVariable('DELETED', $GLOBALS['DIC']['lng']->txt('ecs_status_deleted'));

        $form = $this->dInitFormTreeSettings($form);
        $GLOBALS['DIC']['tpl']->setVariable('GENERAL_FORM', $form->getHTML());
        $GLOBALS['DIC']['tpl']->setVariable('TFORM_ACTION', $this->ctrl->getFormAction($this, 'dEditTree'));

        $explorer = $this->dShowLocalExplorer();
        $this->dShowCmsExplorer($explorer);
    }

    /**
     * Init form settings
     */
    protected function dInitFormTreeSettings(ilPropertyFormGUI $form = null)
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

        if ($form instanceof ilPropertyFormGUI) {
            return $form;
        }

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
        $assignment = new ilECSNodeMappingAssignment(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid'],
            0
        );

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'dEditTree'));
        $form->setTitle($this->lng->txt('general_settings'));
        $form->addCommandButton('dUpdateTreeSettings', $this->lng->txt('save'));
        $form->addCommandButton('dTrees', $this->lng->txt('cancel'));
        $form->setTableWidth('30%');

        // CMS id (readonly)
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        $cmsid = new ilNumberInputGUI($this->lng->txt('ecs_cms_id'), 'cmsid');
        $cmsid->setValue(
            ilECSCmsData::lookupCmsId(ilECSCmsTree::lookupRootId((int) $_REQUEST['tid']))
        );
        $cmsid->setDisabled(true);
        $cmsid->setSize(7);
        $cmsid->setMaxLength(12);
        $form->addItem($cmsid);


        $mapping_status = ilECSMappingUtils::lookupMappingStatus(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid']
        );
        $mapping_advanced = ($mapping_status != ilECSMappingUtils::MAPPED_MANUAL ? true : false);

        // Status (readonly)
        $status = new ilNonEditableValueGUI($this->lng->txt('status'), '');
        $status->setValue(ilECSMappingUtils::mappingStatusToString($mapping_status));
        $form->addItem($status);

        // title update
        $title = new ilCheckboxInputGUI($this->lng->txt('ecs_title_updates'), 'title');
        $title->setValue(1);
        $title->setChecked($assignment->isTitleUpdateEnabled());
        #$title->setInfo($this->lng->txt('ecs_title_update_info'));
        $form->addItem($title);


        $position = new ilCheckboxInputGUI($this->lng->txt('ecs_position_updates'), 'position');
        $position->setDisabled(!$mapping_advanced);
        $position->setChecked($mapping_advanced && $assignment->isPositionUpdateEnabled());
        $position->setValue(1);
        #$position->setInfo($this->lng->txt('ecs_position_update_info'));
        $form->addItem($position);

        $tree = new ilCheckboxInputGUI($this->lng->txt('ecs_tree_updates'), 'tree');
        $tree->setDisabled(!$mapping_advanced);
        $tree->setChecked($mapping_advanced && $assignment->isTreeUpdateEnabled());
        $tree->setValue(1);
        #$tree->setInfo($this->lng->txt('ecs_tree_update_info'));
        $form->addItem($tree);

        return $form;
    }

    /**
     *
     * @return boolean Update global settings
     */
    protected function dUpdateTreeSettings()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        $assignment = new ilECSNodeMappingAssignment(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid'],
            0
        );
        $assignment->setRefId(0);
        $assignment->setObjId(0);

        $form = $this->dInitFormTreeSettings();
        if ($form->checkInput()) {
            $assignment->enableTitleUpdate($form->getInput('title'));
            $assignment->enableTreeUpdate($form->getInput('tree'));
            $assignment->enablePositionUpdate($form->getInput('position'));
            $assignment->update();

            ilUtil::sendSuccess($this->lng->txt('settings_saved', true));
            $this->ctrl->redirect($this, 'dEditTree');
        }

        $form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->dEditTree($form);
        return true;
    }
    
    /**
     * Synchronize Tree
     */
    protected function dSynchronizeTree()
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTreeSynchronizer.php';
        $sync = new ilECSCmsTreeSynchronizer(
            $this->getServer(),
            $this->mid,
            (int) $_REQUEST['tid']
        );
        $sync->sync();
        ilUtil::sendSuccess($this->lng->txt('ecs_cms_tree_synchronized'), true);
        $this->ctrl->redirect($this, 'dTrees');
    }
    
    protected function dSynchronizeTrees()
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSDirectoryTreeConnector.php';

        $this->log->dump('Start synchronizing cms directory trees');
        
        try {
            $connector = new ilECSDirectoryTreeConnector($this->getServer());
            $res = $connector->getDirectoryTrees();
            
            $this->log->dump($res, ilLogLevel::DEBUG);
            
            foreach ((array) $res->getLinkIds() as $cms_id) {
                include_once './Services/WebServices/ECS/classes/class.ilECSEventQueueReader.php';
                include_once './Services/WebServices/ECS/classes/class.ilECSEvent.php';
                $event = new ilECSEventQueueReader($this->getServer()->getServerId());
                $event->add(
                    ilECSEventQueueReader::TYPE_DIRECTORY_TREES,
                    $cms_id,
                    ilECSEvent::UPDATED
                );
            }
            $this->ctrl->redirect($this, 'dTrees');
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'dTrees');
        }
    }

    /**
     * Show local explorer
     */
    protected function dShowLocalExplorer()
    {
        global $DIC;

        $tree = $DIC['tree'];

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingLocalExplorer.php';
        $explorer = new ilECSNodeMappingLocalExplorer(
            $this->ctrl->getLinkTarget($this, 'dEditTree'),
            $this->getServer()->getServerId(),
            $this->getMid()
        );
        $explorer->setPostVar('lnodes[]');

        $lnodes = (array) $_REQUEST['lnodes'];
        $checked_node = array_pop($lnodes);
        if ((int) $_REQUEST['lid']) {
            $checked_node = (int) $_REQUEST['lid'];
        }

        if ($checked_node) {
            $explorer->setCheckedItems(array($checked_node));
        } else {
            $explorer->setCheckedItems(array(ROOT_FOLDER_ID));
        }
        $explorer->setTargetGet('lref_id');
        $explorer->setSessionExpandVariable('lexpand');
        $explorer->setExpand((int) $_GET['lexpand']);
        $explorer->setExpandTarget($this->ctrl->getLinkTarget($this, 'dEditTree'));
        $explorer->setOutput(0);
        $GLOBALS['DIC']['tpl']->setVariable('LOCAL_EXPLORER', $explorer->getOutput());

        return $explorer;
    }

    /**
     * Show cms explorer
     */
    protected function dShowCmsExplorer(ilExplorer $localExplorer)
    {
        global $DIC;

        $tree = $DIC['tree'];

        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingCmsExplorer.php';
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';

        $explorer = new ilECSNodeMappingCmsExplorer(
            $this->ctrl->getLinkTarget($this, 'dEditTree'),
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid']
        );
        $explorer->setRoot(ilECSCmsTree::lookupRootId((int) $_REQUEST['tid']));
        $explorer->setTree(
            new ilECSCmsTree(
                (int) $_REQUEST['tid']
            )
        );
        $explorer->setPostVar('rnodes[]');

        // Read checked items from mapping of checked items in local explorer
        $active_node = $tree->getRootId();
        foreach ($localExplorer->getCheckedItems() as $ref_id) {
            $explorer->setCheckedItems(
                ilECSNodeMappingAssignments::lookupMappedItemsForRefId(
                    $this->getServer()->getServerId(),
                    $this->getMid(),
                    (int) $_REQUEST['tid'],
                    $ref_id
                )
            );
            $active_node = $ref_id;
        }


        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        $cmsTree = new ilECSCmsTree((int) $_REQUEST['tid']);
        foreach (ilECSNodeMappingAssignments::lookupAssignmentsByRefId(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid'],
            $active_node
        ) as $cs_id) {
            foreach ($cmsTree->getPathId($cs_id) as $path_id) {
                #$explorer->setExpand($path_id);
            }
        }

        $explorer->setTargetGet('rref_id');
        $explorer->setSessionExpandVariable('rexpand');

        #if((int) $_REQUEST['rexpand'])
        {
            $explorer->setExpand((int) $_GET['rexpand']);
        }
        $explorer->setExpandTarget($this->ctrl->getLinkTarget($this, 'dEditTree'));
        $explorer->setOutput(0);
        $GLOBALS['DIC']['tpl']->setVariable('REMOTE_EXPLORER', $explorer->getOutput());
    }

    /**
     * Init tree
     * @return
     */
    protected function dInitEditTree()
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        ilECSCmsData::updateStatus(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid']
        );
        return $this->dEditTree();
    }


    /**
     * Do mapping
     */
    protected function dMap()
    {
        if (!$_POST['lnodes']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'dEditTree');
        }

        $ref_id = end($_POST['lnodes']);

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
        ilECSNodeMappingAssignments::deleteDisconnectableMappings(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid'],
            $ref_id
        );


        $nodes = (array) $_POST['rnodes'];
        $nodes = (array) array_reverse($nodes);

        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        foreach ($nodes as $cms_id) {
            $assignment = new ilECSNodeMappingAssignment(
                $this->getServer()->getServerId(),
                $this->getMid(),
                (int) $_REQUEST['tid'],
                (int) $cms_id
            );
            $assignment->setRefId($ref_id);
            $assignment->setObjId(ilObject::_lookupObjId($ref_id));
            $assignment->enablePositionUpdate(false);
            $assignment->enableTreeUpdate(false);
            $assignment->enableTitleUpdate(ilECSNodeMappingAssignments::lookupDefaultTitleUpdate(
                $this->getServer()->getServerId(),
                $this->getMid(),
                (int) $_REQUEST['tid']
            ));
            $assignment->update();

            // Delete subitems mappings for cms subtree
            $cmsTree = new ilECSCmsTree((int) $_REQUEST['tid']);
            $childs = $cmsTree->getSubTreeIds($cms_id);

            ilECSNodeMappingAssignments::deleteMappingsByCsId(
                $this->getServer()->getServerId(),
                $this->getMid(),
                (int) $_REQUEST['tid'],
                $childs
            );
        }

        ilECSCmsData::updateStatus(
            $this->getServer()->getServerId(),
            $this->getMid(),
            (int) $_REQUEST['tid']
        );

        // Save parameter cid
        $this->ctrl->setParameter($this, 'lid', (int) $ref_id);

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'dEditTree');
    }

    /**
     * Show directory trees
     */
    protected function dMappingOverview()
    {
        $this->setSubTabs(self::TAB_DIRECTORY);
        $GLOBALS['DIC']['ilTabs']->activateSubTab('dMappingOverview');
        $GLOBALS['DIC']['ilTabs']->activateTab('ecs_dir_allocation');
    }
    
    

    /**
     * Set tabs
     * @global ilTabsGUI $ilTabs
     */
    protected function setTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt('ecs_back_settings'),
            $this->ctrl->getParentReturn($this)
        );
        // Directories are only visible for import type campus managment.
        if (ilECSParticipantSettings::loookupCmsMid($this->getServer()->getServerId()) == $this->getMid()) {
            $ilTabs->addTab(
                'ecs_dir_allocation',
                $this->lng->txt('ecs_dir_alloc'),
                $this->ctrl->getLinkTarget($this, 'dSettings')
            );
        }
        
        $ilTabs->addTab(
            'ecs_crs_allocation',
            $this->lng->txt('ecs_crs_alloc'),
            $this->ctrl->getLinkTarget($this, 'cStart')
        );
    }

    /**
     * Set Sub tabs
     * @global ilTabsGUI $ilTabs
     * @param string $a_tab
     */
    protected function setSubTabs($a_tab)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        if ($a_tab == self::TAB_DIRECTORY) {
            $ilTabs->addSubTab(
                'dMappingOverview',
                $this->lng->txt('ecs_cc_mapping_overview'),
                $this->ctrl->getLinkTarget($this, 'dMappingOverview')
            );
            $ilTabs->addSubTab(
                'dTrees',
                $this->lng->txt('ecs_cms_dir_tree'),
                $this->ctrl->getLinkTarget($this, 'dTrees')
            );
            $ilTabs->addSubTab(
                'dSettings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'dSettings')
            );
        }
        if ($a_tab == self::TAB_COURSE) {
            // Check if attributes are available
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttributes.php';
            $atts = ilECSCourseAttributes::getInstance($this->getServer()->getServerId(), $this->getMid());

            include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
            if (ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isCourseAllocationEnabled()) {
                $ilTabs->addSubTab(
                    'cInitTree',
                    $this->lng->txt('ecs_cmap_overview'),
                    $this->ctrl->getLinkTarget($this, 'cInitOverview')
                );
            }

            $ilTabs->addSubTab(
                'cSettings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'cSettings')
            );
        }
    }
}
