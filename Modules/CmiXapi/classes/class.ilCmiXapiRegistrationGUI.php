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

    protected ilObjCmiXapi $object;
    
    protected ilCmiXapiUser $cmixUser;
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\DI\Container $dic;
    
    /**
     * ilCmiXapiRegistrationGUI constructor.
     */
    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        
        $this->object = $object;
        
        $this->cmixUser = new ilCmiXapiUser($object->getId(), $DIC->user()->getId(), $object->getPrivacyIdent());
    }

    /**
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
     * @throws ilCtrlException
     */
    protected function cancelCmd() : void
    {
        $this->dic->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    protected function showFormCmd(ilPropertyFormGUI $form = null) : void
    {
        if ($form === null) {
            $form = $this->buildForm();
        }
        
        $this->main_tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    protected function saveFormCmd() : void
    {
        $form = $this->buildForm();
    
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showFormCmd($form);
            return;
        }
        
        $this->saveRegistration($form);
        
        $this->main_tpl->setOnScreenMessage('success', $this->dic->language()->txt('registration_saved_successfully'), true);
        $this->dic->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
    }

    /**
     * @throws ilCtrlException
     */
    protected function buildForm() : \ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        
        $form->setFormAction($this->dic->ctrl()->getFormAction($this, self::CMD_SHOW_FORM));
        
        if (!$this->hasRegistration()) {
            $form->setTitle($this->dic->language()->txt('form_create_registration'));
            $form->addCommandButton(self::CMD_SAVE_FORM, $this->dic->language()->txt('btn_create_registration'));
        } else {
            $form->setTitle($this->dic->language()->txt('form_change_registration'));
            $form->addCommandButton(self::CMD_SAVE_FORM, $this->dic->language()->txt('btn_change_registration'));
        }
        
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt('cancel'));
        
        $userIdent = new ilEMailInputGUI($this->dic->language()->txt('field_user_ident'), 'user_ident');
        $userIdent->setInfo($this->dic->language()->txt('field_user_ident_info'));
        $userIdent->setRequired(true);
        $userIdent->setValue($this->cmixUser->getUsrIdent());
        $form->addItem($userIdent);
        
        return $form;
    }

    protected function hasRegistration() : int
    {
        return strlen($this->cmixUser->getUsrIdent());
    }

    protected function saveRegistration(ilPropertyFormGUI $form) : void
    {
        $this->cmixUser->setUsrIdent($form->getInput('user_ident'));
        $this->cmixUser->save();
    }
}
