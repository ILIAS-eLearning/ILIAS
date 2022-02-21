<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT based-object GUI base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTBasedObjectGUI
{
    private ilObjectGUI $gui;
    protected ?ilADTBasedObject $object = null;

    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    /**
     * Constructor
     * Parent GUI is just needed for testing (ilCtrl)
     * @param ilObjectGUI $a_parent_gui
     */
    public function __construct(ilObjectGUI $a_parent_gui)
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->gui = $a_parent_gui;
        $this->object = $this->initObject();
    }

    /**
     * Init ADT-based object
     */
    abstract protected function initObject() : ilADTBasedObject;


    //
    // VERY BASIC EXAMPLE OF FORM HANDLING
    //

    public function editAction(ilADTGroupFormBridge $a_form = null) : bool
    {
        if (!$a_form) {
            $a_form = $this->initForm();
        }

        $this->tpl->setContent($a_form->getForm()->getHTML());
        return true;
    }

    /**
     * Prepare/customize form elements
     * @param ilADTGroupFormBridge $a_adt_form
     */
    abstract protected function prepareFormElements(ilADTGroupFormBridge $a_adt_form) : void;

    /**
     * Init ADT-based form
     * @return ilADTFormBridge $a_form
     */
    protected function initForm() : ilADTFormBridge
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this->gui, "updateAction"));

        $adt_form = ilADTFactory::getInstance()->getFormBridgeForInstance($this->object->getProperties());

        // has to be done BEFORE prepareFormElements() ...
        $adt_form->setForm($form);

        /** @noinspection PhpParamsInspection */
        $this->prepareFormElements($adt_form);

        $adt_form->addToForm();
        $adt_form->addJS($this->tpl);

        // :TODO:
        $form->addCommandButton("updateAction", $this->lng->txt("save"));

        return $adt_form;
    }

    /**
     * Parse incoming values and update if valid
     * @noinspection PhpParamsInspection
     */
    public function updateAction() : bool
    {
        $adt_form = $this->initForm();
        $valid = $adt_form->getForm()->checkInput();

        $old_chksum = $this->object->getProperties()->getCheckSum();

        $adt_form->importFromPost();
        $valid = $adt_form->validate();

        $changed = ($old_chksum != $this->object->getProperties()->getCheckSum());

        // validation errors have top priority
        if (!$valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
            return $this->editAction($adt_form);
        }

        // :TODO: experimental, update only if necessary
        if ($changed) {
            if ($this->object->update()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            } else {
                // error occured in db-layer (primary/unique)
                foreach ($this->object->getDBErrors() as $element_id => $codes) {
                    $element = $adt_form->getElement($element_id);
                    if ($element) {
                        $element->setExternalErrors($this->object->translateDBErrorCodes($codes));
                    }
                }

                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
                return $this->editAction($adt_form);
            }
        }

        $this->ctrl->redirect($this->gui, "edit");
        return true;
    }
}
