<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Jens Conze
 * @ingroup ServicesMail
 */
class ilMailOptionsGUI
{
    private ilGlobalTemplateInterface $tpl;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilObjUser $user;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilMailOptionsFormGUI $form;

    public function __construct(
        ilGlobalTemplateInterface $tpl = null,
        ilCtrl $ctrl = null,
        ilSetting $setting = null,
        ilLanguage $lng = null,
        ilObjUser $user = null,
        GlobalHttpState $http = null,
        ilFormatMail $mail = null,
        ilMailbox $malBox = null,
        Refinery $refinery = null
    ) {
        global $DIC;
        $this->tpl = $tpl ?? $DIC->ui()->mainTemplate();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
        $this->settings = $setting ?? $DIC->settings();
        $this->lng = $lng ?? $DIC->language();
        $this->user = $user ?? $DIC->user();
        $this->http = $http ?? $DIC->http();
        $this->refinery = $refinery ?? $DIC->refinery();

        $this->lng->loadLanguageModule('mail');
        $this->ctrl->saveParameter($this, 'mobj_id');
    }

    public function executeCommand() : void
    {
        if (!$this->settings->get('show_mail_settings', '0')) {
            $referrer = '';
            if ($this->http->wrapper()->query()->has('referrer')) {
                $referrer = $this->http->wrapper()->query()->retrieve(
                    'referrer',
                    $this->refinery->kindlyTo()->string()
                );
            }
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
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_options_saved'), true);
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
