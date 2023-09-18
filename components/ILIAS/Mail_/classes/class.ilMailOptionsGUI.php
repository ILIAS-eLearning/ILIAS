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

declare(strict_types=1);

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Jens Conze
 * @ingroup ServicesMail
 */
class ilMailOptionsGUI
{
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $lng;
    private readonly ilSetting $settings;
    private readonly ilObjUser $user;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilMailOptionsFormGUI $form;
    protected ilMailOptions $mail_options;

    public function __construct(
        ilGlobalTemplateInterface $tpl = null,
        ilCtrlInterface $ctrl = null,
        ilLanguage $lng = null,
        ilObjUser $user = null,
        GlobalHttpState $http = null,
        Refinery $refinery = null,
        ilMailOptions $mail_options = null
    ) {
        global $DIC;
        $this->tpl = $tpl ?? $DIC->ui()->mainTemplate();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
        $this->lng = $lng ?? $DIC->language();
        $this->user = $user ?? $DIC->user();
        $this->http = $http ?? $DIC->http();
        $this->refinery = $refinery ?? $DIC->refinery();
        $this->mail_options = $mail_options ?? new ilMailOptions($this->user->getId());

        $this->lng->loadLanguageModule('mail');
        $this->ctrl->saveParameter($this, 'mobj_id');
    }

    public function executeCommand(): void
    {
        if (!$this->mail_options->mayManageInvididualSettings()) {
            $referrer = '';
            if ($this->http->wrapper()->query()->has('referrer')) {
                $referrer = $this->http->wrapper()->query()->retrieve(
                    'referrer',
                    $this->refinery->kindlyTo()->string()
                );
            }
            if (strtolower(ilPersonalSettingsGUI::class) === strtolower($referrer)) {
                $this->ctrl->redirectByClass(ilPersonalSettingsGUI::class);
            }
            $this->ctrl->redirectByClass(ilMailGUI::class);
        }

        if (!($cmd = $this->ctrl->getCmd())) {
            $cmd = 'showOptions';
        }
        $this->$cmd();
    }

    public function setForm(ilMailOptionsFormGUI $form): void
    {
        $this->form = $form;
    }

    protected function getForm(): ilMailOptionsFormGUI
    {
        return $this->form ?? new ilMailOptionsFormGUI(
            $this->mail_options,
            $this,
            'saveOptions'
        );
    }

    protected function saveOptions(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        $form = $this->getForm();
        if ($form->save()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_options_saved'), true);
            $this->ctrl->redirect($this, 'showOptions');
        }

        $this->showOptions($form);
    }

    protected function showOptions(ilMailOptionsFormGUI $form = null): void
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
