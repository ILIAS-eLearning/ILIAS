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

use ILIAS\MediaCast\StandardGUIRequest;

/**
 * Media Cast Settings.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjMediaCastSettingsGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjMediaCastSettingsGUI: ilAdministrationGUI
 */
class ilObjMediaCastSettingsGUI extends ilObjectGUI
{
    protected StandardGUIRequest $mc_request;
    protected ilMediaCastSettings $mc_settings;

    /**
     * @param mixed $a_data
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = 'mcts';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('mcst');
        $this->mc_settings = ilMediaCastSettings::_getInstance();
        $this->mc_request = $DIC->mediaCast()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        $rbac_system = $this->rbac_system;

        if ($rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "mcst_edit_settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(): void
    {
        $this->tabs_gui->setTabActive('mcst_edit_settings');
        $this->initFormSettings();
    }

    public function saveSettings(): void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $purposeSuffixes = [];
        $form = $this->getForm();

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            foreach ($this->mc_settings->getPurposeSuffixes() as $purpose => $filetypes) {
                $purposeSuffixes[$purpose] = explode(",", preg_replace("/[^\w,]/", "", strtolower($this->mc_request->getSettingsPurpose($purpose))));
            }

            if ($form->checkInput()) {
                $this->mc_settings->setPurposeSuffixes($purposeSuffixes);
                $this->mc_settings->setDefaultAccess($form->getInput("defaultaccess"));
                $this->mc_settings->setMimeTypes(explode(",", $form->getInput("mimetypes")));
                $this->mc_settings->setVideoCompletionThreshold((int) $form->getInput("video_completion_threshold"));

                $this->mc_settings->save();

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            } else {
                $form->setValuesByPost();
                $this->initFormSettings($form);
                return;
            }
        }
        $this->initMediaCastSettings();
        $ilCtrl->redirect($this, "view");
    }

    public function cancel(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "view");
    }

    protected function initMediaCastSettings(): void
    {
        $this->mc_settings = ilMediaCastSettings::_getInstance();
    }

    protected function getForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('settings'));

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        //Default Visibility
        $radio_group = new ilRadioGroupInputGUI($lng->txt("mcst_default_visibility"), "defaultaccess");
        $radio_option = new ilRadioOption($lng->txt("mcst_visibility_users"), "users");
        $radio_group->addOption($radio_option);
        $radio_option = new ilRadioOption($lng->txt("mcst_visibility_public"), "public");
        $radio_group->addOption($radio_option);
        $radio_group->setInfo($lng->txt("mcst_news_item_visibility_info"));
        $radio_group->setRequired(false);
        $radio_group->setValue($this->mc_settings->getDefaultAccess());
        #$ch->addSubItem($radio_group);
        $form->addItem($radio_group);

        // video completion threshold
        $ti = new ilNumberInputGUI($lng->txt("mcst_video_completion_threshold"), "video_completion_threshold");
        $ti->setMaxLength(3);
        $ti->setSize(3);
        $ti->setSuffix("%");
        $ti->setMaxValue(100);
        $ti->setMinValue(0);
        $ti->setInfo($lng->txt("mcst_video_completion_threshold_info"));
        $ti->setValue($this->mc_settings->getVideoCompletionThreshold());
        $form->addItem($ti);

        foreach ($this->mc_settings->getPurposeSuffixes() as $purpose => $filetypes) {
            if ($purpose !== "VideoAlternative") {
                $text = new ilTextInputGUI($lng->txt("mcst_" . strtolower($purpose) . "_settings_title"), $purpose);
                $text->setValue(implode(",", $filetypes));
                $text->setInfo($lng->txt("mcst_" . strtolower($purpose) . "_settings_info"));
                $form->addItem($text);
            }
        }

        $text = new ilTextAreaInputGUI($lng->txt("mcst_mimetypes"), "mimetypes");
        $text->setInfo($lng->txt("mcst_mimetypes_info"));
        $text->setCols(120);
        $text->setRows(10);
        if (is_array($this->mc_settings->getMimeTypes())) {
            $text->setValue(implode(",", $this->mc_settings->getMimeTypes()));
        }
        $form->addItem($text);

        return $form;
    }

    protected function initFormSettings(?ilPropertyFormGUI $form = null): void
    {
        if (!$form) {
            $form = $this->getForm();
        }
        $this->tpl->setContent($form->getHTML());
    }
}
