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



/**
* Meta Data Settings.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjMDSettingsGUI: ilPermissionGUI, ilAdvancedMDSettingsGUI, ilMDCopyrightUsageGUI
*
* @ingroup ServicesMetaData
*/
class ilObjMDSettingsGUI extends ilObjectGUI
{

    protected ?ilPropertyFormGUI $form = null;
    protected ?ilMDSettings $md_settings = null;
    protected ?ilMDCopyrightSelectionEntry $entry = null;


    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->type = 'mds';
        $this->lng->loadLanguageModule("meta");
    }


    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('no_permission'), $this->ilErr->WARNING);
        }

        switch ($next_class) {
            case 'iladvancedmdsettingsgui':
                $this->tabs_gui->setTabActive('md_advanced');
                $adv_md = new ilAdvancedMDSettingsGUI(
                    ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION,
                    (int) $this->ref_id
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
                $copyright_id = $_GET['entry_id'];
                $gui = new ilMDCopyrightUsageGUI((int) $copyright_id);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->initMDSettings();
                if (!$cmd || $cmd == 'view') {
                    $cmd = "showGeneralSettings";
                }

                $this->$cmd();
                break;
        }
        return true;
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



    public function getAdminTabs()
    {

        if ($this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
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

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }


    public function showGeneralSettings() : void
    {
        
        $this->initGeneralSettingsForm();
        $this->tpl->setContent($this->form->getHTML());
    }
    

    public function initGeneralSettingsForm(string $a_mode = "edit") : void
    {

        $this->tabs_gui->setTabActive('md_general_settings');
        
        
        $this->form = new ilPropertyFormGUI();
    
        $ti = new ilTextInputGUI($this->lng->txt("md_delimiter"), "delimiter");
        $ti->setInfo($this->lng->txt("md_delimiter_info"));
        $ti->setMaxLength(1);
        $ti->setSize(1);
        $ti->setValue($this->md_settings->getDelimiter());
        $this->form->addItem($ti);
                    
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton("saveGeneralSettings", $this->lng->txt("save"));
            $this->form->addCommandButton("showGeneralSettings", $this->lng->txt("cancel"));
        }
                    
        $this->form->setTitle($this->lng->txt("md_general_settings"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }
    

    public function saveGeneralSettings() : void
    {
        
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        
        $delim = (trim($_POST['delimiter']) == "")
            ? ","
            : trim($_POST['delimiter']);
        $this->md_settings->setDelimiter($delim);
        $this->md_settings->save();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);

        $this->ctrl->redirect($this, "showGeneralSettings");
    }
    

    public function showCopyrightSettings() : void
    {
        
        $this->tabs_gui->setTabActive('md_copyright');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.settings.html', 'Services/MetaData');
        
        $this->initSettingsForm();
        $this->tpl->setVariable('SETTINGS_TABLE', $this->form->getHTML());
        
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
        
        $this->md_settings->activateCopyrightSelection((bool) $_POST['active']);
        $this->md_settings->save();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->showCopyrightSettings();
    }

    public function showCopyrightUsages()
    {
        $this->ctrl->setParameterByClass('ilmdcopyrightusagegui', 'entry_id', $_GET['entry_id']);
        $this->ctrl->redirectByClass('ilmdcopyrightusagegui', "showUsageTable");
    }
    

    public function editEntry() : void
    {
        $this->ctrl->saveParameter($this, 'entry_id');
        $this->initCopyrightEditForm();
        $this->tpl->setContent($this->form->getHTML());
    }
    

    public function addEntry() : void
    {
        $this->initCopyrightEditForm('add');
        $this->tpl->setContent($this->form->getHTML());
    }
    
    

    public function saveEntry() : bool
    {

        
        $this->entry = new ilMDCopyrightSelectionEntry(0);
    
        $this->entry->setTitle(ilUtil::stripSlashes($_POST['title']));
        $this->entry->setDescription(ilUtil::stripSlashes($_POST['description']));
        $this->entry->setCopyright($this->stripSlashes($_POST['copyright']));
        $this->entry->setLanguage('en');
        $this->entry->setCopyrightAndOtherRestrictions(true);
        $this->entry->setCosts(false);
        $this->entry->setOutdated((bool) $_POST['outdated']);
        
        if (!$this->entry->validate()) {
            ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields'));
            $this->addEntry();
            return false;
        }
        $this->entry->add();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->showCopyrightSettings();
        return true;
    }
    

    public function confirmDeleteEntries() : void
    {
        if (!is_array($_POST['entry_id']) or !count($_POST['entry_id'])) {
            ilUtil::sendInfo($this->lng->txt('select_one'));
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
        foreach ($_POST["entry_id"] as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $c_gui->addItem('entry_id[]', $entry_id, $entry->getTitle());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }
    

    public function deleteEntries() : bool
    {
        if (!is_array($_POST['entry_id']) or !count($_POST['entry_id'])) {
            ilUtil::sendInfo($this->lng->txt('select_one'));
            $this->showCopyrightSettings();
            return true;
        }

        
        foreach ($_POST["entry_id"] as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $entry->delete();
        }
        ilUtil::sendSuccess($this->lng->txt('md_copyrights_deleted'));
        $this->showCopyrightSettings();
        return true;
    }


    public function updateEntry() : bool
    {

        
        $this->entry = new ilMDCopyrightSelectionEntry((int) $_REQUEST['entry_id']);
    
        $this->entry->setTitle(ilUtil::stripSlashes($_POST['title']));
        $this->entry->setDescription(ilUtil::stripSlashes($_POST['description']));
        $this->entry->setCopyright($this->stripSlashes($_POST['copyright']));
        $this->entry->setOutdated((bool) $_POST['outdated']);
        
        if (!$this->entry->validate()) {
            ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields'));
            $this->editEntry();
            return false;
        }
        $this->entry->update();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->showCopyrightSettings();
        return true;
    }
    
    

    protected function initSettingsForm() : void
    {
        
        if (is_object($this->form)) {
            return;
        }
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt('md_copyright_settings'));


        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton('saveCopyrightSettings', $this->lng->txt('save'));
            $this->form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
        }
        
        $check = new ilCheckboxInputGUI($this->lng->txt('md_copyright_enabled'), 'active');
        $check->setChecked($this->md_settings->isCopyrightSelectionActive());
        $check->setValue('1');
        $check->setInfo($this->lng->txt('md_copyright_enable_info'));
        $this->form->addItem($check);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            $this->getAdministrationFormId(),
            $this->form,
            $this
        );
    }
    

    public function initCopyrightEditForm(string $a_mode = 'edit') : void
    {
        if (is_object($this->form)) {
            return;
        }
        if (!is_object($this->entry)) {
            
            $this->entry = new ilMDCopyrightSelectionEntry((int) $_REQUEST['entry_id']);
        }
        
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        
        $tit = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $tit->setValue($this->entry->getTitle());
        $tit->setRequired(true);
        $tit->setSize(40);
        $tit->setMaxLength(255);
        $this->form->addItem($tit);
        
        $des = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $des->setValue($this->entry->getDescription());
        $des->setRows(3);
        $this->form->addItem($des);
        
        $cop = new ilTextAreaInputGUI($this->lng->txt('md_copyright_value'), 'copyright');
        $cop->setValue($this->entry->getCopyright());
        $cop->setRows(5);
        $this->form->addItem($cop);

        $usage = new ilRadioGroupInputGUI($this->lng->txt('meta_copyright_usage'), 'outdated');
        $use = new ilRadioOption($this->lng->txt('meta_copyright_in_use'), '0');
        $out = new ilRadioOption($this->lng->txt('meta_copyright_outdated'), '1');
        $usage->addOption($use);
        $usage->addOption($out);
        $usage->setValue((string) $this->entry->getOutdated());
        $this->form->addItem($usage);
        
        switch ($a_mode) {
            case 'edit':
                $this->form->setTitle($this->lng->txt('md_copyright_edit'));
                $this->form->addCommandButton('updateEntry', $this->lng->txt('save'));
                $this->form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
                break;
            
            case 'add':
                $this->form->setTitle($this->lng->txt('md_copyright_add'));
                $this->form->addCommandButton('saveEntry', $this->lng->txt('save'));
                $this->form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
                break;
        }
    }
    

    protected function initMDSettings() : void
    {
        
        $this->md_settings = ilMDSettings::_getInstance();
    }
    

    protected function stripSlashes(string $a_str) : string
    {
        if (ini_get("magic_quotes_gpc")) {
            $a_str = stripslashes($a_str);
        }
        return $a_str;
    }


    public function saveCopyrightPosition() : bool
    {
        if (!isset($_POST['order'])) {
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return false;
        }

        $positions = $_POST['order'];
        asort($positions);
        $position = 0;
        foreach ($positions as $entry_id => $position_ignored) {
            $copyright = new ilMDCopyrightSelectionEntry($entry_id);
            $copyright->setOrderPosition($position++);
            $copyright->update();
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'showCopyrightSettings');
        return false;
    }
}
