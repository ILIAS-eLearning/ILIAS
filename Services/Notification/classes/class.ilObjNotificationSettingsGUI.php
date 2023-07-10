<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Handles general notification settings, see e.g.
 * https://www.ilias.de/docu/goto_docu_wiki_wpage_3457_1357.html
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjNotificationSettingsGUI
{
    protected ilObjNotificationSettings $settings;
    protected int $ref_id;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected int $obj_id;

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->settings = new ilObjNotificationSettings($this->obj_id);
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("show", "save"))) {
                    $this->$cmd();
                }
        }
    }

    protected function show(): void
    {
        $tpl = $this->tpl;

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    protected function initForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();

        $form->setFormAction($ctrl->getFormAction($this, 'save'));
        $form->setTitle($lng->txt('obj_notification_settings'));

        $radio_grp = new ilRadioGroupInputGUI($lng->txt("obj_activation"), 'notification_type');
        $radio_grp->setValue('0');

        $opt_default = new ilRadioOption($lng->txt("obj_user_decides_notification"), '0');
        $opt_0 = new ilRadioOption($lng->txt("obj_settings_for_all_members"), '1');

        $radio_grp->addOption($opt_default);
        $radio_grp->addOption($opt_0);

        $chb_2 = new ilCheckboxInputGUI($lng->txt('obj_user_not_disable_not'), 'no_opt_out');
        $chb_2->setValue(1);

        $opt_0->addSubItem($chb_2);
        $form->addItem($radio_grp);

        if ($this->settings->getMode() === ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT) {
            $radio_grp->setValue('1');
        }
        if ($this->settings->getMode() === ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
            $radio_grp->setValue('1');
            $chb_2->setChecked(true);
        }

        $form->addCommandButton("save", $lng->txt("save"));

        $form->setTitle($this->lng->txt("notifications"));

        return $form;
    }

    protected function save(): void
    {
        $ctrl = $this->ctrl;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->settings->setMode(ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION);
            if ($form->getInput('notification_type') === "1") {
                if ((int) $form->getInput('no_opt_out')) {
                    $this->settings->setMode(ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT);
                } else {
                    $this->settings->setMode(ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT);
                }
            }
            $this->settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $ctrl->redirect($this, "show");
        }

        $form->setValuesByPost();
        $this->show();
    }
}
