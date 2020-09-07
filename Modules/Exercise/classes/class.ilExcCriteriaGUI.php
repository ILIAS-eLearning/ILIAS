<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilExcCriteriaGUI:
 * @ingroup ModulesExercise
 */
class ilExcCriteriaGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $cat_id; // [int]
    
    public function __construct($a_cat_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->cat_id = $a_cat_id;
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("view");
    
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }
    
    
    //
    // LIST/TABLE
    //
    
    protected function view()
    {
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
    
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "add"));
            
        include_once "Services/Form/classes/class.ilSelectInputGUI.php";
        $types = new ilSelectInputGUI($lng->txt("type"), "type");
        $types->setOptions(ilExcCriteria::getTypesMap());
        $ilToolbar->addStickyItem($types);
        
        include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
        $button = ilSubmitButton::getInstance();
        $button->setCaption("exc_add_criteria");
        $button->setCommand("add");
        $ilToolbar->addStickyItem($button);
        
        include_once "Modules/Exercise/classes/class.ilExcCriteriaTableGUI.php";
        $tbl = new ilExcCriteriaTableGUI($this, "view", $this->cat_id);
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function saveOrder()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $all_cat = ilExcCriteria::getInstancesByParentId($this->cat_id);
                
        $pos = 0;
        asort($_POST["pos"]);
        foreach (array_keys($_POST["pos"]) as $id) {
            if (array_key_exists($id, $all_cat)) {
                $pos += 10;
                $all_cat[$id]->setPosition($pos);
                $all_cat[$id]->update();
            }
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "view");
    }
    
    protected function confirmDeletion()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $ids = $_POST["id"];
        if (!sizeof($ids)) {
            ilUtil::sendInfo($lng->txt("select_one"), true);
            $ilCtrl->redirect($this, "view");
        }
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this, "delete"));
        $confirmation_gui->setHeaderText($lng->txt("exc_criteria_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "view");
        $confirmation_gui->setConfirm($lng->txt("delete"), "delete");

        foreach (ilExcCriteria::getInstancesByParentId($this->cat_id) as $item) {
            if (in_array($item->getId(), $ids)) {
                $confirmation_gui->addItem("id[]", $item->getId(), $item->getTitle());
            }
        }
        
        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    protected function delete()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $ids = $_POST["id"];
        if (!sizeof($ids)) {
            $ilCtrl->redirect($this, "view");
        }
        
        foreach (ilExcCriteria::getInstancesByParentId($this->cat_id) as $item) {
            if (in_array($item->getId(), $ids)) {
                $item->delete();
            }
        }
        
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "view");
    }
    
    
    //
    // EDIT
    //
    
    protected function initForm(ilExcCriteria $a_crit_obj)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        
        $is_edit = (bool) $a_crit_obj->getId();
        if (!$is_edit) {
            $form->setFormAction($ilCtrl->getFormAction($this, "create"));
            $form->setTitle($lng->txt("exc_criteria_create_form"));
            $form->addCommandButton("create", $lng->txt("create"));
        } else {
            $form->setFormAction($ilCtrl->getFormAction($this, "update"));
            $form->setTitle($lng->txt("exc_criteria_update_form"));
            $form->addCommandButton("update", $lng->txt("save"));
        }
                
        $form->addCommandButton("view", $lng->txt("cancel"));
        
        $type = new ilNonEditableValueGUI($lng->txt("type"));
        $type->setValue($a_crit_obj->getTranslatedType());
        $form->addItem($type);
                
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        $desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
        $form->addItem($desc);
        
        $req = new ilCheckboxInputGUI($lng->txt("required_field"), "req");
        $form->addItem($req);
        
        $a_crit_obj->initCustomForm($form);
        
        return $form;
    }
    
    protected function add(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        $new_type = trim($_REQUEST["type"]);
        if (!$new_type) {
            $ilCtrl->redirect($this, "view");
        }
        
        $ilCtrl->setParameter($this, "type", $new_type);
        
        if (!$a_form) {
            $crit_obj = ilExcCriteria::getInstanceByType($new_type);
            $a_form = $this->initForm($crit_obj);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function exportForm(ilExcCriteria $a_crit_obj, ilPropertyFormGUI $a_form)
    {
        $a_form->getItemByPostVar("title")->setValue($a_crit_obj->getTitle());
        $a_form->getItemByPostVar("desc")->setValue($a_crit_obj->getDescription());
        $a_form->getItemByPostVar("req")->setChecked($a_crit_obj->isRequired());
        
        $a_crit_obj->exportCustomForm($a_form);
    }
    
    protected function importForm(ilExcCriteria $a_crit_obj)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $is_edit = (bool) $a_crit_obj->getId();
        
        $form = $this->initForm($a_crit_obj);
        if ($form->checkInput()) {
            $a_crit_obj->setTitle($form->getInput("title"));
            $a_crit_obj->setDescription($form->getInput("desc"));
            $a_crit_obj->setRequired($form->getInput("req"));
            
            $a_crit_obj->importCustomForm($form);
            
            if (!$is_edit) {
                $a_crit_obj->setParent($this->cat_id);
                $a_crit_obj->save();
            } else {
                $a_crit_obj->update();
            }
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "view");
        }
        
        $form->setValuesByPost();
        $this->{$is_edit ? "edit" : "add"}($form);
    }
    
    protected function create()
    {
        $ilCtrl = $this->ctrl;
        
        $new_type = trim($_REQUEST["type"]);
        if (!$new_type) {
            $ilCtrl->redirect($this, "view");
        }
        
        $crit_obj = ilExcCriteria::getInstanceByType($new_type);
        $this->importForm($crit_obj);
    }
    
    protected function getCurrentCritera()
    {
        $ilCtrl = $this->ctrl;
        
        $id = (int) $_REQUEST["crit_id"];
        if ($id) {
            $crit_obj = ilExcCriteria::getInstanceById($id);
            if ($crit_obj->getParent() == $this->cat_id) {
                $ilCtrl->setParameter($this, "crit_id", $id);
                return $crit_obj;
            }
        }
        
        $ilCtrl->redirect($this, "view");
    }
    
    protected function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        
        $crit_obj = $this->getCurrentCritera();
        
        if (!$a_form) {
            $a_form = $this->initForm($crit_obj);
            $this->exportForm($crit_obj, $a_form);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function update()
    {
        $crit_obj = $this->getCurrentCritera();
        $this->importForm($crit_obj);
    }
}
