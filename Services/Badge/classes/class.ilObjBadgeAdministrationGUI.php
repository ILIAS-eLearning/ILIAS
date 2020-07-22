<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");
include_once("./Services/Badge/classes/class.ilBadgeHandler.php");

/**
 * Badge Administration Settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjBadgeAdministrationGUI: ilPermissionGUI, ilBadgeManagementGUI
 * @ilCtrl_IsCalledBy ilObjBadgeAdministrationGUI: ilAdministrationGUI
 *
 * @ingroup ServicesBadge
 */
class ilObjBadgeAdministrationGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
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

        $this->lng->loadLanguageModule("badge");
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case 'ilbadgemanagementgui':
                $this->assertActive();
                $this->tabs_gui->setTabActive('activity');
                include_once "Services/Badge/classes/class.ilBadgeManagementGUI.php";
                $gui = new ilBadgeManagementGUI($this->ref_id, $this->obj_id, $this->type);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    public function getAdminTabs()
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
    
    protected function assertActive()
    {
        if (!ilBadgeHandler::getInstance()->isActive()) {
            $this->ctrl->redirect($this, "editSettings");
        }
    }
    
    
    //
    // settings
    //

    protected function editSettings($a_form = null)
    {
        $this->tabs_gui->setTabActive("settings");
        
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $obi = (bool) $form->getInput("act")
                ? (bool) $form->getInput("obi")
                : null;
        
            $handler = ilBadgeHandler::getInstance();
            $handler->setActive((bool) $form->getInput("act"));
            $handler->setObiActive($obi);
            $handler->setObiOrganisation(trim($form->getInput("obi_org")));
            $handler->setObiContact(trim($form->getInput("obi_cont")));
            $handler->setObiSalt(trim($form->getInput("obi_salt")));
            
            $handler->rebuildIssuerStaticUrl();
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
        
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function initFormSettings()
    {
        $ilAccess = $this->access;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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

        /* see bug #0020124
        $obi = new ilCheckboxInputGUI($this->lng->txt("badge_obi_activate"), "obi");
        $obi->setInfo($this->lng->txt("badge_obi_activate_info"));
        $form->addItem($obi);

            $obi_org = new ilTextInputGUI($this->lng->txt("badge_obi_organisation"), "obi_org");
            $obi_org->setRequired(true);
            $obi_org->setInfo($this->lng->txt("badge_obi_organisation_info"));
            $obi->addSubItem($obi_org);

            $obi_contact = new ilEmailInputGUI($this->lng->txt("badge_obi_contact"), "obi_cont");
            $obi_contact->setRequired(true);
            $obi_contact->setInfo($this->lng->txt("badge_obi_contact_info"));
            $obi->addSubItem($obi_contact);

            $obi_salt = new ilTextInputGUI($this->lng->txt("badge_obi_salt"), "obi_salt");
            $obi_salt->setRequired(true);
            $obi_salt->setInfo($this->lng->txt("badge_obi_salt_info"));
            $obi->addSubItem($obi_salt);
        */

        $handler = ilBadgeHandler::getInstance();
        $act->setChecked($handler->isActive());

        /* see bug 0020124
        $obi->setChecked($handler->isObiActive());
        $obi_org->setValue($handler->getObiOrganistation());
        $obi_contact->setValue($handler->getObiContact());
        $obi_salt->setValue($handler->getObiSalt());
        */
        
        return $form;
    }
    
    
    //
    // types
    //
    
    protected function listTypes()
    {
        $ilAccess = $this->access;
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("types");
        
        include_once "Services/Badge/classes/class.ilBadgeTypesTableGUI.php";
        $tbl = new ilBadgeTypesTableGUI(
            $this,
            "listTypes",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $this->tpl->setContent($tbl->getHTML());
    }
    
    protected function activateTypes()
    {
        $lng = $this->lng;
        
        $this->assertActive();
        
        $ids = $_POST["id"];
        if ($this->checkPermissionBool("write") && is_array($ids) && count($ids) > 0) {
            $handler = ilBadgeHandler::getInstance();
            $inactive = array();
            foreach ($handler->getInactiveTypes() as $type) {
                if (!in_array($type, $ids)) {
                    $inactive[] = $type;
                }
            }
            $handler->setInactiveTypes($inactive);
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listTypes");
    }
    
    protected function deactivateTypes()
    {
        $lng = $this->lng;
        
        $this->assertActive();
        
        $ids = $_POST["id"];
        if ($this->checkPermissionBool("write") && is_array($ids) && count($ids) > 0) {
            $handler = ilBadgeHandler::getInstance();
            $inactive = array_merge($handler->getInactiveTypes(), $ids);
            $handler->setInactiveTypes($inactive);
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listTypes");
    }
    
    
    //
    // images templates
    //
    
    protected function listImageTemplates()
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
        
        include_once "Services/Badge/classes/class.ilBadgeImageTemplateTableGUI.php";
        $tbl = new ilBadgeImageTemplateTableGUI(
            $this,
            "listImageTemplates",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $this->tpl->setContent($tbl->getHTML());
    }
    
    protected function addImageTemplate(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("imgtmpl");
        
        if (!$a_form) {
            $a_form = $this->initImageTemplateForm("create");
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function initImageTemplateForm($a_mode)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveBadge"));
        $form->setTitle($lng->txt("badge_image_template_form"));
        
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        $img = new ilImageFileInputGUI($lng->txt("image"), "img");
        $img->setSuffixes(array("png", "svg"));
        if ($a_mode == "create") {
            $img->setRequired(true);
        }
        $img->setALlowDeletion(false);
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
        
        if ($a_mode == "create") {
            $form->addCommandButton("saveImageTemplate", $lng->txt("save"));
        } else {
            $form->addCommandButton("updateImageTemplate", $lng->txt("save"));
        }
        $form->addCommandButton("listImageTemplates", $lng->txt("cancel"));
        
        return $form;
    }
    
    protected function saveImageTemplate()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->checkPermission("write");
        
        $form = $this->initImageTemplateForm("create");
        if ($form->checkInput()) {
            include_once "Services/Badge/classes/class.ilBadgeImageTemplate.php";
            $tmpl = new ilBadgeImageTemplate();
            $tmpl->setTitle($form->getInput("title"));
            $tmpl->setTypes($form->getInput("type"));
            $tmpl->create();
            
            $tmpl->uploadImage($_FILES["img"]);
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $form->setValuesByPost();
        $this->addImageTemplate($form);
    }
    
    protected function editImageTemplate(ilPropertyFormGUI $a_form = null)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("imgtmpl");
                
        $tmpl_id = $_REQUEST["tid"];
        if (!$tmpl_id) {
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $ilCtrl->setParameter($this, "tid", $tmpl_id);
        
        include_once "Services/Badge/classes/class.ilBadgeImageTemplate.php";
        $tmpl = new ilBadgeImageTemplate($tmpl_id);
        
        if (!$a_form) {
            $a_form = $this->initImageTemplateForm("edit");
            $this->setImageTemplateFormValues($a_form, $tmpl);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function setImageTemplateFormValues(ilPropertyFormGUI $a_form, ilBadgeImageTemplate $a_tmpl)
    {
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
    
    protected function updateImageTemplate()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->checkPermission("write");
        
        $tmpl_id = $_REQUEST["tid"];
        if (!$tmpl_id) {
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $ilCtrl->setParameter($this, "tid", $tmpl_id);
        
        include_once "Services/Badge/classes/class.ilBadgeImageTemplate.php";
        $tmpl = new ilBadgeImageTemplate($tmpl_id);
        
        $form = $this->initImageTemplateForm("update");
        if ($form->checkInput()) {
            $tmpl->setTitle($form->getInput("title"));
            
            if ($form->getInput("tmode") != "all") {
                $tmpl->setTypes($form->getInput("type"));
            } else {
                $tmpl->setTypes(null);
            }
            
            $tmpl->update();
            
            $tmpl->uploadImage($_FILES["img"]);
        
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "listImageTemplates");
        }
        
        $this->setImageTemplateFormValues($form, $tmpl);
        $form->setValuesByPost();
        $this->editImageTemplate($form);
    }
    
    protected function confirmDeleteImageTemplates()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("write");
        
        $tmpl_ids = $_REQUEST["id"];
        if (!$tmpl_ids) {
            $ilCtrl->redirect($this, "listImageTemplates");
        }
                
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listImageTemplates")
        );
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("badge_template_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listImageTemplates");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteImageTemplates");
        
        include_once("./Services/Badge/classes/class.ilBadgeImageTemplate.php");
        foreach ($tmpl_ids as $tmpl_id) {
            $tmpl = new ilBadgeImageTemplate($tmpl_id);
            $confirmation_gui->addItem("id[]", $tmpl_id, $tmpl->getTitle());
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function deleteImageTemplates()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->checkPermission("write");
        
        $tmpl_ids = $_REQUEST["id"];
        if ($tmpl_ids) {
            include_once("./Services/Badge/classes/class.ilBadgeImageTemplate.php");
            foreach ($tmpl_ids as $tmpl_id) {
                $tmpl = new ilBadgeImageTemplate($tmpl_id);
                $tmpl->delete();
            }
        
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        }
        
        $ilCtrl->redirect($this, "listImageTemplates");
    }
    
    
    //
    // object badges
    //
    
    protected function listObjectBadges()
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        
        $this->assertActive();
        $this->tabs_gui->setTabActive("obj_badges");
        
        include_once("./Services/Badge/classes/class.ilObjectBadgeTableGUI.php");
        $tbl = new ilObjectBadgeTableGUI(
            $this,
            "listObjectBadges",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyObjectFilter()
    {
        $ilAccess = $this->access;
        
        include_once "Services/Badge/classes/class.ilObjectBadgeTableGUI.php";
        $tbl = new ilObjectBadgeTableGUI(
            $this,
            "listObjectBadges",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listObjectBadges();
    }
    
    protected function resetObjectFilter()
    {
        $ilAccess = $this->access;
        
        include_once "Services/Badge/classes/class.ilObjectBadgeTableGUI.php";
        $tbl = new ilObjectBadgeTableGUI(
            $this,
            "listObjectBadges",
            $ilAccess->checkAccess("write", "", $this->object->getRefId())
        );
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listObjectBadges();
    }
    
    protected function listObjectBadgeUsers()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $parent_obj_id = $_REQUEST["pid"];
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
        
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "listUsers", null, null, $parent_obj_id, (int) $_REQUEST["bid"]);
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyUserFilter()
    {
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "listUsers", null, null, $parent_obj_id, (int) $_REQUEST["bid"]);
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listObjectBadgeUsers();
    }
    
    protected function resetUserFilter()
    {
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "listUsers", null, null, $parent_obj_id, (int) $_REQUEST["bid"]);
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listObjectBadgeUsers();
    }
    
    
    //
    // see ilBadgeManagementGUI
    //
    
    protected function getObjectBadgesFromMultiAction()
    {
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        
        $badge_ids = $_REQUEST["id"];
        if (!$badge_ids ||
            !$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilCtrl->redirect($this, "listObjectBadges");
        }
        
        return $badge_ids;
    }
    
    protected function toggleObjectBadges($a_status)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_ids = $this->getObjectBadgesFromMultiAction();
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->setActive($a_status);
            $badge->update();
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listObjectBadges");
    }
    
    protected function activateObjectBadges()
    {
        $this->toggleObjectBadges(true);
    }
    
    protected function deactivateObjectBadges()
    {
        $this->toggleObjectBadges(false);
    }
    
    protected function confirmDeleteObjectBadges()
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
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("badge_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listObjectBadges");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteObjectBadges");
            
        include_once "Services/Badge/classes/class.ilBadge.php";
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $parent = $badge->getParentMeta();
            
            // :TODO: container presentation
            $container = "(" . $parent["type"] . "/" .
                    $parent["id"] . ") " .
                    $parent["title"];
            if ((bool) $parent["deleted"]) {
                $container .= ' <span class="il_ItemAlertProperty">' . $lng->txt("deleted") . '</span>';
            }
            
            $confirmation_gui->addItem(
                "id[]",
                $badge_id,
                $container . " - " .
                $badge->getTitle() .
                " (" . sizeof(ilBadgeAssignment::getInstancesByBadgeId($badge_id)) . ")"
            );
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function deleteObjectBadges()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_ids = $this->getObjectBadgesFromMultiAction();
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->delete();
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listObjectBadges");
    }
}
