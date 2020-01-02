<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jens Conze
 * @version $Id$
 *
 * @ingroup ServicesMail
 */
class ilMailOptionsGUI
{
    /**
     * @var \ilTemplate
     */
    private $tpl;

    /**
     * @var \ilCtrl
     */
    private $ctrl;

    /**
     * @var \ilLanguage
     */
    private $lng;

    /**
     * @var \ilSetting
     */
    private $settings;

    /**
     * @var \ilObjUser
     */
    private $user;

    /**
     * @var \ilFormatMail
     */
    private $umail;

    /**
     * @var ilMailbox|null
     */
    private $mbox;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var \ilMailOptionsFormGUI
     */
    protected $form = null;

    /**
     * ilMailOptionsGUI constructor.
     * @param ilTemplate|null $tpl
     * @param ilCtrl|null $ctrl
     * @param ilSetting|null $setting
     * @param ilLanguage|null $lng
     * @param ilObjUser|null $user
     * @param \Psr\Http\Message\ServerRequestInterface|null $request
     * @param \ilFormatMail|null $mail
     * @param \ilMailBox|null $malBox
     */
    public function __construct(
        \ilTemplate $tpl = null,
        \ilCtrl $ctrl = null,
        \ilSetting $setting = null,
        \ilLanguage $lng = null,
        \ilObjUser $user = null,
        \Psr\Http\Message\ServerRequestInterface $request = null,
        \ilFormatMail $mail = null,
        \ilMailbox $malBox = null
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

        $this->settings = $setting;
        if (null === $this->settings) {
            $this->settings = $DIC->settings();
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
            $this->umail = new \ilFormatMail($this->user->getId());
        }

        $this->mbox = $malBox;
        if (null === $this->mbox) {
            $this->mbox = new \ilMailbox($this->user->getId());
        }

        $this->lng->loadLanguageModule('mail');
        $this->ctrl->saveParameter($this, 'mobj_id');
    }

    public function executeCommand()
    {
        if (!$this->settings->get('show_mail_settings')) {
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
     * @param \ilMailOptionsFormGUI $form
     */
    public function setForm(\ilMailOptionsFormGUI $form)
    {
        $this->form = $form;
    }

    /**
     * @return \ilMailOptionsFormGUI
     */
    protected function getForm()
    {
        if (null !== $this->form) {
            return $this->form;
        }

        return new \ilMailOptionsFormGUI(
            new \ilMailOptions($this->user->getId()),
            $this,
            'saveOptions'
        );
    }

    /**
     * Called if the user pushes the submit button of the mail options form.
     * Passes the post data to the mail options model instance to store them.
     */
    protected function saveOptions()
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        $form = $this->getForm();
        if ($form->save()) {
            ilUtil::sendSuccess($this->lng->txt('mail_options_saved'));
        }

        $this->showOptions($form);
    }

    /**
     * Called to display the mail options form
     * @param $form \ilMailOptionsFormGUI|null
     */
    protected function showOptions(\ilMailOptionsFormGUI $form = null)
    {
        if (null === $form) {
            $form = $this->getForm();
            $form->populate();
        } else {
            $form->setValuesByPost();
        }

        $this->tpl->setContent($form->getHTML());
        $this->tpl->show();
    }
}
