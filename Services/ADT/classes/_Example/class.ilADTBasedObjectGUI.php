<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT based-object GUI base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTBasedObjectGUI
{
    protected $object; // [ilADTBasedObject]
    
    /**
     * Constructor
     *
     * Parent GUI is just needed for testing (ilCtrl)
     *
     * @param ilObjectGUI $a_parent_gui
     * @return self
     */
    public function __construct(ilObjectGUI $a_parent_gui)
    {
        $this->gui = $a_parent_gui;
        $this->object = $this->initObject();
    }
    
    /**
     * Init ADT-based object
     */
    abstract protected function initObject();
    
    
    //
    // VERY BASIC EXAMPLE OF FORM HANDLING
    //
    
    /**
     * Edit object ADT properties
     *
     * @param ilADTGroupFormBridge $a_form
     */
    public function editAction(ilADTGroupFormBridge $a_form = null)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        if (!$a_form) {
            $a_form = $this->initForm();
        }
                
        $tpl->setContent($a_form->getForm()->getHTML());
    }
    
    /**
     * Prepare/customize form elements
     *
     * @param ilADTGroupFormBridge $a_adt_form
     */
    abstract protected function prepareFormElements(ilADTGroupFormBridge $a_adt_form);
    
    /**
     * Init ADT-based form
     *
     * @return ilADTGroupFormBridge $a_form
     */
    protected function initForm()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this->gui, "updateAction"));
        
        $adt_form = ilADTFactory::getInstance()->getFormBridgeForInstance($this->object->getProperties());
        
        // has to be done BEFORE prepareFormElements() ...
        $adt_form->setForm($form);
            
        $this->prepareFormElements($adt_form);
        
        $adt_form->addToForm();
        $adt_form->addJS($tpl);
        
        // :TODO:
        $form->addCommandButton("updateAction", $lng->txt("save"));
                
        return $adt_form;
    }
    
    /**
     * Parse incoming values and update if valid
     */
    public function updateAction()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $adt_form = $this->initForm();
        $valid = $adt_form->getForm()->checkInput(); // :TODO: return value is obsolete
        
        $old_chksum = $this->object->getProperties()->getCheckSum();
        
        $adt_form->importFromPost();
        $valid = $adt_form->validate();
        
        $changed = ($old_chksum != $this->object->getProperties()->getCheckSum());
        
        // validation errors have top priority
        if (!$valid) {
            ilUtil::sendFailure($lng->txt("form_input_not_valid"));
            return $this->editAction($adt_form);
        }
                
        // :TODO: experimental, update only if necessary
        if ($changed) {
            if ($this->object->update()) {
                ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            } else {
                // error occured in db-layer (primary/unique)
                foreach ($this->object->getDBErrors() as $element_id => $codes) {
                    $element = $adt_form->getElement($element_id);
                    if ($element) {
                        $element->setExternalErrors($this->object->translateDBErrorCodes($codes));
                    }
                }
                
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
                return $this->editAction($adt_form);
            }
        }
        
        $ilCtrl->redirect($this->gui, "edit");
    }
}
