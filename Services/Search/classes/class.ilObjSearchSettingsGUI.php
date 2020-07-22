<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjSearchSettingsGUI: ilPermissionGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjSearchSettingsGUI extends ilObjectGUI
{
    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        $this->type = "seas";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('search');
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }
    
    public function cancelObject()
    {
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "settings");
    }

    /**
    * Show settings
    * @access	public
    */
    public function settingsObject()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        
        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
        }
        $this->tabs_gui->setTabActive('settings');
        $this->initFormSettings();
        $this->tpl->setContent($this->form->getHTML());
        return true;
    }

    /**
    * Save settings
    * @access	public
    */
    public function saveSettingsObject()
    {
        include_once 'Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->object->initSettingsObject();
        $this->object->settings_obj->setMaxHits((int) $_POST['max_hits']);
        $this->object->settings_obj->enableIndex($_POST['search_index']);
        $this->object->settings_obj->enableLucene($_POST['search_lucene']);
        $this->object->settings_obj->setHideAdvancedSearch($_POST['hide_adv_search']);
        $this->object->settings_obj->setAutoCompleteLength($_POST['auto_complete_length']);
        $this->object->settings_obj->setDefaultOperator((int) $_POST['operator']);
        $this->object->settings_obj->enableLuceneItemFilter((int) $_POST['if']);
        $this->object->settings_obj->setLuceneItemFilter((array) $_POST['filter']);
        

        $rpc_settings = ilRPCServerSettings::getInstance();
        if ($this->object->settings_obj->enabledLucene() and !$rpc_settings->pingServer()) {
            ilUtil::sendInfo($this->lng->txt('search_no_connection_lucene'), true);
            $this->ctrl->redirect($this, 'settings');

            return false;
        }

        $this->object->settings_obj->update();

        ilUtil::sendInfo($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'settings');

        return true;
    }
    
    public function getAdminTabs()
    {
        $this->getTabs();
    }

    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings","", "view"),
                "",
                ""
            );
        }

        if ($rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'lucene_advanced_settings',
                $this->ctrl->getLinkTarget($this, 'advancedLuceneSettings')
            );
        }

        if ($rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'lucene_settings_tab',
                $this->ctrl->getLinkTarget($this, 'luceneSettings')
            );
        }


        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }
    
    /**
     * Init settings form
     * @return void
     */
    protected function initFormSettings()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $access = $GLOBALS['DIC']->access();
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        include_once './Services/Search/classes/class.ilSearchSettings.php';
        
        $settings = new ilSearchSettings();
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'updateSettings'));
        
        if ($access->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton('updateSettings', $this->lng->txt('save'));
        }
        $this->form->setTitle($this->lng->txt('seas_settings'));
        
        // Max hits
        $hits = new ilSelectInputGUI($this->lng->txt('seas_max_hits'), 'max_hits');
        $hits->setValue($settings->getMaxHits());
        $hits->setRequired(true);
        for ($value = 5; $value <= 50; $value += 5) {
            $values[$value] = $value;
        }
        $hits->setOptions($values);
        $hits->setInfo($this->lng->txt('seas_max_hits_info'));
        $this->form->addItem($hits);
        
        
        // Search type
        $type = new ilRadioGroupInputGUI($this->lng->txt('search_type'), 'search_type');
        
        if ($settings->enabledLucene()) {
            $type->setValue(ilSearchSettings::LUCENE_SEARCH);
        } elseif ($settings->enabledIndex()) {
            $type->setValue(ilSearchSettings::INDEX_SEARCH);
        } else {
            $type->setValue(ilSearchSettings::LIKE_SEARCH);
        }
        $type->setRequired(true);
        $this->form->addItem($type);

        // Default operator
        $operator = new ilRadioGroupInputGUI($this->lng->txt('lucene_default_operator'), 'operator');
        $operator->setRequired(true);
        $operator->setInfo($this->lng->txt('lucene_default_operator_info'));
        $operator->setValue($settings->getDefaultOperator());
        
        $and = new ilRadioOption($this->lng->txt('lucene_and'), ilSearchSettings::OPERATOR_AND);
        $operator->addOption($and);
        
        $or = new ilRadioOption($this->lng->txt('lucene_or'), ilSearchSettings::OPERATOR_OR);
        $operator->addOption($or);
        $this->form->addItem($operator);
        
        // user search
        $us = new ilCheckboxInputGUI($this->lng->txt('search_user_search_form'), 'user_search_enabled');
        $us->setInfo($this->lng->txt('search_user_search_info_form'));
        $us->setValue(1);
        $us->setChecked($settings->isLuceneUserSearchEnabled());
        $this->form->addItem($us);
        
        
        // Item filter
        $if = new ilCheckboxInputGUI($this->lng->txt('search_item_filter_form'), 'if');
        $if->setValue(1);
        $if->setChecked($settings->isLuceneItemFilterEnabled());
        $if->setInfo($this->lng->txt('search_item_filter_form_info'));
        $this->form->addItem($if);

        $filter = $settings->getLuceneItemFilter();
        foreach (ilSearchSettings::getLuceneItemFilterDefinitions() as $obj => $def) {
            $ch = new ilCheckboxInputGUI($this->lng->txt($def['trans']), 'filter[' . $obj . ']');
            if (isset($filter[$obj]) and $filter[$obj]) {
                $ch->setChecked(true);
            }
            $ch->setValue(1);
            $if->addSubItem($ch);
        }
        
        $cdate = new ilCheckboxInputGUI($this->lng->txt('search_cdate_filter'), 'cdate');
        $cdate->setInfo($this->lng->txt('search_cdate_filter_info'));
        $cdate->setChecked($settings->isDateFilterEnabled());
        $cdate->setValue(1);
        $this->form->addItem($cdate);
        
        // hide advanced search
        $cb = new ilCheckboxInputGUI($lng->txt("search_hide_adv_search"), "hide_adv_search");
        $cb->setChecked($settings->getHideAdvancedSearch());
        $this->form->addItem($cb);
        

        
        $direct = new ilRadioOption($this->lng->txt('search_direct'), ilSearchSettings::LIKE_SEARCH, $this->lng->txt('search_like_info'));
        $type->addOption($direct);
        
        if ($ilDB->supportsFulltext()) {
            $index = new ilRadioOption($this->lng->txt('search_index'), ilSearchSettings::INDEX_SEARCH, $this->lng->txt('search_full_info'));
            $type->addOption($index);
        }
        
        $lucene = new ilRadioOption($this->lng->txt('search_lucene'), ilSearchSettings::LUCENE_SEARCH, $this->lng->txt('java_server_info'));
        $type->addOption($lucene);

        
        // number of auto complete entries
        $options = array(
            5 => 5,
            10 => 10,
            20 => 20,
            30 => 30
            );
        $si = new ilSelectInputGUI($lng->txt("search_auto_complete_length"), "auto_complete_length");
        $si->setOptions($options);
        $val = ($settings->getAutoCompleteLength() > 0)
            ? $settings->getAutoCompleteLength()
            : 10;
        $si->setValue($val);
        $this->form->addItem($si);

        $inactive_user = new ilCheckboxInputGUI($this->lng->txt('search_show_inactive_user'), 'inactive_user');
        $inactive_user->setInfo($this->lng->txt('search_show_inactive_user_info'));
        $inactive_user->setChecked($settings->isInactiveUserVisible());
        $this->form->addItem($inactive_user);

        $limited_user = new ilCheckboxInputGUI($this->lng->txt('search_show_limited_user'), 'limited_user');
        $limited_user->setInfo($this->lng->txt('search_show_limited_user_info'));
        $limited_user->setChecked($settings->isLimitedUserVisible());
        $this->form->addItem($limited_user);
    }
    
    
    /**
     * Update Settings
     * @return void
     */
    protected function updateSettingsObject()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        $this->initFormSettings();
        $this->form->checkInput();
        
        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $GLOBALS['DIC']->ctrl()->redirect($this, 'settings');
        }
        
        include_once './Services/Search/classes/class.ilSearchSettings.php';
        $settings = ilSearchSettings::getInstance();
        $settings->setMaxHits((int) $_POST['max_hits']);
        
        switch ((int) $_POST['search_type']) {
            case ilSearchSettings::LIKE_SEARCH:
                $settings->enableIndex(false);
                $settings->enableLucene(false);
                break;
            case ilSearchSettings::INDEX_SEARCH:
                $settings->enableIndex(true);
                $settings->enableLucene(false);
                break;
            case ilSearchSettings::LUCENE_SEARCH:
                $settings->enableIndex(false);
                $settings->enableLucene(true);
                break;
        }

        $settings->setDefaultOperator((int) $_POST['operator']);
        $settings->enableLuceneItemFilter((int) $_POST['if']);
        $settings->setLuceneItemFilter((array) $_POST['filter']);

        $settings->setHideAdvancedSearch($_POST['hide_adv_search']);
        $settings->setAutoCompleteLength($_POST['auto_complete_length']);
        $settings->showInactiveUser($_POST["inactive_user"]);
        $settings->showLimitedUser($_POST["limited_user"]);
        $settings->enableDateFilter($_POST['cdate']);
        $settings->enableLuceneUserSearch((int) $_POST['user_search_enabled']);
        $settings->update();
        
        
        // refresh lucene server
        try {
            $this->refreshLuceneSettings();
            ilUtil::sendInfo($this->lng->txt('settings_saved'));
            $this->settingsObject();
            return true;
        } catch (Exception $exception) {
            ilUtil::sendFailure($exception->getMessage());
            $this->settingsObject();
            return false;
        }
        
        unset($_SESSION['search_last_class']);
    }
    
    /**
     * Lucene settings
     * @param
     * @return
     */
    protected function luceneSettingsObject(ilPropertyFormGUI $form = null)
    {
        $this->initSubTabs('lucene');
        $this->tabs_gui->setTabActive('lucene_settings_tab');
        
        if (!$form instanceof ilPropertyFormGUI) {
            $this->initFormLuceneSettings();
        }
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Show lucene settings form
     * @param
     * @return
     */
    protected function initFormLuceneSettings()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        include_once './Services/Search/classes/class.ilSearchSettings.php';
        
        $this->settings = ilSearchSettings::getInstance();
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'cancel'));
        
        $this->form->setTitle($this->lng->txt('lucene_settings_title'));
        
        $access = $GLOBALS['DIC']->access();
        if ($access->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton('saveLuceneSettings', $this->lng->txt('save'));
        }
        
        
        
        // Item filter
        $if = new ilCheckboxInputGUI($this->lng->txt('search_mime_filter_form'), 'mime_enabled');
        $if->setValue(1);
        $if->setChecked($this->settings->isLuceneMimeFilterEnabled());
        $if->setInfo($this->lng->txt('search_mime_filter_form_info'));
        $this->form->addItem($if);
        
        $mimes = $this->settings->getLuceneMimeFilter();
        foreach (ilSearchSettings::getLuceneMimeFilterDefinitions() as $mime => $def) {
            $ch = new ilCheckboxInputGUI($this->lng->txt($def['trans']), 'mime[' . $mime . ']');
            if (isset($mimes[$mime]) and $mimes[$mime]) {
                $ch->setChecked(true);
            }
            $ch->setValue(1);
            $if->addSubItem($ch);
        }
        
        $prefix = new ilCheckboxInputGUI($this->lng->txt('lucene_prefix_wildcard'), 'prefix');
        $prefix->setValue(1);
        $prefix->setInfo($this->lng->txt('lucene_prefix_wildcard_info'));
        $prefix->setChecked($this->settings->isPrefixWildcardQueryEnabled());
        $this->form->addItem($prefix);
        

        $numFrag = new ilNumberInputGUI($this->lng->txt('lucene_num_fragments'), 'fragmentCount');
        $numFrag->setRequired(true);
        $numFrag->setSize(2);
        $numFrag->setMaxLength(2);
        $numFrag->setMinValue(1);
        $numFrag->setMaxValue(10);
        $numFrag->setInfo($this->lng->txt('lucene_num_frag_info'));
        $numFrag->setValue($this->settings->getFragmentCount());
        $this->form->addItem($numFrag);
        
        $sizeFrag = new ilNumberInputGUI($this->lng->txt('lucene_size_fragments'), 'fragmentSize');
        $sizeFrag->setRequired(true);
        $sizeFrag->setSize(2);
        $sizeFrag->setMaxLength(4);
        $sizeFrag->setMinValue(10);
        $sizeFrag->setMaxValue(1000);
        $sizeFrag->setInfo($this->lng->txt('lucene_size_frag_info'));
        $sizeFrag->setValue($this->settings->getFragmentSize());
        $this->form->addItem($sizeFrag);
        
        $maxSub = new ilNumberInputGUI($this->lng->txt('lucene_max_sub'), 'maxSubitems');
        $maxSub->setRequired(true);
        $maxSub->setSize(2);
        $maxSub->setMaxLength(2);
        $maxSub->setMinValue(1);
        $maxSub->setMaxValue(10);
        $maxSub->setInfo($this->lng->txt('lucene_max_sub_info'));
        $maxSub->setValue($this->settings->getMaxSubitems());
        $this->form->addItem($maxSub);
        
        $relevance = new ilCheckboxInputGUI($this->lng->txt('lucene_relevance'), 'relevance');
        $relevance->setOptionTitle($this->lng->txt('lucene_show_relevance'));
        $relevance->setInfo($this->lng->txt('lucene_show_relevance_info'));
        $relevance->setValue(1);
        $relevance->setChecked($this->settings->isRelevanceVisible());
        $this->form->addItem($relevance);
        
        // begin-patch mime_filter
        $subrel = new ilCheckboxInputGUI('', 'subrelevance');
        $subrel->setOptionTitle($this->lng->txt('lucene_show_sub_relevance'));
        $subrel->setValue(1);
        $subrel->setChecked($this->settings->isSubRelevanceVisible());
        $relevance->addSubItem($subrel);
        // end-patch mime_filter
        
        $last_index = new ilDateTimeInputGUI($this->lng->txt('lucene_last_index_time'), 'last_index');
        $last_index->setRequired(true);
        $last_index->setShowTime(true);
        $last_index->setDate($this->settings->getLastIndexTime());
        $last_index->setInfo($this->lng->txt('lucene_last_index_time_info'));
        $this->form->addItem($last_index);
    
        return true;
    }
    
    /**
     * Save Lucene settings
     * @return
     */
    protected function saveLuceneSettingsObject()
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilLog = $DIC['ilLog'];
        $ilSetting = $DIC['ilSetting'];
        
        $this->initFormLuceneSettings();
        
        $settings = ilSearchSettings::getInstance();
        $settings->setFragmentCount((int) $_POST['fragmentCount']);
        $settings->setFragmentSize((int) $_POST['fragmentSize']);
        $settings->setMaxSubitems((int) $_POST['maxSubitems']);
        $settings->showRelevance((int) $_POST['relevance']);
        $settings->enableLuceneOfflineFilter((int) $_POST['offline_filter']);
        $settings->enableLuceneMimeFilter((int) $_POST['mime_enabled']);
        $settings->setLuceneMimeFilter((array) $_POST['mime']);
        $settings->showSubRelevance((int) $_POST['subrelevance']);
        $settings->enablePrefixWildcardQuery((int) $_POST['prefix']);
        
        if ($this->form->checkInput()) {
            $settings->setLastIndexTime($this->form->getItemByPostVar('last_index')->getDate());
            $settings->update();
            
            // refresh lucene server
            try {
                $this->refreshLuceneSettings();
                ilUtil::sendInfo($this->lng->txt('settings_saved'));
                $this->luceneSettingsObject();
                return true;
            } catch (Exception $exception) {
                ilUtil::sendFailure($exception->getMessage());
                $this->luceneSettingsObject();
                return false;
            }
        }
        
        ilUtil::sendInfo($this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->luceneSettingsObject($this->form);
        return false;
    }
    
    /**
     * Refresh lucene server settings
     * @throws Exception
     */
    protected function refreshLuceneSettings()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        if (!ilSearchSettings::getInstance()->enabledLucene()) {
            return true;
        }
        
        try {
            include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
            ilRpcClientFactory::factory('RPCAdministration')->refreshSettings(CLIENT_ID . '_' . $ilSetting->get('inst_id', 0));
        } catch (Exception $exception) {
            ilLoggerFactory::getLogger('src')->error('Refresh of lucene server settings failed with message: ' . $exception->getMessage());
            throw $exception;
        }
    }
    
    /**
     * show advanced settings
     */
    protected function advancedLuceneSettingsObject()
    {
        $this->initSubTabs('lucene');
        $this->tabs_gui->setTabActive('lucene_advanced_settings');
        
        include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchActivationTableGUI.php';
        include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchSettings.php';
        
        $table = new ilLuceneAdvancedSearchActivationTableGUI($this, 'advancedLuceneSettings');
        $table->setTitle($this->lng->txt('lucene_advanced_settings_table'));
        $table->parse(ilLuceneAdvancedSearchSettings::getInstance());
        
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Save advanced settings
     */
    protected function saveAdvancedLuceneSettingsObject()
    {
        $access = $GLOBALS['DIC']->access();
        
        if (!$access->checkAccess('write', '', $this->object->getRefId())) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $GLOBALS['DIC']->ctrl()->redirect($this, 'settings');
        }
        
        
        $settings = ilLuceneAdvancedSearchSettings::getInstance();
        foreach (ilLuceneAdvancedSearchFields::getFields() as $field => $translation) {
            $settings->setActive($field, in_array($field, (array) $_POST['fid']) ? true : false);
        }
        $settings->save();
        ilUtil::sendInfo($this->lng->txt('settings_saved'));
        $this->advancedLuceneSettingsObject();
    }
    
    /**
     *
     */
    protected function initSubTabs($a_section)
    {
        switch ($a_section) {
            case 'lucene':
                $this->tabs_gui->addSubTabTarget(
                    'lucene_general_settings',
                    $this->ctrl->getLinkTarget($this, 'luceneSettings')
                );

                break;
        }
    }
} // END class.ilObjSearchSettingsGUI
