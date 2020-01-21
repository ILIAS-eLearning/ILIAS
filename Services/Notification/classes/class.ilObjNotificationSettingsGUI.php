<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles general notification settings, see e.g.
 * https://www.ilias.de/docu/goto_docu_wiki_wpage_3457_1357.html
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ServiceNotification
 */
class ilObjNotificationSettingsGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * Constructor
     */
    public function __construct($a_ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        include_once("./Services/Notification/classes/class.ilObjNotificationSettings.php");
        $this->settings = new ilObjNotificationSettings($this->obj_id);
    }

    /**
     * Execute command
     */
    public function executeCommand()
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

    /**
     * Show form
     */
    protected function show()
    {
        $tpl = $this->tpl;

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }


    /**
     * Init settings form
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $form->setFormAction($ctrl->getFormAction($this, 'save'));
        $form->setTitle($lng->txt('obj_notification_settings'));

        $radio_grp = new ilRadioGroupInputGUI($lng->txt("obj_activation"), 'notification_type');
        $radio_grp->setValue('0');

        $opt_default  = new ilRadioOption($lng->txt("obj_user_decides_notification"), '0');
        $opt_0 = new ilRadioOption($lng->txt("obj_settings_for_all_members"), '1');

        $radio_grp->addOption($opt_default);
        $radio_grp->addOption($opt_0);

        $chb_2 = new ilCheckboxInputGUI($lng->txt('obj_user_not_disable_not'), 'no_opt_out');
        $chb_2->setValue(1);

        $opt_0->addSubItem($chb_2);
        $form->addItem($radio_grp);

        if ($this->settings->getMode() == ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT) {
            $radio_grp->setValue('1');
        }
        if ($this->settings->getMode() == ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
            $radio_grp->setValue('1');
            $chb_2->setChecked(true);
        }

        $form->addCommandButton("save", $lng->txt("save"));

        $form->setTitle($this->lng->txt("notifications"));

        return $form;
    }

    /**
     * Save
     */
    protected function save()
    {
        $ctrl = $this->ctrl;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->settings->setMode(ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION);
            if ($_POST['notification_type'] == "1") {
                if ((int) $form->getInput('no_opt_out')) {
                    $this->settings->setMode(ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT);
                } else {
                    $this->settings->setMode(ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT);
                }
            }
            $this->settings->save();
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $ctrl->redirect($this, "show");
        }

        $form->setValuesByPost();
        $this->show();
    }
}
