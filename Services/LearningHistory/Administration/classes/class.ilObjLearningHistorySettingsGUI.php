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
 * Learning History Settings.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjLearningHistorySettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjLearningHistorySettingsGUI: ilAdministrationGUI
 */
class ilObjLearningHistorySettingsGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ilTabsGUI $tabs;
    protected \ILIAS\DI\UIServices $ui;
    protected ilSetting $setting;
    protected ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->ui = $DIC->ui();
        $this->setting = $DIC->settings();
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->type = 'lhts';

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("lhist");
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $rbacsystem = $this->rbacsystem;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("editSettings");

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $tabs->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd === "view") {
                    $cmd = "editSettings";
                }
                if (in_array($cmd, ["editSettings", "saveSettings"])) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $tabs->addTab(
                "settings",
                $lng->txt("settings"),
                $ctrl->getLinkTarget($this, "editSettings")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $tabs->addTab(
                "perm_settings",
                $lng->txt("perm_settings"),
                $ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    public function editSettings(): void
    {
        $main_tpl = $this->main_tpl;
        $ui = $this->ui;
        $tabs = $this->tabs;

        $tabs->activateTab("settings");

        $form = $this->initForm();
        $main_tpl->setContent($ui->renderer()->render($form));
    }

    public function initForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $setting = $this->setting;

        $fields["enable_learning_history"] = $f->input()->field()->checkbox(
            $lng->txt("lhist_enable_learning_history"),
            $lng->txt("lhist_enable_learning_history_info")
        )
            ->withValue((bool) $setting->get("enable_learning_history"));

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("settings"));


        $form_action = $ctrl->getLinkTarget($this, "saveSettings");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    public function saveSettings(): void
    {
        $request = $this->request;
        $form = $this->initForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $setting = $this->setting;

        if ($request->getMethod() === "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_array($data["sec"])) {
                $setting->set("enable_learning_history", (int) ($data["sec"]["enable_learning_history"]));
                $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            }
        }
        $ctrl->redirect($this, "editSettings");
    }
}
