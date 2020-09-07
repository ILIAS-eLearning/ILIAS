<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteriaCatalogue.php";

/**
 * Class ilExcCriteriaCatalogueGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilExcCriteriaCatalogueGUI: ilExcCriteriaGUI
 * @ingroup ModulesExercise
 */
class ilExcCriteriaCatalogueGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $exc_obj; // [ilObjExercise]
    
    public function __construct(ilObjExercise $a_exc_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->exc_obj = $a_exc_obj;
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("view");
    
        switch ($next_class) {
            case "ilexccriteriagui":
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, ""));
                $ilCtrl->saveParameter($this, "cat_id");
                include_once "Modules/Exercise/classes/class.ilExcCriteriaGUI.php";
                $crit_gui = new ilExcCriteriaGUI($_REQUEST["cat_id"]);
                $ilCtrl->forwardCommand($crit_gui);
                break;
            
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
        
        $ilToolbar->addButton(
            $lng->txt("exc_add_criteria_catalogue"),
            $ilCtrl->getLinkTarget($this, "add")
        );
        
        include_once "Modules/Exercise/classes/class.ilExcCriteriaCatalogueTableGUI.php";
        $tbl = new ilExcCriteriaCatalogueTableGUI($this, "view", $this->exc_obj->getId());
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function saveOrder()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $all_cat = ilExcCriteriaCatalogue::getInstancesByParentId($this->exc_obj->getId());
                
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
        $confirmation_gui->setHeaderText($lng->txt("exc_criteria_catalogue_deletion_confirmation"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "view");
        $confirmation_gui->setConfirm($lng->txt("delete"), "delete");

        foreach (ilExcCriteriaCatalogue::getInstancesByParentId($this->exc_obj->getId()) as $item) {
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
        
        foreach (ilExcCriteriaCatalogue::getInstancesByParentId($this->exc_obj->getId()) as $item) {
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
    
    protected function initForm(ilExcCriteriaCatalogue $a_cat_obj = null)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        
        $is_edit = ($a_cat_obj !== null);
        if (!$is_edit) {
            $form->setFormAction($ilCtrl->getFormAction($this, "create"));
            $form->setTitle($lng->txt("exc_criteria_catalogue_create_form"));
            $form->addCommandButton("create", $lng->txt("create"));
        } else {
            $form->setFormAction($ilCtrl->getFormAction($this, "update"));
            $form->setTitle($lng->txt("exc_criteria_catalogue_update_form"));
            $form->addCommandButton("update", $lng->txt("save"));
        }
                
        $form->addCommandButton("view", $lng->txt("cancel"));
                
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        return $form;
    }
    
    protected function add(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        
        if (!$a_form) {
            $a_form = $this->initForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function exportForm(ilExcCriteriaCatalogue $a_cat_obj, ilPropertyFormGUI $a_form)
    {
        $a_form->getItemByPostVar("title")->setValue($a_cat_obj->getTitle());
    }
    
    protected function importForm(ilExcCriteriaCatalogue $a_cat_obj = null)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $is_edit = ($a_cat_obj !== null);
        
        $form = $this->initForm($a_cat_obj);
        if ($form->checkInput()) {
            if (!$is_edit) {
                $a_cat_obj = new ilExcCriteriaCatalogue();
                $a_cat_obj->setParent($this->exc_obj->getId());
            }
            
            $a_cat_obj->setTitle($form->getInput("title"));
            
            if (!$is_edit) {
                $a_cat_obj->save();
            } else {
                $a_cat_obj->update();
            }
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "view");
        }
        
        $form->setValuesByPost();
        $this->{$is_edit ? "edit" : "add"}($form);
    }
    
    protected function create()
    {
        $this->importForm();
    }
    
    protected function getCurrentCatalogue()
    {
        $ilCtrl = $this->ctrl;
        
        $id = (int) $_REQUEST["cat_id"];
        if ($id) {
            $cat_obj = new ilExcCriteriaCatalogue($id);
            if ($cat_obj->getParent() == $this->exc_obj->getId()) {
                $ilCtrl->setParameter($this, "cat_id", $id);
                return $cat_obj;
            }
        }
        
        $ilCtrl->redirect($this, "view");
    }
    
    protected function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        
        $cat_obj = $this->getCurrentCatalogue();
        
        if (!$a_form) {
            $a_form = $this->initForm($cat_obj);
            $this->exportForm($cat_obj, $a_form);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function update()
    {
        $cat_obj = $this->getCurrentCatalogue();
        $this->importForm($cat_obj);
    }
}
