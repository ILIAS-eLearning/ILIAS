<?php declare(strict_types=1);

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\GlobalHttpState;

/**
 * Meta Data Settings.
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjMDSettingsGUI: ilPermissionGUI, ilAdvancedMDSettingsGUI, ilMDCopyrightUsageGUI
 * @ingroup      ServicesMetaData
 */
class ilObjMDSettingsGUI extends ilObjectGUI
{
    protected ?ilPropertyFormGUI $form = null;
    protected ?ilMDSettings $md_settings = null;
    protected ?ilMDCopyrightSelectionEntry $entry = null;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct($a_data, $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->type = 'mds';
        $this->lng->loadLanguageModule("meta");
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    protected function initEntryIdFromQuery() : int
    {
        $entry_id = 0;
        if ($this->http->wrapper()->query()->has('entry_id')) {
            $entry_id = $this->http->wrapper()->query()->retrieve(
                'entry_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $entry_id;
    }

    protected function initEntryIdFromPost() : array
    {
        $entries = [];
        if ($this->http->wrapper()->post()->has('entry_id')) {
            return $this->http->wrapper()->post()->retrieve(
                'entry_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'iladvancedmdsettingsgui':
                $this->tabs_gui->setTabActive('md_advanced');
                $adv_md = new ilAdvancedMDSettingsGUI(
                    ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION,
                    $this->ref_id
                );
                $ret = $this->ctrl->forwardCommand($adv_md);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');

                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilmdcopyrightusagegui':
                // this command is used if copyrightUsageGUI calls getParentReturn (see ...UsageGUI->setTabs)
                $this->ctrl->setReturn($this, 'showCopyrightSettings');
                $copyright_id = $this->initEntryIdFromQuery();
                $gui = new ilMDCopyrightUsageGUI($copyright_id);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->initMDSettings();
                if (!$cmd || $cmd === 'view') {
                    $cmd = "showGeneralSettings";
                }

                $this->$cmd();
                break;
        }
    }

    protected function getType() : string
    {
        return $this->type;
    }

    protected function getParentObjType() : string
    {
        return 'meta';
    }

    protected function getAdministrationFormId() : int
    {
        return ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT;
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "md_general_settings",
                $this->ctrl->getLinkTarget($this, "showGeneralSettings"),
                array("showGeneralSettings", "view")
            );

            $this->tabs_gui->addTarget(
                "md_copyright",
                $this->ctrl->getLinkTarget($this, "showCopyrightSettings"),
                array("showCopyrightSettings")
            );

            $this->tabs_gui->addTarget(
                "md_advanced",
                $this->ctrl->getLinkTargetByClass('iladvancedmdsettingsgui', ""),
                '',
                'iladvancedmdsettingsgui'
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function showGeneralSettings(?ilPropertyFormGUI $form = null) : void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initGeneralSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initGeneralSettingsForm(string $a_mode = "edit") : ilPropertyFormGUI
    {
        $this->tabs_gui->setTabActive('md_general_settings');
        $form = new ilPropertyFormGUI();
        $ti = new ilTextInputGUI($this->lng->txt("md_delimiter"), "delimiter");
        $ti->setInfo($this->lng->txt("md_delimiter_info"));
        $ti->setMaxLength(1);
        $ti->setSize(1);
        $ti->setValue($this->md_settings->getDelimiter());
        $form->addItem($ti);

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton("saveGeneralSettings", $this->lng->txt("save"));
            $form->addCommandButton("showGeneralSettings", $this->lng->txt("cancel"));
        }
        $form->setTitle($this->lng->txt("md_general_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

    public function saveGeneralSettings() : void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        $form = $this->initGeneralSettingsForm();
        if ($form->checkInput()) {
            $delim = $form->getInput('delimiter');
            $delim = (
                trim($delim) === '' ?
                ',' :
                trim($delim)
            );
            $this->md_settings->setDelimiter($delim);
            $this->md_settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $form->setValuesByPost();
        $this->showGeneralSettings($form);
    }

    public function showCopyrightSettings(?ilPropertyFormGUI $form = null) : void
    {
        $this->tabs_gui->setTabActive('md_copyright');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.settings.html', 'Services/MetaData');

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setVariable('SETTINGS_TABLE', $form->getHTML());

        $has_write = $this->access->checkAccess('write', '', $this->object->getRefId());
        $table_gui = new ilMDCopyrightTableGUI($this, 'showCopyrightSettings', $has_write);
        $table_gui->setTitle($this->lng->txt("md_copyright_selection"));
        $table_gui->parseSelections();

        if ($has_write) {
            $table_gui->addCommandButton('addEntry', $this->lng->txt('add'));
            $table_gui->addMultiCommand("confirmDeleteEntries", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("entry_id");
        }
        $this->tpl->setVariable('COPYRIGHT_TABLE', $table_gui->getHTML());
    }

    public function saveCopyrightSettings() : void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "showCopyrightSettings");
        }
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->md_settings->activateCopyrightSelection((bool) $form->getInput('active'));
            $this->md_settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $this->showCopyrightSettings($form);
    }

    public function showCopyrightUsages() : void
    {
        $this->ctrl->setParameterByClass('ilmdcopyrightusagegui', 'entry_id', $this->initEntryIdFromQuery());
        $this->ctrl->redirectByClass('ilmdcopyrightusagegui', "showUsageTable");
    }

    public function editEntry(?ilPropertyFormGUI $form = null) : void
    {
        $this->ctrl->saveParameter($this, 'entry_id');
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initCopyrightEditForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function addEntry(?ilPropertyFormGUI $form = null) : void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initCopyrightEditForm('add');
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function saveEntry() : bool
    {
        $form = $this->initCopyrightEditForm('add');
        if ($form->checkInput()) {
            $this->entry = new ilMDCopyrightSelectionEntry(0);
            $this->entry->setTitle($form->getInput('title'));
            $this->entry->setDescription($form->getInput('description'));
            $this->entry->setCopyright($form->getInput('copyright'));
            $this->entry->setLanguage('en');
            $this->entry->setCopyrightAndOtherRestrictions(true);
            $this->entry->setCosts(false);
            $this->entry->setOutdated((bool) $form->getInput('outdated'));

            if (!$this->entry->validate()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('fill_out_all_required_fields'));
                $this->addEntry($form);
                return false;
            }
            $this->entry->add();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return true;
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $this->addEntry($form);
        return false;
    }

    public function confirmDeleteEntries() : void
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSettings();
            return;
        }

        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteEntries"));
        $c_gui->setHeaderText($this->lng->txt("md_delete_cp_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showCopyrightSettings");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteEntries");

        // add items to delete
        foreach ($entry_ids as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $c_gui->addItem('entry_id[]', $entry_id, $entry->getTitle());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function deleteEntries() : bool
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSettings();
            return true;
        }

        foreach ($entry_ids as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $entry->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_copyrights_deleted'));
        $this->showCopyrightSettings();
        return true;
    }

    public function updateEntry() : bool
    {
        $this->entry = new ilMDCopyrightSelectionEntry($this->initEntryIdFromQuery());
        $form = $this->initCopyrightEditForm();
        if ($form->checkInput()) {
            $this->entry->setTitle($form->getInput('title'));
            $this->entry->setDescription($form->getInput('description'));
            $this->entry->setCopyright($form->getInput('copyright'));
            $this->entry->setOutdated((bool) $form->getInput('outdated'));
            if (!$this->entry->validate()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('fill_out_all_required_fields'));
                $this->editEntry($form);
                return false;
            }
            $this->entry->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return true;
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->editEntry($form);
        return false;
    }

    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('md_copyright_settings'));

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('saveCopyrightSettings', $this->lng->txt('save'));
            $form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
        }

        $check = new ilCheckboxInputGUI($this->lng->txt('md_copyright_enabled'), 'active');
        $check->setChecked($this->md_settings->isCopyrightSelectionActive());
        $check->setValue('1');
        $check->setInfo($this->lng->txt('md_copyright_enable_info'));
        $form->addItem($check);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            $this->getAdministrationFormId(),
            $form,
            $this
        );
        return $form;
    }

    public function initCopyrightEditForm(string $a_mode = 'edit') : ilPropertyFormGUI
    {
        if (!is_object($this->entry)) {
            $this->entry = new ilMDCopyrightSelectionEntry($this->initEntryIdFromQuery());
        }
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $tit = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $tit->setValue($this->entry->getTitle());
        $tit->setRequired(true);
        $tit->setSize(40);
        $tit->setMaxLength(255);
        $form->addItem($tit);

        $des = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $des->setValue($this->entry->getDescription());
        $des->setRows(3);
        $form->addItem($des);

        $cop = new ilTextAreaInputGUI($this->lng->txt('md_copyright_value'), 'copyright');
        $cop->setValue($this->entry->getCopyright());
        $cop->setRows(5);
        $form->addItem($cop);
        $usage = new ilRadioGroupInputGUI($this->lng->txt('meta_copyright_usage'), 'outdated');
        $use = new ilRadioOption($this->lng->txt('meta_copyright_in_use'), '0');
        $out = new ilRadioOption($this->lng->txt('meta_copyright_outdated'), '1');
        $usage->addOption($use);
        $usage->addOption($out);
        $usage->setValue((string) $this->entry->getOutdated());
        $form->addItem($usage);

        switch ($a_mode) {
            case 'edit':
                $form->setTitle($this->lng->txt('md_copyright_edit'));
                $form->addCommandButton('updateEntry', $this->lng->txt('save'));
                $form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
                break;

            case 'add':
                $form->setTitle($this->lng->txt('md_copyright_add'));
                $form->addCommandButton('saveEntry', $this->lng->txt('save'));
                $form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
                break;
        }
        return $form;
    }

    protected function initMDSettings() : void
    {
        $this->md_settings = ilMDSettings::_getInstance();
    }

    public function saveCopyrightPosition() : bool
    {
        if (!$this->http->wrapper()->post()->has('order')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_select_one'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return false;
        }
        $positions = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf(
                $this->refinery->kindlyTo()->int()
            )
        );
        asort($positions);
        $position = 0;
        foreach ($positions as $entry_id => $position_ignored) {
            $copyright = new ilMDCopyrightSelectionEntry($entry_id);
            $copyright->setOrderPosition($position++);
            $copyright->update();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'showCopyrightSettings');
        return false;
    }
}
