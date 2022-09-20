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

/**
 * User profile info settings UI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilUserProfileInfoSettingsGUI:
 */
class ilUserProfileInfoSettingsGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilUserProfilePromptService $user_prompt;
    protected ilProfilePromptSettings $prompt_settings;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->user_prompt = new ilUserProfilePromptService();
        $this->prompt_settings = $this->user_prompt->data()->getSettings();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("show", "save"))) {
                    $this->$cmd();
                }
        }
    }

    public function show(): void
    {
        $tpl = $this->tpl;

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    public function initForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $prompt_settings = $this->prompt_settings;

        $lng->loadLanguageModule("meta");

        $form = new ilPropertyFormGUI();

        // default info text
        $first = true;
        foreach ($lng->getInstalledLanguages() as $l) {
            // info text
            $ti = new ilTextAreaInputGUI($lng->txt("meta_l_" . $l), "user_profile_info_text_" . $l);
            $ti->setRows(7);
            if ($first) {
                $ti->setInfo($lng->txt("user_profile_info_text_info"));
            }
            $first = false;
            $ti->setValue($prompt_settings->getInfoText($l));
            $form->addItem($ti);
        }

        // prompting settings
        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($lng->txt('user_prompting_settings'));
        $form->addItem($sec);

        // prompt mode
        $radg = new ilRadioGroupInputGUI($lng->txt("user_prompting_recurrence"), "prompt_mode");
        $radg->setValue($prompt_settings->getMode());

        // ... only if incomplete
        $op1 = new ilRadioOption(
            $lng->txt("user_prompt_incomplete"),
            ilProfilePromptSettings::MODE_INCOMPLETE_ONLY,
            $lng->txt("user_prompt_incomplete_info")
        );
        $radg->addOption($op1);

        // ... once after login
        $op2 = new ilRadioOption(
            $lng->txt("user_prompt_once_after_login"),
            ilProfilePromptSettings::MODE_ONCE_AFTER_LOGIN,
            $lng->txt("user_prompt_once_after_login_info")
        );
        $radg->addOption($op2);

        // days after login
        $ti = new ilNumberInputGUI($lng->txt("days"), "days_after_login");
        $ti->setMaxLength(4);
        $ti->setSize(4);
        $ti->setValue($prompt_settings->getDays());
        $op2->addSubItem($ti);

        // ... repeatly
        $op3 = new ilRadioOption(
            $lng->txt("user_prompt_repeat"),
            ilProfilePromptSettings::MODE_REPEAT,
            $lng->txt("user_prompt_repeat_info")
        );
        $radg->addOption($op3);

        // repeat all x days
        $ti = new ilNumberInputGUI($lng->txt("days"), "days_repeat");
        $ti->setMaxLength(4);
        $ti->setSize(4);
        $ti->setValue($prompt_settings->getDays());
        $op3->addSubItem($ti);

        $form->addItem($radg);


        // prompting info text
        $first = true;
        foreach ($lng->getInstalledLanguages() as $l) {
            // info text
            $ti = new ilTextAreaInputGUI($lng->txt("meta_l_" . $l), "user_profile_prompt_text_" . $l);
            $ti->setRows(7);
            if ($first) {
                $ti->setInfo($lng->txt("user_profile_prompt_text_info"));
            }
            $first = false;
            $ti->setValue($prompt_settings->getPromptText($l));
            $form->addItem($ti);
        }

        $form->addCommandButton("save", $lng->txt("save"));

        $form->setTitle($lng->txt("user_profile_info_std"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function save(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $days = ($form->getInput("prompt_mode") == ilProfilePromptSettings::MODE_ONCE_AFTER_LOGIN)
                ? $form->getInput("days_after_login")
                : $form->getInput("days_repeat");
            $info_text = $prompt_text = [];
            foreach ($lng->getInstalledLanguages() as $l) {
                $info_text[$l] = $form->getInput("user_profile_info_text_" . $l);
                $prompt_text[$l] = $form->getInput("user_profile_prompt_text_" . $l);
            }
            $this->user_prompt->data()->saveSettings($this->user_prompt->settings(
                $form->getInput("prompt_mode"),
                $days,
                $info_text,
                $prompt_text
            ));
            /*$setting = new ilSetting("user");
            foreach ($lng->getInstalledLanguages() as $l)
            {
                $setting->set("user_profile_info_".$l, $form->getInput("user_profile_info_text_".$l));
            }*/

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "show");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }
}
