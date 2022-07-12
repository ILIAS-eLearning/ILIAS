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

/**
 * Media Cast Settings.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjTaggingSettingsGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjTaggingSettingsGUI: ilAdministrationGUI
 */
class ilObjTaggingSettingsGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ilTabsGUI $tabs;
    protected string $requested_tag;

    /**
     * @inheritDoc
     */
    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->type = 'tags';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('tagging');

        $params = $this->request->getQueryParams();
        $body = $this->request->getParsedBody();
        $this->requested_tag = (string) ilUtil::stripSlashes($body["tag"] ?? ($params["tag"] ?? ""));
    }

    /**
     * Execute command
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilObjectException($this->lng->txt("permission_denied"));
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

    public function getAdminTabs() : void
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "tagging_edit_settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function addSubTabs() : void
    {
        $ilTabs = $this->tabs;

        $tags_set = new ilSetting("tags");
        if ($tags_set->get("enable")) {
            $ilTabs->addSubTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );

            if ($this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
                $ilTabs->addSubTab(
                    "forbidden_tags",
                    $this->lng->txt("tagging_forbidden_tags"),
                    $this->ctrl->getLinkTarget($this, "editForbiddenTags")
                );

                $ilTabs->addSubTab(
                    "users",
                    $this->lng->txt("users"),
                    $this->ctrl->getLinkTarget($this, "showUsers")
                );
            }
        }
    }


    public function editSettings() : void
    {
        $ilTabs = $this->tabs;

        $this->tabs_gui->setTabActive('tagging_edit_settings');
        $this->addSubTabs();
        $ilTabs->activateSubTab("settings");
        $form = $this->initFormSettings();
        $this->tpl->setContent($form->getHTML());
    }

    public function saveSettings() : void
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        $this->checkPermission("write");

        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $tags_set = new ilSetting("tags");
            $tags_set->set("enable", $form->getInput("enable_tagging"));
            $tags_set->set("enable_all_users", $form->getInput("enable_all_users"));
            $ilSetting->set("block_activated_pdtag", $form->getInput("enable_tagging"));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }
        $ilCtrl->redirect($this, "view");
    }

    public function cancel() : void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "view");
    }

    /**
     * Init settings property form
     */
    protected function initFormSettings() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $tags_set = new ilSetting("tags");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('tagging_settings'));

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        // enable tagging
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("tagging_enable_tagging"),
            "enable_tagging"
        );
        $cb_prop->setValue("1");
        $cb_prop->setChecked((bool) $tags_set->get("enable"));

        // enable all users info
        $cb_prop2 = new ilCheckboxInputGUI(
            $lng->txt("tagging_enable_all_users"),
            "enable_all_users"
        );
        $cb_prop2->setInfo($lng->txt("tagging_enable_all_users_info"));
        $cb_prop2->setChecked((bool) $tags_set->get("enable_all_users"));
        $cb_prop->addSubItem($cb_prop2);

        $form->addItem($cb_prop);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_TAGGING,
            $form,
            $this
        );

        return $form;
    }

    //
    //
    // FORBIDDEN TAGS
    //
    //

    public function editForbiddenTags() : void
    {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;

        $this->addSubTabs();
        $ilTabs->activateSubTab("forbidden_tags");
        $ilTabs->activateTab("tagging_edit_settings");
        $form = $this->initForbiddenTagsForm();

        $tpl->setContent($form->getHTML());
    }

    /**
     * Init forbidden tags form.
     */
    public function initForbiddenTagsForm() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $tags_set = new ilSetting("tags");
        $forbidden = $tags_set->get("forbidden_tags");

        $forb_str = "";
        if ($forbidden != "") {
            $tags_array = unserialize($forbidden, ['allowed_classes' => false]);
            $forb_str = implode(" ", $tags_array);
        }

        $form = new ilPropertyFormGUI();

        // tags
        $ta = new ilTextAreaInputGUI($this->lng->txt("tagging_tags"), "forbidden_tags");
        $ta->setCols(50);
        $ta->setRows(10);
        $ta->setValue($forb_str);
        $form->addItem($ta);

        $form->addCommandButton("saveForbiddenTags", $lng->txt("save"));

        $form->setTitle($lng->txt("tagging_forbidden_tags"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Save forbidden tags
     */
    public function saveForbiddenTags() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initForbiddenTagsForm();


        $this->checkPermission("write");

        if ($form->checkInput()) {
            $tags = str_replace(",", " ", $form->getInput("forbidden_tags"));
            $tags = explode(" ", $tags);
            $tags_array = array();
            foreach ($tags as $t) {
                $t = strtolower(trim($t));
                if ($t != "") {
                    $tags_array[$t] = $t;
                }
            }

            asort($tags_array);

            $tags_set = new ilSetting("tags");

            $tags_set->set("forbidden_tags", serialize($tags_array));

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "editForbiddenTags");
    }

    //
    //
    // USER INFO
    //
    //

    public function showUsers(bool $a_search = false) : void
    {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $this->addSubTabs();
        $ilTabs->activateTab("tagging_edit_settings");
        $ilTabs->activateSubTab("users");

        $tag = $this->requested_tag;

        // tag input
        $ti = new ilTextInputGUI($lng->txt("tagging_tag"), "tag");
        $ti->setSize(15);
        $ti->setValue($tag);
        $ilToolbar->addInputItem($ti, true);

        $ilToolbar->addFormButton($lng->txt("tagging_search_users"), "searchUsersForTag");
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "searchUsersForTag"));

        if ($a_search) {
            $ilCtrl->setParameter($this, "tag", $tag);
            $table = new ilUserForTagTableGUI(
                $this,
                "searchUsersForTag",
                $tag
            );
            $tpl->setContent($table->getHTML());
        }
    }

    /**
     * Search users for tag
     */
    public function searchUsersForTag() : void
    {
        $this->showUsers(true);
    }
}
