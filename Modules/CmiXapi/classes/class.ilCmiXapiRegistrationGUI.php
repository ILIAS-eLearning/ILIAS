<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected ilObjCmiXapi $object;
    
    /**
     * @var ilCmiXapiUser
     */
    protected ilCmiXapiUser $cmixUser;
    
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

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        switch ($DIC->ctrl()->getNextClass()) {
            default:
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    protected function cancelCmd() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
    }

    /**
     * @param ilPropertyFormGUI|null $form
     * @return void
     */
    protected function showFormCmd(ilPropertyFormGUI $form = null) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($form === null) {
            $form = $this->buildForm();
        }
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    protected function saveFormCmd() : void
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
     * @throws ilCtrlException
     */
    protected function buildForm() : \ilPropertyFormGUI
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

    /**
     * @return int
     */
    protected function hasRegistration() : int
    {
        return strlen($this->cmixUser->getUsrIdent());
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return void
     */
    protected function saveRegistration(ilPropertyFormGUI $form) : void
    {
        $this->cmixUser->setUsrIdent($form->getInput('user_ident'));
        $this->cmixUser->save();
    }
}
