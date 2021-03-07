<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiRegistrationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiRegistrationGUI
{
    const CMD_SHOW_FORM = 'showForm';
    const CMD_SAVE_FORM = 'saveForm';
    const CMD_CANCEL = 'cancel';
    
    const DEFAULT_CMD = self::CMD_SHOW_FORM;
    
    /**
     * @var ilObjCmiXapi
     */
    protected $object;
    
    /**
     * @var ilCmiXapiUser
     */
    protected $cmixUser;
    
    /**
     * ilCmiXapiRegistrationGUI constructor.
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->object = $object;
        
        $this->cmixUser = new ilCmiXapiUser($object->getId(), $DIC->user()->getId(), $object->getPrivacyIdent());
    }
    
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        switch ($DIC->ctrl()->getNextClass()) {
            default:
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function cancelCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
    }

    protected function showFormCmd(ilPropertyFormGUI $form = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($form === null) {
            $form = $this->buildForm();
        }
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function saveFormCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = $this->buildForm();
    
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showFormCmd($form);
            return;
        }
        
        $this->saveRegistration($form);
        
        ilUtil::sendSuccess($DIC->language()->txt('registration_saved_successfully'), true);
        $DIC->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
    }
    
    /**
     * @return ilPropertyFormGUI
     */
    protected function buildForm()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = new ilPropertyFormGUI();
        
        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SHOW_FORM));
        
        if (!$this->hasRegistration()) {
            $form->setTitle($DIC->language()->txt('form_create_registration'));
            $form->addCommandButton(self::CMD_SAVE_FORM, $DIC->language()->txt('btn_create_registration'));
        } else {
            $form->setTitle($DIC->language()->txt('form_change_registration'));
            $form->addCommandButton(self::CMD_SAVE_FORM, $DIC->language()->txt('btn_change_registration'));
        }
        
        $form->addCommandButton(self::CMD_CANCEL, $DIC->language()->txt('cancel'));
        
        $userIdent = new ilEMailInputGUI($DIC->language()->txt('field_user_ident'), 'user_ident');
        $userIdent->setInfo($DIC->language()->txt('field_user_ident_info'));
        $userIdent->setRequired(true);
        $userIdent->setValue($this->cmixUser->getUsrIdent());
        $form->addItem($userIdent);
        
        return $form;
    }
    
    protected function hasRegistration()
    {
        return strlen($this->cmixUser->getUsrIdent());
    }
    
    protected function saveRegistration(ilPropertyFormGUI $form)
    {
        $this->cmixUser->setUsrIdent($form->getInput('user_ident'));
        $this->cmixUser->save();
    }
}
