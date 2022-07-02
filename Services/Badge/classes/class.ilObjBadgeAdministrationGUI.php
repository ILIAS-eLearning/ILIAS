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
 * Badge Administration Settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjBadgeAdministrationGUI: ilPermissionGUI, ilBadgeManagementGUI
 * @ilCtrl_IsCalledBy ilObjBadgeAdministrationGUI: ilAdministrationGUI
 */
class ilObjBadgeAdministrationGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ilBadgeGUIRequest $badge_request;
    protected ilTabsGUI $tabs;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->type = "bdga";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->badge_request = new ilBadgeGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->lng->loadLanguageModule("badge");
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case 'ilbadgemanagementgui':
                $this->assertActive();
                $this->tabs_gui->setTabActive('activity');
                $gui = new ilBadgeManagementGUI($this->ref_id, $this->obj_id, $this->type);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
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
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
            
            if (ilBadgeHandler::getInstance()->isActive()) {
                $this->tabs_gui->addTab(
                    "types",
                    $this->lng->txt("badge_types"),
                    $this->ctrl->getLinkTarget($this, "listTypes")
                );

                $this->tabs_gui->addTab(
                    "imgtmpl",
                    $this->lng->txt("badge_image_templates"),
                    $this->ctrl->getLinkTarget($this, "listImageTemplates")
                );

                $this->tabs_gui->addTab(
                    "activity",
                    $this->lng->txt("badge_activity_badges"),
                    $this->ctrl->getLinkTargetByClass("ilbadgemanagementgui", "")
                );
                
                $this->tabs_gui->addTab(
                    "obj_badges",
                    $this->lng->txt("badge_object_badges"),
                    $this->ctrl->getLinkTarget($this, "listObjectBadges")
                );
            }
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }
    
    protected function assertActive() : void
    {
        if (!ilBadgeHandler::getInstance()->isActive()) {
            $this->ctrl->redirect($this, "editSettings");
        }
    }
    
    
    //
    // settings
    //

    protected function editSettings(ilPropertyFormGUI $a_form = null) : void
    {
        $this->tabs_gui->setTabActive("settings");
        
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function saveSettings() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $handler = ilBadgeHandler::getInstance();
            $handler->setActive((bool) $form->getInput("act"));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
        
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $ilAccess = $this->access;
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("badge_settings"));
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("editSettings", $this->lng->txt("cancel"));
        }

        $act = new ilCheckboxInputGUI($this->lng->txt("badge_service_activate"), "act");
        $act->setInfo($this->lng->txt("badge_service_activate_info"));
        $form->addItem($act);


        $handler = ilBadgeHandler::getInstance();
        $act->setChecked($handler->isActive());

        return $form;
    }
    
    
    //
    // types
    //
    
    protected function listTypes() : void
    {
        $ilAccess = $this->access;
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("types");
        
        $tbl = new ilBadgeTypesTableGUI(
            $this,
            "listTypes",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $this->tpl->setContent($tbl->getHTML());
    }
    
    protected function activateTypes() : void
    {
        $lng = $this->lng;
        
        $this->assertActive();
        
        $ids = $this->badge_request->getIds();
        if ($this->checkPermissionBool("write") && count($ids) > 0) {
            $handler = ilBadgeHandler::getInstance();
            $inactive = [];
            foreach ($handler->getInactiveTypes() as $type) {
                if (!in_array($type, $ids)) {
                    $inactive[] = $type;
                }
            }
            $handler->setInactiveTypes($inactive);
            
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listTypes");
    }
    
    protected function deactivateTypes() : void
    {
        $lng = $this->lng;
        
        $this->assertActive();
        
        $ids = $this->badge_request->getIds();
        if ($this->checkPermissionBool("write") && count($ids) > 0) {
            $handler = ilBadgeHandler::getInstance();
            $inactive = array_merge($handler->getInactiveTypes(), $ids);
            $handler->setInactiveTypes($inactive);
            
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listTypes");
    }
    
    
    //
    // images templates
    //
    
    protected function listImageTemplates() : void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
            
        $this->assertActive();
        $this->tabs_gui->setTabActive("imgtmpl");
        
        if ($this->checkPermissionBool("write")) {
            $ilToolbar->addButton(
                $lng->txt("badge_add_template"),
                $ilCtrl->getLinkTarget($this, "addImageTemplate")
            );
        }
        
        $tbl = new ilBadgeImageTemplateTableGUI(
            $this,
            "listImageTemplates",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $this->tpl->setContent($tbl->getHTML());
    }
    
    protected function addImageTemplate(
        ilPropertyFormGUI $a_form = null
    ) : void {
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("imgtmpl");
        
        if (!$a_form) {
            $a_form = $this->initImageTemplateForm("create");
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function initImageTemplateForm(
        string $a_mode
    ) : ilPropertyFormGUI {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveBadge"));
        $form->setTitle($lng->txt("badge_image_template_form"));
        
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        $img = new ilImageFileInputGUI($lng->txt("image"), "img");
        $img->setSuffixes(array("png", "svg"));
        if ($a_mode === "create") {
            $img->setRequired(true);
        }
        $img->setAllowDeletion(false);
        $form->addItem($img);
        
        $types_mode = new ilRadioGroupInputGUI($lng->txt("badge_template_types"), "tmode");
        $types_mode->setRequired(true);
        $form->addItem($types_mode);
        
        $type_all = new ilRadioOption($lng->txt("badge_template_types_all"), "all");
        $types_mode->addOption($type_all);
        
        $type_spec = new ilRadioOption($lng->txt("badge_template_types_specific"), "spec");
        $types_mode->addOption($type_spec);
        
        $types = new ilCheckboxGroupInputGUI($lng->txt("badge_types"), "type");
        $types->setRequired(true);
        $type_spec->addSubItem($types);
        
        foreach (ilBadgeHandler::getInstance()->getAvailableTypes() as $id => $type) {
            $types->addOption(new ilCheckboxOption($type->getCaption(), $id));
        }
        
        if ($a_mode === "create") {
            $form->addCommandButton("saveImageTemplate", $lng->txt("save"));
        } else {
            $form->addCommandButton("updateImageTemplate", $lng->txt("save"));
        }
        $form->addCommandButton("listImageTemplates", $lng->txt("cancel"));
        
        return $form;
    }
    
    protected function saveImageTemplate() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->checkPermission("write");
        
        $form = $this->initImageTemplateForm("create");
        if ($form->checkInput()) {
            $tmpl = new ilBadgeImageTemplate();
            $tmpl->setTitle($form->getInput("title"));
            $tmpl->setTypes($form->getInput("type"));
            $tmpl->create();
            
            $tmpl->uploadImage($_FILES["img"]);
            
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $form->setValuesByPost();
        $this->addImageTemplate($form);
    }
    
    protected function editImageTemplate(
        ilPropertyFormGUI $a_form = null
    ) : void {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("imgtmpl");
                
        $tmpl_id = $this->badge_request->getTemplateId();
        if (!$tmpl_id) {
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $ilCtrl->setParameter($this, "tid", $tmpl_id);
        
        $tmpl = new ilBadgeImageTemplate($tmpl_id);
        
        if (!$a_form) {
            $a_form = $this->initImageTemplateForm("edit");
            $this->setImageTemplateFormValues($a_form, $tmpl);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function setImageTemplateFormValues(
        ilPropertyFormGUI $a_form,
        ilBadgeImageTemplate $a_tmpl
    ) : void {
        $a_form->getItemByPostVar("title")->setValue($a_tmpl->getTitle());
        $a_form->getItemByPostVar("img")->setImage($a_tmpl->getImagePath());
        $a_form->getItemByPostVar("img")->setValue($a_tmpl->getImage());
        
        if ($a_tmpl->getTypes()) {
            $a_form->getItemByPostVar("tmode")->setValue("spec");
            $a_form->getItemByPostVar("type")->setValue($a_tmpl->getTypes());
        } else {
            $a_form->getItemByPostVar("tmode")->setValue("all");
        }
    }
    
    protected function updateImageTemplate() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->checkPermission("write");
        
        $tmpl_id = $this->badge_request->getTemplateId();
        if (!$tmpl_id) {
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $ilCtrl->setParameter($this, "tid", $tmpl_id);
        
        $tmpl = new ilBadgeImageTemplate($tmpl_id);
        
        $form = $this->initImageTemplateForm("update");
        if ($form->checkInput()) {
            $tmpl->setTitle($form->getInput("title"));
            
            if ($form->getInput("tmode") !== "all") {
                $tmpl->setTypes($form->getInput("type"));
            } else {
                $tmpl->setTypes(null);
            }
            
            $tmpl->update();
            
            $tmpl->uploadImage($_FILES["img"]);
        
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $this->setImageTemplateFormValues($form, $tmpl);
        $form->setValuesByPost();
        $this->editImageTemplate($form);
    }
    
    protected function confirmDeleteImageTemplates() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("write");
        
        $tmpl_ids = $this->badge_request->getIds();
        if (!$tmpl_ids) {
            $ilCtrl->redirect($this, "listImageTemplates");
        }
                
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listImageTemplates")
        );
        
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("badge_template_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listImageTemplates");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteImageTemplates");
        
        foreach ($tmpl_ids as $tmpl_id) {
            $tmpl = new ilBadgeImageTemplate($tmpl_id);
            $confirmation_gui->addItem("id[]", $tmpl_id, $tmpl->getTitle());
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function deleteImageTemplates() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->checkPermission("write");
        
        $tmpl_ids = $this->badge_request->getIds();
        if ($tmpl_ids) {
            foreach ($tmpl_ids as $tmpl_id) {
                $tmpl = new ilBadgeImageTemplate($tmpl_id);
                $tmpl->delete();
            }
        
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }
        
        $ilCtrl->redirect($this, "listImageTemplates");
    }
    
    
    //
    // object badges
    //
    
    protected function listObjectBadges() : void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("obj_badges");
        
        $tbl = new ilObjectBadgeTableGUI(
            $this,
            "listObjectBadges",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyObjectFilter() : void
    {
        $ilAccess = $this->access;
        
        $tbl = new ilObjectBadgeTableGUI(
            $this,
            "listObjectBadges",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listObjectBadges();
    }
    
    protected function resetObjectFilter() : void
    {
        $ilAccess = $this->access;
        
        $tbl = new ilObjectBadgeTableGUI(
            $this,
            "listObjectBadges",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listObjectBadges();
    }
    
    protected function listObjectBadgeUsers() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $parent_obj_id = $this->badge_request->getParentId();
        if (!$parent_obj_id) {
            $ilCtrl->redirect($this, "listObjectBadges");
        }
        
        $this->assertActive();
        
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listObjectBadges")
        );
        
        $ilCtrl->saveParameter($this, "pid");
        
        $tbl = new ilBadgeUserTableGUI($this, "listObjectBadgeUsers", 0, null, $parent_obj_id, $this->badge_request->getBadgeId());
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyListUsers() : void
    {
        $tbl = new ilBadgeUserTableGUI($this, "listObjectBadgeUsers", 0, null, 0, $this->badge_request->getBadgeId());
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listObjectBadgeUsers();
    }
    
    protected function resetListUsers() : void
    {
        $tbl = new ilBadgeUserTableGUI($this, "listObjectBadgeUsers", 0, null, 0, $this->badge_request->getBadgeId());
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listObjectBadgeUsers();
    }
    
    
    //
    // see ilBadgeManagementGUI
    //
    
    protected function getObjectBadgesFromMultiAction() : array
    {
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        
        $badge_ids = $this->badge_request->getIds();
        if (!$badge_ids ||
            !$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilCtrl->redirect($this, "listObjectBadges");
        }
        
        return $badge_ids;
    }
    
    protected function toggleObjectBadges(bool $a_status) : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_ids = $this->getObjectBadgesFromMultiAction();
        
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->setActive($a_status);
            $badge->update();
        }
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listObjectBadges");
    }
    
    protected function activateObjectBadges() : void
    {
        $this->toggleObjectBadges(true);
    }
    
    protected function deactivateObjectBadges() : void
    {
        $this->toggleObjectBadges(false);
    }
    
    protected function confirmDeleteObjectBadges() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
                                
        $badge_ids = $this->getObjectBadgesFromMultiAction();
                
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listObjectBadges")
        );
        
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("badge_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listObjectBadges");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteObjectBadges");
            
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $parent = $badge->getParentMeta();
            
            // :TODO: container presentation
            $container = "(" . $parent["type"] . "/" .
                    $parent["id"] . ") " .
                    $parent["title"];
            if ($parent["deleted"]) {
                $container .= ' <span class="il_ItemAlertProperty">' . $lng->txt("deleted") . '</span>';
            }
            
            $confirmation_gui->addItem(
                "id[]",
                $badge_id,
                $container . " - " .
                $badge->getTitle() .
                " (" . count(ilBadgeAssignment::getInstancesByBadgeId($badge_id)) . ")"
            );
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function deleteObjectBadges() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_ids = $this->getObjectBadgesFromMultiAction();
        
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->delete();
        }
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listObjectBadges");
    }
}
