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

namespace ILIAS\User\Account;

use ILIAS\User\Settings\PersonalSettingsGUI;
use ILIAS\DI\LoggingServices;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\User\StaticURLHandler;

class DeleteAccountGUI
{
    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly Language $lng,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly \ilToolbarGUI $toolbar,
        private readonly LoggingServices $log,
        private readonly \ilMailMimeSenderFactory $mail_sender_factory,
        private readonly \ilSetting $settings,
        private readonly \ilAuthSession $auth_session,
        private readonly \ilObjUser $current_user
    ) {
    }

    public function executeCommand(): void
    {
        $this->tpl->setTitle($this->lng->txt('user_delete_own_account'));
        $cmd = $this->ctrl->getCmd('deleteOwnAccountStep1');
        $this->$cmd();
    }

    protected function deleteOwnAccountStep1(): void
    {
        if (!(bool) $this->settings->get('user_delete_own_account') ||
            $this->current_user->getId() === SYSTEM_USER_ID) {
            $this->ctrl->redirectByClass(
                [\ilDashboardGUI::class, PersonalSettingsGUI::class],
                'show'
            );
        }

        // to make sure
        $this->current_user->removeDeletionFlag();

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('user_delete_own_account'),
            $this->lng->txt('user_delete_own_account_logout_confirmation'),
            $this->ctrl->getFormActionByClass(
                self::class,
                'deleteOwnAccountLogout'
            )
        )->withActionButtonLabel(
            $this->lng->txt('user_delete_own_account_logout_button')
        );

        $this->tpl->setOnScreenMessage(
            'info',
            $this->lng->txt('user_delete_own_account_info')
        );
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('btn_next'),
                $modal->getShowSignal()
            )
        );

        $this->tpl->setContent($this->ui_renderer->render($modal));

        $this->tpl->printToStdout();
    }

    protected function abortDeleteOwnAccount(): void
    {
        $this->current_user->removeDeletionFlag();

        $this->tpl->setOnScreenMessage(
            'info',
            $this->lng->txt('user_delete_own_account_aborted'),
            true
        );
        $this->ctrl->redirectByClass(
            [\ilDashboardGUI::class, PersonalSettingsGUI::class],
            'show'
        );
    }

    protected function deleteOwnAccountLogout(): void
    {
        $this->current_user->activateDeletionFlag();

        \ilSession::setClosingContext(\ilSession::SESSION_CLOSE_USER);
        $this->auth_session->logout();

        $this->ctrl->redirectToURL(
            'login.php?cmd=force_login&target=usr_'
                . StaticURLHandler::DEL_OWN_ACCOUNT_OPERATION
        );
    }

    protected function deleteOwnAccountStep2(): void
    {
        if (
            !(bool) $this->settings->get('user_delete_own_account') ||
            $this->current_user->getId() === SYSTEM_USER_ID ||
            !$this->current_user->hasDeletionFlag()
        ) {
            $this->ctrl->redirect($this, 'showGeneralSettings');
        }

        $this->tpl->setOnScreenMessage(
            'question',
            $this->lng->txt('user_delete_own_account_final_confirmation')
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('confirm'),
                $this->ctrl->getLinkTargetByClass(
                    self::class,
                    'deleteOwnAccountStep3'
                )
            )
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('cancel'),
                $this->ctrl->getLinkTargetByClass(
                    self::class,
                    'abortDeleteOwnAccount'
                )
            )
        );
        $this->tpl->printToStdout();
    }

    protected function deleteOwnAccountStep3(): void
    {
        if (
            !(bool) $this->settings->get('user_delete_own_account') ||
            $this->current_user->getId() === SYSTEM_USER_ID ||
            !$this->current_user->hasDeletionFlag()
        ) {
            $this->ctrl->redirect($this, 'showGeneralSettings');
        }

        // build notification

        $ntf = new \ilSystemNotification();
        $ntf->setLangModules(['user']);
        $ntf->addAdditionalInfo(
            'profile',
            $this->current_user->getProfileAsString($this->lng),
            true
        );

        // mail message
        \ilDatePresentation::setUseRelativeDates(false);
        $ntf->setIntroductionDirect(
            sprintf(
                $this->lng->txt('user_delete_own_account_email_body'),
                $this->current_user->getLogin(),
                ILIAS_HTTP_PATH,
                \ilDatePresentation::formatDate(
                    new \ilDateTime(
                        time(),
                        IL_CAL_UNIX
                    )
                )
            )
        );

        $message = $ntf->composeAndGetMessage(
            $this->current_user->getId(),
            null,
            'read',
            true
        );
        $subject = $this->lng->txt('user_delete_own_account_email_subject');

        // send notification
        $user_email = $this->current_user->getEmail();
        $admin_mail = $this->settings->get('user_delete_own_account_email');

        $mmail = new \ilMimeMail();
        $mmail->From($this->mail_sender_factory->system());

        if ($user_email !== '') {
            $mmail->To($user_email);
            $mmail->Bcc($admin_mail);
            $mmail->Subject($subject, true);
            $mmail->Body($message);
            $mmail->Send();
        } elseif ($admin_mail !== null || $admin_mail !== '') {
            $mmail->To($admin_mail);
            $mmail->Subject($subject, true);
            $mmail->Body($message);
            $mmail->Send();
        }

        $this->log->root()->log(
            'Account deleted: ' . $this->current_user->getLogin()
                . ' (' . $this->current_user->getId() . ')'
        );

        $this->current_user->delete();

        // terminate session
        $this->auth_session->logout();
        $this->ctrl->redirectToURL('login.php?accdel=1');
    }
}
