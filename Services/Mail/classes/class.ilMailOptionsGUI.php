<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Jens Conze
 * @version $Id$
 *
 * @ingroup ServicesMail
 */
class ilMailOptionsGUI
{
    /** @var ilGlobalPageTemplate */
    private $tpl;

    /** @var ilCtrl */
    private $ctrl;

    /** @var ilLanguage */
    private $lng;

    /** @var ilSetting */
    private $settings;

    /** @var ilObjUser */
    private $user;

    /** @var ilFormatMail */
    private $umail;

    /** @var ilMailbox */
    private $mbox;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var ilMailOptionsFormGUI */
    protected $form;
    /** @var ilMailOptions */
    protected $mail_options;

    /**
     * ilMailOptionsGUI constructor.
     * @param ilGlobalPageTemplate|null $tpl
     * @param ilCtrl|null $ctrl
     * @param ilLanguage|null $lng
     * @param ilObjUser|null $user
     * @param ServerRequestInterface|null $request
     * @param ilFormatMail|null $mail
     * @param ilMailbox|null $malBox
     */
    public function __construct(
        ilGlobalPageTemplate $tpl = null,
        ilCtrl $ctrl = null,
        ilLanguage $lng = null,
        ilObjUser $user = null,
        ServerRequestInterface $request = null,
        ilFormatMail $mail = null,
        ilMailbox $malBox = null,
        ilMailOptions $mail_options = null
    ) {
        global $DIC;

        $this->tpl = $tpl;
        if (null === $this->tpl) {
            $this->tpl = $DIC->ui()->mainTemplate();
        }

        $this->ctrl = $ctrl;
        if (null === $this->ctrl) {
            $this->ctrl = $DIC->ctrl();
        }

        $this->lng = $lng;
        if (null === $this->lng) {
            $this->lng = $DIC->language();
        }

        $this->user = $user;
        if (null === $this->user) {
            $this->user = $DIC->user();
        }

        $this->request = $request;
        if (null === $this->request) {
            $this->request = $DIC->http()->request();
        }

        $this->umail = $mail;
        if (null === $this->umail) {
            $this->umail = new ilFormatMail($this->user->getId());
        }

        $this->mbox = $malBox;
        if (null === $this->mbox) {
            $this->mbox = new ilMailbox($this->user->getId());
        }
        $this->mail_options = $mail_options ?? new ilMailOptions((int) $this->user->getId());

        $this->lng->loadLanguageModule('mail');
        $this->ctrl->saveParameter($this, 'mobj_id');
    }

    public function executeCommand() : void
    {
        if (!$this->mail_options->mayManageInvididualSettings()) {
            $referrer = $this->request->getQueryParams()['referrer'] ?? '';
            if (strtolower('ilPersonalSettingsGUI') === strtolower($referrer)) {
                $this->ctrl->redirectByClass('ilPersonalSettingsGUI');
                return;
            }
            $this->ctrl->redirectByClass('ilMailGUI');
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

    /**
     * @param ilMailOptionsFormGUI $form
     */
    public function setForm(ilMailOptionsFormGUI $form) : void
    {
        $this->form = $form;
    }

    /**
     * @return ilMailOptionsFormGUI
     */
    protected function getForm() : ilMailOptionsFormGUI
    {
        if (null !== $this->form) {
            return $this->form;
        }

        return new ilMailOptionsFormGUI(
            $this->mail_options,
            $this,
            'saveOptions'
        );
    }

    /**
     * Called if the user pushes the submit button of the mail options form.
     * Passes the post data to the mail options model instance to store them.
     */
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

    /**
     * Called to display the mail options form
     * @param $form ilMailOptionsFormGUI|null
     */
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
