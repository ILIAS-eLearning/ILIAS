<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPObjSettings.php';

/**
 * Class ilLPListOfSettingsGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilLPListOfSettingsGUI:
 *
 * @ingroup ServicesTracking
 *
 */
class ilLPListOfSettingsGUI extends ilLearningProgressBaseGUI
{
    protected $obj_settings;
    protected $obj_lp;
    
    public function __construct($a_mode, $a_ref_id)
    {
        parent::__construct($a_mode, $a_ref_id);
        
        $this->obj_settings = new ilLPObjSettings($this->getObjId());
        
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $this->obj_lp = ilObjectLP::getInstance($this->getObjId());
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();

        }
        return true;
    }

    /**
     * Show settings tables
     */
    protected function show()
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setSubScreenId("trac_settings");
        
        $info = $this->obj_lp->getSettingsInfo();
        if ($info) {
            ilUtil::sendInfo($info);
        }

        $form = $this->initFormSettings();
        $this->tpl->setContent(
            $this->handleLPUsageInfo() .
            $form->getHTML() .
            $this->getTableByMode()
        );
    }


    /**
     * Init property form
     *
     * @return ilPropertyFormGUI $form
     */
    protected function initFormSettings()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('tracking_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        // Mode
        $mod = new ilRadioGroupInputGUI($this->lng->txt('trac_mode'), 'modus');
        $mod->setRequired(true);
        $mod->setValue($this->obj_lp->getCurrentMode());
        $form->addItem($mod);

        foreach ($this->obj_lp->getValidModes() as $mode_key) {
            $opt = new ilRadioOption(
                $this->obj_lp->getModeText($mode_key),
                $mode_key,
                $this->obj_lp->getModeInfoText($mode_key)
            );
            $opt->setValue($mode_key);
            $mod->addOption($opt);

            // :TODO: Subitem for visits ?!
            if ($mode_key == ilLPObjSettings::LP_MODE_VISITS) {
                $vis = new ilNumberInputGUI($this->lng->txt('trac_visits'), 'visits');
                $vis->setSize(3);
                $vis->setMaxLength(4);
                $vis->setInfo(sprintf(
                    $this->lng->txt('trac_visits_info'),
                    ilObjUserTracking::_getValidTimeSpan()
                ));
                $vis->setRequired(true);
                $vis->setValue($this->obj_settings->getVisits());
                $opt->addSubItem($vis);
            }
        }
        
        $form->addCommandButton('saveSettings', $this->lng->txt('save'));

        return $form;
    }

    /**
     * Save learning progress settings
     * @return void
     */
    protected function saveSettings()
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            // anything changed?
            
            // mode
            $new_mode = (int) $form->getInput('modus');
            $old_mode = $this->obj_lp->getCurrentMode();
            $mode_changed = ($old_mode != $new_mode);
            
            // visits
            $new_visits = null;
            $visits_changed = null;
            if ($new_mode == ilLPObjSettings::LP_MODE_VISITS) {
                $new_visits = (int) $form->getInput('visits');
                $old_visits = $this->obj_settings->getVisits();
                $visits_changed = ($old_visits != $new_visits);
            }
            
            if ($mode_changed) {
                // delete existing collection
                $collection = $this->obj_lp->getCollectionInstance();
                if ($collection) {
                    $collection->delete();
                }
            }
            
            $refresh_lp = ($mode_changed || $visits_changed);
            
            // has to be done before LP refresh!
            $this->obj_lp->resetCaches();
            
            $this->obj_settings->setMode($new_mode);
            $this->obj_settings->setVisits($new_visits);
            $this->obj_settings->update($refresh_lp);
            
            if ($mode_changed &&
                $this->obj_lp->getCollectionInstance() &&
                $new_mode != ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) { // #14819
                ilUtil::sendInfo($this->lng->txt('trac_edit_collection'), true);
            }
            ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'), true);
            $this->ctrl->redirect($this, 'show');
        }

        $form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_check_input'));

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lp_obj_settings.html', 'Services/Tracking');
        $this->tpl->setVariable('PROP_FORM', $form->getHTML());
        $this->tpl->setVariable('COLLECTION_TABLE', $this->getTableByMode());
    }

    /**
     * Get tables by mode
     */
    protected function getTableByMode()
    {
        $collection = $this->obj_lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            include_once "Services/Tracking/classes/repository_statistics/class.ilLPCollectionSettingsTableGUI.php";
            $table = new ilLPCollectionSettingsTableGUI($this, 'show', $this->getRefId(), $this->obj_lp->getCurrentMode());
            $table->parse($collection);
            return $table->getHTML();
        }
    }

    /**
     * Save material assignment
     * @return void
     */
    protected function assign()
    {
        if (!$_POST['item_ids']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'show');
        }
        if (count($_POST['item_ids'])) {
            $collection = $this->obj_lp->getCollectionInstance();
            if ($collection && $collection->hasSelectableItems()) {
                $collection->activateEntries($_POST['item_ids']);
            }
            
            // #15045 - has to be done before LP refresh!
            $this->obj_lp->resetCaches();
            
            // refresh learning progress
            include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }
        ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * save mterial assignment
     * @return void
     */
    protected function deassign()
    {
        if (!$_POST['item_ids']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'show');
            return false;
        }
        if (count($_POST['item_ids'])) {
            $collection = $this->obj_lp->getCollectionInstance();
            if ($collection && $collection->hasSelectableItems()) {
                $collection->deactivateEntries($_POST['item_ids']);
            }
            
            // #15045 - has to be done before LP refresh!
            $this->obj_lp->resetCaches();
            
            // refresh learning progress
            include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }
        ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Group materials
     */
    protected function groupMaterials()
    {
        if (!count((array) $_POST['item_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'show');
        }
        
        $collection = $this->obj_lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            // Assign new grouping id
            $collection->createNewGrouping((array) $_POST['item_ids']);

            // refresh learning progress
            include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }

        ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     *
     */
    protected function releaseMaterials()
    {
        if (!count((array) $_POST['item_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'show');
        }

        $collection = $this->obj_lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            $collection->releaseGrouping((array) $_POST['item_ids']);
            
            // refresh learning progress
            include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }
        
        ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Save obligatory state per grouped materials
     */
    protected function saveObligatoryMaterials()
    {
        if (!is_array((array) $_POST['grp'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'show');
        }

        try {
            $collection = $this->obj_lp->getCollectionInstance();
            if ($collection && $collection->hasSelectableItems()) {
                $collection->saveObligatoryMaterials((array) $_POST['grp']);

                // refresh learning progress
                include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
                ilLPStatusWrapper::_refreshStatus($this->getObjId());
            }

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'show');
        } catch (UnexpectedValueException $e) {
            ilUtil::sendFailure($this->lng->txt('trac_grouped_material_obligatory_err'), true);
            ilUtil::sendInfo($this->lng->txt('err_check_input'), true);
            $this->ctrl->redirect($this, 'show');
        }
    }
    
    protected function updateTLT()
    {
        include_once "Services/MetaData/classes/class.ilMD.php";
        foreach ($_POST['tlt'] as $item_id => $item) {
            $md_obj = new ilMD($this->getObjId(), $item_id, 'st');
            if (!is_object($md_section = $md_obj->getEducational())) {
                $md_section = $md_obj->addEducational();
                $md_section->save();
            }
            $md_section->setPhysicalTypicalLearningTime(
                (int) $item['mo'],
                (int) $item['d'],
                (int) $item['h'],
                (int) $item['m'],
                0
            );
            $md_section->update();
        }
        
        // refresh learning progress
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->getObjId());
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }
    
    
    //
    // USAGE INFO
    //
    
    /**
     * Gather LP data about parent objects
     *
     * @param int $a_ref_id
     * @param array $a_res
     * @return bool
     */
    protected function getLPPathInfo($a_ref_id, array &$a_res)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        $has_lp_parents = false;
                
        $path = $tree->getNodePath($a_ref_id);
        array_shift($path);	 // root
        foreach ($path as $node) {
            $supports_lp = ilObjectLP::isSupportedObjectType($node["type"]);
            
            if ($supports_lp || $has_lp_parents) {
                $a_res[$node["child"]]["node"] = array(
                    "type" => $node["type"]
                    ,"title" => $node["title"]
                    ,"obj_id" => $node["obj_id"]
                    ,"lp" => false
                    ,"active" => false
                );
            }
            
            if (
                $supports_lp &&
                $node["child"] != $a_ref_id) {
                $a_res[$node["child"]]["node"]["lp"] = true;
                $has_lp_parents = true;
                
                $parent_obj_id = $node['obj_id'];
                $parent_obj_lp = \ilObjectLP::getInstance($parent_obj_id);
                $parent_collection = $parent_obj_lp->getCollectionInstance();
                if (
                    $parent_collection &&
                    $parent_collection->hasSelectableItems() &&
                    $parent_collection->isAssignedEntry($a_ref_id)
                ) {
                    $a_res[$node['child']]['node']['active'] = true;
                            }
                        }
                    }
        return $has_lp_parents;
    }
    
    protected function handleLPUsageInfo()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        
        $ref_id = $_GET["ref_id"];
        if (!$ref_id) {
            $ref_id = $_REQUEST["ref_id"];
        }
        
        $coll = array();
        if ($ref_id &&
            $this->getLPPathInfo($ref_id, $coll)) {
            include_once "Services/Link/classes/class.ilLink.php";
            
            $tpl = new ilTemplate("tpl.lp_obj_settings_tree_info.html", true, true, "Services/Tracking");
            
            $margin = 0;
            $has_active = false;
            foreach ($coll as $parent_ref_id => $parts) {
                $node = $parts["node"];
                $params = array();
                if ($node["lp"]) {
                    if ($node["active"]) {
                        $tpl->touchBlock("parent_active_bl");
                        $has_active = true;
                    }
                    
                    $params["gotolp"] = 1;
                }
                
                if ($ilAccess->checkAccess("read", "", $parent_ref_id) &&
                    $parent_ref_id != $ref_id) { // #17170
                    $tpl->setCurrentBlock("parent_link_bl");
                    $tpl->setVariable("PARENT_LINK_TITLE", $node["title"]);
                    $tpl->setVariable("PARENT_URL", ilLink::_getLink($parent_ref_id, $node["type"], $params));
                    $tpl->parseCurrentBlock();
                } else {
                    $tpl->setCurrentBlock("parent_nolink_bl");
                    $tpl->setVariable("PARENT_NOLINK_TITLE", $node["title"]);
                    $tpl->parseCurrentBlock();
                }
                
                $tpl->setCurrentBlock("parent_usage_bl");
                $tpl->setVariable("PARENT_TYPE_URL", ilUtil::getTypeIconPath($node["type"], $node["obj_id"]));
                $tpl->setVariable("PARENT_TYPE_ALT", $lng->txt("obj_" . $node["type"]));
                
                $tpl->setVariable("PARENT_STYLE", $node["lp"]
                    ? ''
                    : ' class="ilLPParentInfoListLPUnsupported"');
                $tpl->setVariable("MARGIN", $margin);
                $tpl->parseCurrentBlock();
                
                $margin += 25;
            }
            
            if ($has_active) {
                $tpl->setVariable("LEGEND", sprintf(
                    $lng->txt("trac_lp_settings_info_parent_legend"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
                ));
            }
            
            include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
            $panel = ilPanelGUI::getInstance();
            $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
            $panel->setHeading($lng->txt("trac_lp_settings_info_parent_container"));
            $panel->setBody($tpl->get());
            
            return $panel->getHTML();
        }
    }
}
