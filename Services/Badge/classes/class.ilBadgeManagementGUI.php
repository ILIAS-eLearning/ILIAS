<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Badge/classes/class.ilBadgeHandler.php");

/**
 * Class ilBadgeManagementGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilBadgeManagementGUI: ilPropertyFormGUI
 * @package ServicesBadge
 */
class ilBadgeManagementGUI
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
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $parent_ref_id; // [int]
    protected $parent_obj_id; // [int]
    protected $parent_obj_type; // [string]
    
    const CLIPBOARD_ID = "bdgclpbrd";
        
    /**
     * Construct
     *
     * @param int $a_parent_ref_id
     * @param int $a_parent_obj_id
     * @param string $a_parent_obj_type
     * @return self
     */
    public function __construct($a_parent_ref_id, $a_parent_obj_id = null, $a_parent_obj_type = null)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        $lng = $DIC->language();
        
        $this->parent_ref_id = $a_parent_ref_id;
        $this->parent_obj_id = $a_parent_obj_id
            ? $a_parent_obj_id
            : ilObject::_lookupObjId($a_parent_ref_id);
        $this->parent_obj_type = $a_parent_obj_type
            ? $a_parent_obj_type
            : ilObject::_lookupType($this->parent_obj_id);

        if (!ilBadgeHandler::getInstance()->isObjectActive($this->parent_obj_id)) {
            throw new ilException("inactive object");
        }
        
        $lng->loadLanguageModule("badge");
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("listBadges");

        switch ($next_class) {
            case "ilpropertyformgui":
                // ajax - update
                if ((int) $_REQUEST["bid"]) {
                    include_once "./Services/Badge/classes/class.ilBadge.php";
                    $badge = new ilBadge((int) $_REQUEST["bid"]);
                    $type = $badge->getTypeInstance();
                    $form = $this->initBadgeForm("edit", $type, $badge->getTypeId());
                    $this->setBadgeFormValues($form, $badge, $type);
                }
                // ajax- create
                else {
                    $type_id = $_REQUEST["type"];
                    $ilCtrl->setParameter($this, "type", $type_id);
                    $handler = ilBadgeHandler::getInstance();
                    $type = $handler->getTypeInstanceByUniqueId($type_id);
                    $form = $this->initBadgeForm("create", $type, $type_id);
                }
                $ilCtrl->forwardCommand($form);
                break;
                
            /*
            case "illplistofsettingsgui":
                $id = $_GET["lpid"];
                if($id)
                {
                    $ilCtrl->saveParameter($this, "bid");
                    $ilCtrl->saveParameter($this, "lpid");

                    $ilTabs->clearTargets();
                    $ilTabs->setBackTarget(
                        $lng->txt("back"),
                        $ilCtrl->getLinkTarget($this, "editBadge")
                    );
                    include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                    include_once "Services/Tracking/classes/repository_statistics/class.ilLPListOfSettingsGUI.php";
                    $lpgui = new ilLPListOfSettingsGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $id);
                    $ilCtrl->forwardCommand($lpgui);
                    break;
                }
            */
                
            default:
                $this->$cmd();
                break;
        }
        
        return true;
    }
    
    protected function setTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->addSubTab(
            "badges",
            $lng->txt("obj_bdga"),
            $ilCtrl->getLinkTarget($this, "listBadges")
        );
                
        $ilTabs->addSubTab(
            "users",
            $lng->txt("users"),
            $ilCtrl->getLinkTarget($this, "listUsers")
        );
        
        $ilTabs->activateSubTab($a_active);
    }
    
    protected function hasWrite()
    {
        $ilAccess = $this->access;
        return $ilAccess->checkAccess("write", "", $this->parent_ref_id);
    }
    
    protected function listBadges()
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $this->setTabs("badges");
        
        if ($this->hasWrite()) {
            $handler = ilBadgeHandler::getInstance();
            $valid_types = $handler->getAvailableTypesForObjType($this->parent_obj_type);
            if ($valid_types) {
                include_once "Services/Badge/classes/class.ilBadge.php";
                $options = array();
                foreach ($valid_types as $id => $type) {
                    $options[$id] = ($this->parent_obj_type != "bdga")
                        ? ilBadge::getExtendedTypeCaption($type)
                        : $type->getCaption();
                }
                asort($options);
                
                include_once "Services/Form/classes/class.ilSelectInputGUI.php";
                $drop = new ilSelectInputGUI($lng->txt("type"), "type");
                $drop->setOptions($options);
                $ilToolbar->addInputItem($drop, true);
                
                $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "addBadge"));
                $ilToolbar->addFormButton($lng->txt("create"), "addBadge");
            } else {
                ilUtil::sendInfo($lng->txt("badge_no_valid_types_for_obj"));
            }
            
            if (is_array($_SESSION[self::CLIPBOARD_ID])) {
                if ($valid_types) {
                    $ilToolbar->addSeparator();
                }
                            
                $tt = array();
                foreach ($this->getValidBadgesFromClipboard() as $badge) {
                    $tt[] = $badge->getTitle();
                }
                $ttid = "bdgpst";
                include_once "Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php";
                ilTooltipGUI::addTooltip(
                    $ttid,
                    implode("<br />", $tt),
                    "",
                    "bottom center",
                    "top center",
                    false
                );
                 
                $lng->loadLanguageModule("content");
                $ilToolbar->addButton(
                    $lng->txt("cont_paste_from_clipboard") .
                        " (" . sizeof($tt) . ")",
                    $ilCtrl->getLinkTarget($this, "pasteBadges"),
                    "",
                    "",
                    "",
                    $ttid
                );
                $ilToolbar->addButton(
                    $lng->txt("clear_clipboard"),
                    $ilCtrl->getLinkTarget($this, "clearClipboard")
                );
            }
        }
        
        include_once "Services/Badge/classes/class.ilBadgeTableGUI.php";
        $tbl = new ilBadgeTableGUI($this, "listBadges", $this->parent_obj_id, $this->hasWrite());
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyBadgeFilter()
    {
        include_once "Services/Badge/classes/class.ilBadgeTableGUI.php";
        $tbl = new ilBadgeTableGUI($this, "listBadges", $this->parent_obj_id, $this->hasWrite());
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listBadges();
    }
    
    protected function resetBadgeFilter()
    {
        include_once "Services/Badge/classes/class.ilBadgeTableGUI.php";
        $tbl = new ilBadgeTableGUI($this, "listBadges", $this->parent_obj_id, $this->hasWrite());
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listBadges();
    }
    
    
    //
    // badge (CRUD)
    //
    
    protected function addBadge(ilPropertyFormGUI $a_form = null)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $type_id = $_REQUEST["type"];
        if (!$type_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        $ilCtrl->setParameter($this, "type", $type_id);
        
        $handler = ilBadgeHandler::getInstance();
        $type = $handler->getTypeInstanceByUniqueId($type_id);
        if (!$type) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        if (!$a_form) {
            $a_form = $this->initBadgeForm("create", $type, $type_id);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function initBadgeForm($a_mode, ilBadgeType $a_type, $a_type_unique_id)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveBadge"));
        $form->setTitle($lng->txt("badge_badge") . ' "' . $a_type->getCaption() . '"');
        
        $active = new ilCheckboxInputGUI($lng->txt("active"), "act");
        $form->addItem($active);
        
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        $desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
        $desc->setRequired(true);
        $form->addItem($desc);
        
        $crit = new ilTextAreaInputGUI($lng->txt("badge_criteria"), "crit");
        $crit->setRequired(true);
        $form->addItem($crit);
        
        if ($a_mode == "create") {
            // upload
    
            $img_mode = new ilRadioGroupInputGUI($lng->txt("image"), "img_mode");
            $img_mode->setRequired(true);
            $form->addItem($img_mode);
    
            $img_mode_tmpl = new ilRadioOption($lng->txt("badge_image_from_template"), "tmpl");
            $img_mode->addOption($img_mode_tmpl);

            $img_mode_up = new ilRadioOption($lng->txt("badge_image_from_upload"), "up");
            $img_mode->addOption($img_mode_up);
            
            $img_upload = new ilImageFileInputGUI($lng->txt("file"), "img");
            $img_upload->setRequired(true);
            $img_upload->setSuffixes(array("png", "svg"));
            $img_mode_up->addSubItem($img_upload);

            // templates
            
            include_once "Services/Badge/classes/class.ilBadgeImageTemplate.php";
            $valid_templates = ilBadgeImageTemplate::getInstancesByType($a_type_unique_id);
            if (sizeof($valid_templates)) {
                $options = array();
                $options[""] = $lng->txt("please_select");
                foreach ($valid_templates as $tmpl) {
                    $options[$tmpl->getId()] = $tmpl->getTitle();
                }

                $tmpl = new ilSelectInputGUI($lng->txt("badge_image_template_form"), "tmpl");
                $tmpl->setRequired(true);
                $tmpl->setOptions($options);
                $img_mode_tmpl->addSubItem($tmpl);
            } else {
                // no templates, activate upload
                $img_mode_tmpl->setDisabled(true);
                $img_mode->setValue("up");
            }
        } else {
            $img_upload = new ilImageFileInputGUI($lng->txt("image"), "img");
            $img_upload->setSuffixes(array("png", "svg"));
            $img_upload->setALlowDeletion(false);
            $form->addItem($img_upload);
        }
        
        $valid = new ilTextInputGUI($lng->txt("badge_valid"), "valid");
        $form->addItem($valid);
        
        $custom = $a_type->getConfigGUIInstance();
        if ($custom &&
            $custom instanceof ilBadgeTypeGUI) {
            $custom->initConfigForm($form, $this->parent_ref_id);
        }
        
        // :TODO: valid date/period
        
        if ($a_mode == "create") {
            $form->addCommandButton("saveBadge", $lng->txt("save"));
        } else {
            $form->addCommandButton("updateBadge", $lng->txt("save"));
        }
        $form->addCommandButton("listBadges", $lng->txt("cancel"));
        
        return $form;
    }
    
    protected function saveBadge()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $type_id = $_REQUEST["type"];
        if (!$type_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        $ilCtrl->setParameter($this, "type", $type_id);
        
        $handler = ilBadgeHandler::getInstance();
        $type = $handler->getTypeInstanceByUniqueId($type_id);
        if (!$type) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        $form = $this->initBadgeForm("create", $type, $type_id);
        $custom = $type->getConfigGUIInstance();

        if ($form->checkInput() &&
            (!$custom || $custom->validateForm($form))) {
            include_once "Services/Badge/classes/class.ilBadge.php";
            $badge = new ilBadge();
            $badge->setParentId($this->parent_obj_id); // :TODO: ref_id?
            $badge->setTypeId($type_id);
            $badge->setActive($form->getInput("act"));
            $badge->setTitle($form->getInput("title"));
            $badge->setDescription($form->getInput("desc"));
            $badge->setCriteria($form->getInput("crit"));
            $badge->setValid($form->getInput("valid"));

            if ($custom &&
                $custom instanceof ilBadgeTypeGUI) {
                $badge->setConfiguration($custom->getConfigFromForm($form));
            }
                        
            $badge->create();
            
            if ($form->getInput("img_mode") == "up") {
                $badge->uploadImage($_FILES["img"]);
            } else {
                $tmpl = new ilBadgeImageTemplate($form->getInput("tmpl"));
                $badge->importImage($tmpl->getImage(), $tmpl->getImagePath());
            }
                    
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "listBadges");
        }
        
        $form->setValuesByPost();
        $this->addBadge($form);
    }
    
    protected function editBadge(ilPropertyFormGUI $a_form = null)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $badge_id = $_REQUEST["bid"];
        if (!$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        $ilCtrl->setParameter($this, "bid", $badge_id);
        
        include_once "./Services/Badge/classes/class.ilBadge.php";
        $badge = new ilBadge($badge_id);
        
        $static_cnt = ilBadgeHandler::getInstance()->countStaticBadgeInstances($badge);
        if ($static_cnt) {
            ilUtil::sendInfo(sprintf($lng->txt("badge_edit_with_published"), $static_cnt));
        }
        
        if (!$a_form) {
            $type = $badge->getTypeInstance();
            $a_form = $this->initBadgeForm("edit", $type, $badge->getTypeId());
            $this->setBadgeFormValues($a_form, $badge, $type);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function setBadgeFormValues(ilPropertyFormGUI $a_form, ilBadge $a_badge, ilBadgeType $a_type)
    {
        $a_form->getItemByPostVar("act")->setChecked($a_badge->isActive());
        $a_form->getItemByPostVar("title")->setValue($a_badge->getTitle());
        $a_form->getItemByPostVar("desc")->setValue($a_badge->getDescription());
        $a_form->getItemByPostVar("crit")->setValue($a_badge->getCriteria());
        $a_form->getItemByPostVar("img")->setValue($a_badge->getImage());
        $a_form->getItemByPostVar("img")->setImage($a_badge->getImagePath());
        $a_form->getItemByPostVar("valid")->setValue($a_badge->getValid());
        
        $custom = $a_type->getConfigGUIInstance();
        if ($custom &&
            $custom instanceof ilBadgeTypeGUI) {
            $custom->importConfigToForm($a_form, $a_badge->getConfiguration());
        }
    }
    
    protected function updateBadge()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_id = $_REQUEST["bid"];
        if (!$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        $ilCtrl->setParameter($this, "bid", $badge_id);
        
        include_once "./Services/Badge/classes/class.ilBadge.php";
        $badge = new ilBadge($badge_id);
        $type = $badge->getTypeInstance();
        $custom = $type->getConfigGUIInstance();
        if ($custom &&
            !($custom instanceof ilBadgeTypeGUI)) {
            $custom = null;
        }
        $form = $this->initBadgeForm("update", $type, $badge->getTypeId());
        if ($form->checkInput() &&
            (!$custom || $custom->validateForm($form))) {
            $badge->setActive($form->getInput("act"));
            $badge->setTitle($form->getInput("title"));
            $badge->setDescription($form->getInput("desc"));
            $badge->setCriteria($form->getInput("crit"));
            $badge->setValid($form->getInput("valid"));
                                
            if ($custom) {
                $badge->setConfiguration($custom->getConfigFromForm($form));
            }
                        
            $badge->update();
            
            $badge->uploadImage($_FILES["img"]);
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "listBadges");
        }
        
        ilUtil::sendFailure($lng->txt("form_input_not_valid"));
        $form->setValuesByPost();
        $this->editBadge($form);
    }
    
    protected function confirmDeleteBadges()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
                                
        $badge_ids = $this->getBadgesFromMultiAction();
                
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listBadges")
        );
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("badge_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listBadges");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteBadges");
            
        include_once "Services/Badge/classes/class.ilBadge.php";
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $confirmation_gui->addItem("id[]", $badge_id, $badge->getTitle() .
                " (" . sizeof(ilBadgeAssignment::getInstancesByBadgeId($badge_id)) . ")");
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function deleteBadges()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_ids = $this->getBadgesFromMultiAction();
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->delete();
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listBadges");
    }

    
    //
    // badges multi action
    //
            
    protected function getBadgesFromMultiAction()
    {
        $ilCtrl = $this->ctrl;
        
        $badge_ids = $_REQUEST["id"];
        if (!$badge_ids ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        return $badge_ids;
    }
    
    protected function copyBadges()
    {
        $ilCtrl = $this->ctrl;
        
        $badge_ids = $this->getBadgesFromMultiAction();

        $_SESSION[self::CLIPBOARD_ID] = array_unique(
            array_merge((array) $_SESSION[self::CLIPBOARD_ID], $badge_ids)
        );
        
        $ilCtrl->redirect($this, "listBadges");
    }
    
    protected function clearClipboard()
    {
        $ilCtrl = $this->ctrl;
        
        unset($_SESSION[self::CLIPBOARD_ID]);
        $ilCtrl->redirect($this, "listBadges");
    }
    
    protected function getValidBadgesFromClipboard()
    {
        $res = array();
        
        $valid_types = array_keys(ilBadgeHandler::getInstance()->getAvailableTypesForObjType($this->parent_obj_type));
            
        include_once "Services/Badge/classes/class.ilBadge.php";
        foreach ($_SESSION[self::CLIPBOARD_ID] as $badge_id) {
            $badge = new ilBadge($badge_id);
            if (in_array($badge->getTypeId(), $valid_types)) {
                $res[] = $badge;
            }
        }
        
        return $res;
    }
    
    protected function pasteBadges()
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->hasWrite() ||
            !is_array($_SESSION[self::CLIPBOARD_ID])) {
            $ilCtrl->redirect($this, "listBadges");
        }
        
        foreach ($this->getValidBadgesFromClipboard() as $badge) {
            $badge->copy($this->parent_obj_id);
        }
        
        $ilCtrl->redirect($this, "listBadges");
    }
        
    protected function toggleBadges($a_status)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $badge_ids = $this->getBadgesFromMultiAction();
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->setActive($a_status);
            $badge->update();
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listBadges");
    }
    
    protected function activateBadges()
    {
        $this->toggleBadges(true);
    }
    
    protected function deactivateBadges()
    {
        $this->toggleBadges(false);
    }

    
    //
    // users
    //
    
    protected function listUsers()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        
        $this->setTabs("users");
        
        if ($this->hasWrite()) {
            $manual = ilBadgeHandler::getInstance()->getAvailableManualBadges($this->parent_obj_id, $this->parent_obj_type);
            if (sizeof($manual)) {
                include_once "Services/Form/classes/class.ilSelectInputGUI.php";
                $drop = new ilSelectInputGUI($lng->txt("badge_badge"), "bid");
                $drop->setOptions($manual);
                $ilToolbar->addInputItem($drop, true);

                $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "awardBadgeUserSelection"));
                $ilToolbar->addFormButton($lng->txt("badge_award_badge"), "awardBadgeUserSelection");
            }
        }
        
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "listUsers", $this->parent_ref_id);
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyListUsers()
    {
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "listUsers", $this->parent_ref_id);
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listUsers();
    }
    
    protected function resetListUsers()
    {
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "listUsers", $this->parent_ref_id);
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listUsers();
    }
    
    protected function awardBadgeUserSelection()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $bid = (int) $_REQUEST["bid"];
        if (!$bid ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listUsers");
        }
        
        $manual = array_keys(ilBadgeHandler::getInstance()->getAvailableManualBadges($this->parent_obj_id, $this->parent_obj_type));
        if (!in_array($bid, $manual)) {
            $ilCtrl->redirect($this, "listUsers");
        }
        
        $back_target = "listUsers";
        if ($_REQUEST["tgt"] == "bdgl") {
            $ilCtrl->saveParameter($this, "tgt");
            $back_target = "listBadges";
        }
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, $back_target)
        );
        
        $ilCtrl->setParameter($this, "bid", $bid);
        
        include_once "./Services/Badge/classes/class.ilBadge.php";
        $badge = new ilBadge($bid);
        
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "awardBadgeUserSelection", $this->parent_ref_id, $badge);
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function applyAwardBadgeUserSelection()
    {
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "awardBadgeUserSelection", $this->parent_ref_id);
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->awardBadgeUserSelection();
    }
    
    protected function resetAwardBadgeUserSelection()
    {
        include_once "Services/Badge/classes/class.ilBadgeUserTableGUI.php";
        $tbl = new ilBadgeUserTableGUI($this, "awardBadgeUserSelection", $this->parent_ref_id);
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->awardBadgeUserSelection();
    }
    
    protected function assignBadge()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        
        $user_ids = $_POST["id"];
        $badge_id = $_REQUEST["bid"];
        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listUsers");
        }
        
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        $new_badges = array();
        foreach ($user_ids as $user_id) {
            if (!ilBadgeAssignment::exists($badge_id, $user_id)) {
                $ass = new ilBadgeAssignment($badge_id, $user_id);
                $ass->setAwardedBy($ilUser->getId());
                $ass->store();
                
                $new_badges[$user_id][] = $badge_id;
            }
        }
        
        ilBadgeHandler::getInstance()->sendNotification($new_badges, $this->parent_ref_id);
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listUsers");
    }
    
    protected function confirmDeassignBadge()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
                        
        $user_ids = $_POST["id"];
        $badge_id = $_REQUEST["bid"];
        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listUsers");
        }
                
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listUsers")
        );
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        $badge = new ilBadge($badge_id);
        
        $ilCtrl->setParameter($this, "bid", $badge->getId());
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText(sprintf($lng->txt("badge_assignment_deletion_confirmation"), $badge->getTitle()));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listUsers");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deassignBadge");
            
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        $assigned_users = ilBadgeAssignment::getAssignedUsers($badge->getId());
        
        include_once "Services/User/classes/class.ilUserUtil.php";
        foreach ($user_ids as $user_id) {
            if (in_array($user_id, $assigned_users)) {
                $confirmation_gui->addItem(
                    "id[]",
                    $user_id,
                    ilUserUtil::getNamePresentation($user_id, false, false, "", true)
                );
            }
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function deassignBadge()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $user_ids = $_POST["id"];
        $badge_id = $_REQUEST["bid"];
        if (!$user_ids ||
            !$badge_id ||
            !$this->hasWrite()) {
            $ilCtrl->redirect($this, "listUsers");
        }
        
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        foreach ($user_ids as $user_id) {
            $ass = new ilBadgeAssignment($badge_id, $user_id);
            $ass->delete();
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listUsers");
    }
}
