<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Jens Conze
 * @version $Id$
 *
 * @ingroup ServicesMail
 */
class ilMailOptionsGUI
{
    private ilGlobalTemplateInterface $tpl;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilObjUser $user;
    private ilFormatMail $umail;
    private ilMailbox $mbox;
    protected ServerRequestInterface $request;
    protected ilMailOptionsFormGUI $form;


    public function __construct(
        ilGlobalTemplateInterface $tpl = null,
        ilCtrl $ctrl = null,
        ilSetting $setting = null,
        ilLanguage $lng = null,
        ilObjUser $user = null,
        ServerRequestInterface $request = null,
        ilFormatMail $mail = null,
        ilMailbox $malBox = null
    ) {
        global $DIC;

        if (null === $tpl) {
            $tpl = $DIC->ui()->mainTemplate();
        }
        $this->tpl = $tpl;

        if (null === $ctrl) {
            $ctrl = $DIC->ctrl();
        }
        $this->ctrl = $ctrl;

        if (null === $setting) {
            $setting = $DIC->settings();
        }
        $this->settings = $setting;

        if (null === $lng) {
            $lng = $DIC->language();
        }
        $this->lng = $lng;

        if (null === $user) {
            $user = $DIC->user();
        }
        $this->user = $user;

        if (null === $request) {
            $request = $DIC->http()->request();
        }
        $this->request = $request;

        if (null === $mail) {
            $mail = new ilFormatMail($this->user->getId());
        }
        $this->umail = $mail;

        if (null === $malBox) {
            $malBox = new ilMailbox($this->user->getId());
        }
        $this->mbox = $malBox;

        $this->lng->loadLanguageModule('mail');
        $this->ctrl->saveParameter($this, 'mobj_id');
    }

    public function executeCommand() : void
    {
        if (!$this->settings->get('show_mail_settings')) {
            $referrer = $this->request->getQueryParams()['referrer'] ?? '';
            if (strtolower(ilPersonalSettingsGUI::class) === strtolower($referrer)) {
                $this->ctrl->redirectByClass(ilPersonalSettingsGUI::class);
                return;
            }
            $this->ctrl->redirectByClass(ilMailGUI::class);
            return;
        }

        $nextClass = $this->ctrl->getNextClass($this);
        switch ($nextClass) {
            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = 'showOptions';
                }

                $this->$cmd();
                break;
        }
    }

    
    public function setForm(ilMailOptionsFormGUI $form) : void
    {
        $this->form = $form;
    }

    
    protected function getForm() : ilMailOptionsFormGUI
    {
        return $this->form ?? new ilMailOptionsFormGUI(
            new ilMailOptions($this->user->getId()),
            $this,
            'saveOptions'
        );
    }


    protected function saveOptions() : void
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        $form = $this->getForm();
        if ($form->save()) {
            ilUtil::sendSuccess($this->lng->txt('mail_options_saved'), true);
            $this->ctrl->redirect($this, 'showOptions');
        }

        $this->showOptions($form);
    }


    protected function showOptions(ilMailOptionsFormGUI $form = null) : void
    {
        if (null === $form) {
            $form = $this->getForm();
            $form->populate();
        } else {
            $form->setValuesByPost();
        }

        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }
}
