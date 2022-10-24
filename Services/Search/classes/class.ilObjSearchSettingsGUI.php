<?php

declare(strict_types=1);
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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ilCtrl_Calls ilObjSearchSettingsGUI: ilPermissionGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjSearchSettingsGUI extends ilObjectGUI
{
    private GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->type = "seas";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('search');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    public function cancelObject(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "settings");
    }

    public function settingsObject(?ilPropertyFormGUI $form = null): bool
    {
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        $this->tabs_gui->setTabActive('settings');
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormSettings();
        }
        $this->tpl->setContent($form->getHTML());
        return true;
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }


    protected function getTabs(): void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings","", "view"),
                "",
                ""
            );
        }

        if ($this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'lucene_advanced_settings',
                $this->ctrl->getLinkTarget($this, 'advancedLuceneSettings')
            );
        }

        if ($this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'lucene_settings_tab',
                $this->ctrl->getLinkTarget($this, 'luceneSettings')
            );
        }


        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    protected function initFormSettings(): ilPropertyFormGUI
    {
        $settings = new ilSearchSettings();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'updateSettings'));

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('updateSettings', $this->lng->txt('save'));
        }
        $form->setTitle($this->lng->txt('seas_settings'));

        // Max hits
        $hits = new ilSelectInputGUI($this->lng->txt('seas_max_hits'), 'max_hits');
        $hits->setValue($settings->getMaxHits());
        $hits->setRequired(true);
        $values = [];
        for ($value = 5; $value <= 50; $value += 5) {
            $values[$value] = $value;
        }
        $hits->setOptions($values);
        $hits->setInfo($this->lng->txt('seas_max_hits_info'));
        $form->addItem($hits);


        // Search type
        $type = new ilRadioGroupInputGUI($this->lng->txt('search_type'), 'search_type');

        if ($settings->enabledLucene()) {
            $type->setValue((string) ilSearchSettings::LUCENE_SEARCH);
        } else {
            $type->setValue((string) ilSearchSettings::LIKE_SEARCH);
        }
        $type->setRequired(true);
        $form->addItem($type);

        // Default operator
        $operator = new ilRadioGroupInputGUI($this->lng->txt('lucene_default_operator'), 'operator');
        $operator->setRequired(true);
        $operator->setInfo($this->lng->txt('lucene_default_operator_info'));
        $operator->setValue((string) $settings->getDefaultOperator());

        $and = new ilRadioOption($this->lng->txt('lucene_and'), (string) ilSearchSettings::OPERATOR_AND);
        $operator->addOption($and);

        $or = new ilRadioOption($this->lng->txt('lucene_or'), (string) ilSearchSettings::OPERATOR_OR);
        $operator->addOption($or);
        $form->addItem($operator);

        // user search
        $us = new ilCheckboxInputGUI($this->lng->txt('search_user_search_form'), 'user_search_enabled');
        $us->setInfo($this->lng->txt('search_user_search_info_form'));
        $us->setValue('1');
        $us->setChecked($settings->isLuceneUserSearchEnabled());
        $form->addItem($us);


        // Item filter
        $if = new ilCheckboxInputGUI($this->lng->txt('search_item_filter_form'), 'if');
        $if->setValue('1');
        $if->setChecked($settings->isLuceneItemFilterEnabled());
        $if->setInfo($this->lng->txt('search_item_filter_form_info'));
        $form->addItem($if);

        $filter = $settings->getLuceneItemFilter();
        foreach (ilSearchSettings::getLuceneItemFilterDefinitions() as $obj => $def) {
            $ch = new ilCheckboxInputGUI($this->lng->txt($def['trans']), 'filter[' . $obj . ']');
            if (isset($filter[$obj]) and $filter[$obj]) {
                $ch->setChecked(true);
            }
            $ch->setValue('1');
            $if->addSubItem($ch);
        }

        $cdate = new ilCheckboxInputGUI($this->lng->txt('search_cdate_filter'), 'cdate');
        $cdate->setInfo($this->lng->txt('search_cdate_filter_info'));
        $cdate->setChecked($settings->isDateFilterEnabled());
        $cdate->setValue('1');
        $form->addItem($cdate);

        // hide advanced search
        $cb = new ilCheckboxInputGUI($this->lng->txt("search_hide_adv_search"), "hide_adv_search");
        $cb->setChecked($settings->getHideAdvancedSearch());
        $form->addItem($cb);



        $direct = new ilRadioOption($this->lng->txt('search_direct'), (string) ilSearchSettings::LIKE_SEARCH, $this->lng->txt('search_like_info'));
        $type->addOption($direct);
        $lucene = new ilRadioOption($this->lng->txt('search_lucene'), (string) ilSearchSettings::LUCENE_SEARCH, $this->lng->txt('java_server_info'));
        $type->addOption($lucene);


        // number of auto complete entries
        $options = array(
            5 => 5,
            10 => 10,
            20 => 20,
            30 => 30
            );
        $si = new ilSelectInputGUI($this->lng->txt("search_auto_complete_length"), "auto_complete_length");
        $si->setOptions($options);
        $val = ($settings->getAutoCompleteLength() > 0)
            ? $settings->getAutoCompleteLength()
            : 10;
        $si->setValue($val);
        $form->addItem($si);

        $inactive_user = new ilCheckboxInputGUI($this->lng->txt('search_show_inactive_user'), 'inactive_user');
        $inactive_user->setInfo($this->lng->txt('search_show_inactive_user_info'));
        $inactive_user->setChecked($settings->isInactiveUserVisible());
        $form->addItem($inactive_user);

        $limited_user = new ilCheckboxInputGUI($this->lng->txt('search_show_limited_user'), 'limited_user');
        $limited_user->setInfo($this->lng->txt('search_show_limited_user_info'));
        $limited_user->setChecked($settings->isLimitedUserVisible());
        $form->addItem($limited_user);
        return $form;
    }

    /**
     * @throws Exception
     */
    protected function updateSettingsObject(): bool
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'settings');
        }
        $form = $this->initFormSettings();
        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->settingsObject($form);
            return false;
        }

        $settings = ilSearchSettings::getInstance();
        $settings->setMaxHits((int) $form->getInput('max_hits'));

        switch ((int) $form->getInput('search_type')) {
            case ilSearchSettings::LIKE_SEARCH:
                $settings->enableLucene(false);
                break;
            case ilSearchSettings::LUCENE_SEARCH:
                $settings->enableLucene(true);
                break;
        }
        $settings->setDefaultOperator((int) $form->getInput('operator'));
        $settings->enableLuceneItemFilter((bool) $form->getInput('if'));
        $settings->setLuceneItemFilter((array) $form->getInput('filter'));

        $settings->setHideAdvancedSearch((bool) $form->getInput('hide_adv_search'));
        $settings->setAutoCompleteLength((int) $form->getInput('auto_complete_length'));
        $settings->showInactiveUser((bool) $form->getInput('inactive_user'));
        $settings->showLimitedUser((bool) $form->getInput('limited_user'));
        $settings->enableDateFilter((bool) $form->getInput('cdate'));
        $settings->enableLuceneUserSearch((bool) $form->getInput('user_search_enabled'));
        $settings->update();

        // refresh lucene server
        try {
            $this->refreshLuceneSettings();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
            ilSession::clear('search_last_class');
            $this->ctrl->redirect($this, 'settings');
            return true;
        } catch (Exception $exception) {
            $this->tpl->setOnScreenMessage('failure', $exception->getMessage());
            $this->settingsObject();
            return false;
        }
    }


    protected function luceneSettingsObject(ilPropertyFormGUI $form = null): void
    {
        $this->initSubTabs('lucene');
        $this->tabs_gui->setTabActive('lucene_settings_tab');

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormLuceneSettings();
        }
        $this->tpl->setContent($form->getHTML());
    }


    protected function initFormLuceneSettings(): ilPropertyFormGUI
    {
        $search_settings = ilSearchSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'cancel'));

        $form->setTitle($this->lng->txt('lucene_settings_title'));


        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('saveLuceneSettings', $this->lng->txt('save'));
        }

        // Item filter
        $if = new ilCheckboxInputGUI($this->lng->txt('search_mime_filter_form'), 'mime_enabled');
        $if->setValue('1');
        $if->setChecked($search_settings->isLuceneMimeFilterEnabled());
        $if->setInfo($this->lng->txt('search_mime_filter_form_info'));
        $form->addItem($if);

        $mimes = $search_settings->getLuceneMimeFilter();
        foreach (ilSearchSettings::getLuceneMimeFilterDefinitions() as $mime => $def) {
            $ch = new ilCheckboxInputGUI($this->lng->txt($def['trans']), 'mime[' . $mime . ']');
            if (isset($mimes[$mime]) and $mimes[$mime]) {
                $ch->setChecked(true);
            }
            $ch->setValue('1');
            $if->addSubItem($ch);
        }

        $prefix = new ilCheckboxInputGUI($this->lng->txt('lucene_prefix_wildcard'), 'prefix');
        $prefix->setValue('1');
        $prefix->setInfo($this->lng->txt('lucene_prefix_wildcard_info'));
        $prefix->setChecked($search_settings->isPrefixWildcardQueryEnabled());
        $form->addItem($prefix);


        $numFrag = new ilNumberInputGUI($this->lng->txt('lucene_num_fragments'), 'fragmentCount');
        $numFrag->setRequired(true);
        $numFrag->setSize(2);
        $numFrag->setMaxLength(2);
        $numFrag->setMinValue(1);
        $numFrag->setMaxValue(10);
        $numFrag->setInfo($this->lng->txt('lucene_num_frag_info'));
        $numFrag->setValue((string) $search_settings->getFragmentCount());
        $form->addItem($numFrag);

        $sizeFrag = new ilNumberInputGUI($this->lng->txt('lucene_size_fragments'), 'fragmentSize');
        $sizeFrag->setRequired(true);
        $sizeFrag->setSize(2);
        $sizeFrag->setMaxLength(4);
        $sizeFrag->setMinValue(10);
        $sizeFrag->setMaxValue(1000);
        $sizeFrag->setInfo($this->lng->txt('lucene_size_frag_info'));
        $sizeFrag->setValue((string) $search_settings->getFragmentSize());
        $form->addItem($sizeFrag);

        $maxSub = new ilNumberInputGUI($this->lng->txt('lucene_max_sub'), 'maxSubitems');
        $maxSub->setRequired(true);
        $maxSub->setSize(2);
        $maxSub->setMaxLength(2);
        $maxSub->setMinValue(1);
        $maxSub->setMaxValue(10);
        $maxSub->setInfo($this->lng->txt('lucene_max_sub_info'));
        $maxSub->setValue((string) $search_settings->getMaxSubitems());
        $form->addItem($maxSub);

        $relevance = new ilCheckboxInputGUI($this->lng->txt('lucene_relevance'), 'relevance');
        $relevance->setOptionTitle($this->lng->txt('lucene_show_relevance'));
        $relevance->setInfo($this->lng->txt('lucene_show_relevance_info'));
        $relevance->setValue('1');
        $relevance->setChecked($search_settings->isRelevanceVisible());
        $form->addItem($relevance);

        // begin-patch mime_filter
        $subrel = new ilCheckboxInputGUI('', 'subrelevance');
        $subrel->setOptionTitle($this->lng->txt('lucene_show_sub_relevance'));
        $subrel->setValue('1');
        $subrel->setChecked($search_settings->isSubRelevanceVisible());
        $relevance->addSubItem($subrel);
        // end-patch mime_filter

        $last_index = new ilDateTimeInputGUI($this->lng->txt('lucene_last_index_time'), 'last_index');
        $last_index->setRequired(true);
        $last_index->setShowTime(true);
        $last_index->setDate($search_settings->getLastIndexTime());
        $last_index->setInfo($this->lng->txt('lucene_last_index_time_info'));
        $form->addItem($last_index);

        return $form;
    }

    /**
     * @throws Exception
     */
    protected function saveLuceneSettingsObject(): bool
    {
        $form = $this->initFormLuceneSettings();
        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->luceneSettingsObject($form);
            return false;
        }

        $settings = ilSearchSettings::getInstance();
        $settings->setFragmentCount((int) $form->getInput('fragmentCount'));
        $settings->setFragmentSize((int) $form->getInput('fragmentCount'));
        $settings->setMaxSubitems((int) $form->getInput('maxSubitems'));
        $settings->showRelevance((bool) $form->getInput('relevance'));
        $settings->enableLuceneOfflineFilter((bool) $form->getInput('offline_filter'));
        $settings->enableLuceneMimeFilter((bool) $form->getInput('mime_enabled'));
        $settings->setLuceneMimeFilter((array) $form->getInput('mime'));
        $settings->showSubRelevance((bool) $form->getInput('subrelevance'));
        $settings->enablePrefixWildcardQuery((bool) $form->getInput('prefix'));
        $settings->setLastIndexTime($form->getItemByPostVar('last_index')->getDate());
        $settings->update();

        // refresh lucene server
        try {
            $this->refreshLuceneSettings();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'luceneSettings');
            return true;
        } catch (Exception $exception) {
            $this->tpl->setOnScreenMessage('failure', $exception->getMessage());
            $this->luceneSettingsObject($form);
            return false;
        }
    }

    /**
     * @throws Exception
     */
    protected function refreshLuceneSettings(): bool
    {
        if (!ilSearchSettings::getInstance()->enabledLucene()) {
            return true;
        }

        try {
            ilRpcClientFactory::factory('RPCAdministration')->refreshSettings(CLIENT_ID . '_' . $this->settings->get('inst_id', '0'));
            return true;
        } catch (Exception $exception) {
            ilLoggerFactory::getLogger('src')->error('Refresh of lucene server settings failed with message: ' . $exception->getMessage());
            throw $exception;
        }
    }

    protected function advancedLuceneSettingsObject(): void
    {
        $this->initSubTabs('lucene');
        $this->tabs_gui->setTabActive('lucene_advanced_settings');


        $table = new ilLuceneAdvancedSearchActivationTableGUI($this, 'advancedLuceneSettings');
        $table->setTitle($this->lng->txt('lucene_advanced_settings_table'));
        $table->parse(ilLuceneAdvancedSearchSettings::getInstance());

        $this->tpl->setContent($table->getHTML());
    }

    protected function saveAdvancedLuceneSettingsObject(): void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'settings');
        }

        $enabled_md_ids = new SplFixedArray(0);
        if ($this->http->wrapper()->post()->has('fid')) {
            $enabled_md_ids = $this->http->wrapper()->post()->retrieve(
                'fid',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        $settings = ilLuceneAdvancedSearchSettings::getInstance();
        foreach (ilLuceneAdvancedSearchFields::getFields() as $field => $translation) {
            $settings->setActive($field, in_array($field, (array) $enabled_md_ids));
        }
        $settings->save();
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'advancedLuceneSettings');
    }

    protected function initSubTabs(string $a_section): void
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
