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
 
use ILIAS\Exercise\GUIRequest;

/**
 * Class ilExcCriteriaCatalogueGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilExcCriteriaCatalogueGUI: ilExcCriteriaGUI
 */
class ilExcCriteriaCatalogueGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjExercise $exc_obj;
    protected GUIRequest $request;
    
    public function __construct(ilObjExercise $a_exc_obj)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->exc_obj = $a_exc_obj;

        $this->request = $DIC->exercise()->internal()->gui()->request();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand() : void
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
                $crit_gui = new ilExcCriteriaGUI($this->request->getCatalogueId());
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
    
    protected function view() : void
    {
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $ilToolbar->addButton(
            $lng->txt("exc_add_criteria_catalogue"),
            $ilCtrl->getLinkTarget($this, "add")
        );
        
        $tbl = new ilExcCriteriaCatalogueTableGUI($this, "view", $this->exc_obj->getId());
        $tpl->setContent($tbl->getHTML());
    }
    
    protected function saveOrder() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $all_cat = ilExcCriteriaCatalogue::getInstancesByParentId($this->exc_obj->getId());
                
        $pos = 0;
        $req_positions = $this->request->getPositions();
        asort($req_positions);
        foreach (array_keys($req_positions) as $id) {
            if (array_key_exists($id, $all_cat)) {
                $pos += 10;
                $all_cat[$id]->setPosition($pos);
                $all_cat[$id]->update();
            }
        }
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "view");
    }
    
    protected function confirmDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $ids = $this->request->getCatalogueIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("select_one"), true);
            $ilCtrl->redirect($this, "view");
        }
        
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
    
    protected function delete() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $ids = $this->request->getCatalogueIds();
        if (count($ids) == 0) {
            $ilCtrl->redirect($this, "view");
        }
        
        foreach (ilExcCriteriaCatalogue::getInstancesByParentId($this->exc_obj->getId()) as $item) {
            if (in_array($item->getId(), $ids)) {
                $item->delete();
            }
        }
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "view");
    }
    
    
    //
    // EDIT
    //
    
    protected function initForm(
        ilExcCriteriaCatalogue $a_cat_obj = null
    ) : ilPropertyFormGUI {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
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
    
    protected function add(ilPropertyFormGUI $a_form = null) : void
    {
        $tpl = $this->tpl;
        
        if (!$a_form) {
            $a_form = $this->initForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function exportForm(
        ilExcCriteriaCatalogue $a_cat_obj,
        ilPropertyFormGUI $a_form
    ) : void {
        $a_form->getItemByPostVar("title")->setValue($a_cat_obj->getTitle());
    }
    
    protected function importForm(
        ilExcCriteriaCatalogue $a_cat_obj = null
    ) : void {
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
            
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "view");
        }
        
        $form->setValuesByPost();
        $this->{$is_edit ? "edit" : "add"}($form);
    }
    
    protected function create() : void
    {
        $this->importForm();
    }
    
    protected function getCurrentCatalogue() : ?ilExcCriteriaCatalogue
    {
        $ilCtrl = $this->ctrl;
        
        $id = $this->request->getCatalogueId();
        if ($id > 0) {
            $cat_obj = new ilExcCriteriaCatalogue($id);
            if ($cat_obj->getParent() == $this->exc_obj->getId()) {
                $ilCtrl->setParameter($this, "cat_id", $id);
                return $cat_obj;
            }
        }
        
        $ilCtrl->redirect($this, "view");

        return null;
    }
    
    protected function edit(ilPropertyFormGUI $a_form = null) : void
    {
        $tpl = $this->tpl;
        
        $cat_obj = $this->getCurrentCatalogue();
        
        if (!$a_form) {
            $a_form = $this->initForm($cat_obj);
            $this->exportForm($cat_obj, $a_form);
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function update() : void
    {
        $cat_obj = $this->getCurrentCatalogue();
        $this->importForm($cat_obj);
    }
}
