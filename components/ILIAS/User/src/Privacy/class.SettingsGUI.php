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

namespace ILIAS\User\Privacy;

use ILIAS\User\Context;
use ILIAS\User\Profile\PublicProfileGUI;
use ILIAS\User\Profile\ChecklistStatus;
use ILIAS\User\Profile\Visibility as ProfileVisibility;
use ILIAS\User\Settings\SettingsImplementation;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use Psr\Http\Message\ServerRequestInterface;

class SettingsGUI
{
    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAppEventHandler $event,
        private readonly ServerRequestInterface $request,
        private readonly \ilObjUser $user,
        private readonly \ilSetting $settings,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly SettingsImplementation $user_settings,
        private readonly ProfileVisibility $profile_mode,
        private readonly ChecklistStatus $checklist_status,
        private readonly \ilSetting $chat_settings,
        private readonly \ilSetting $notification_settings
    ) {
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('show') . 'Cmd';
        $this->$cmd();
        $this->tpl->printToStdout();
    }

    public function showCmd(
        ?StandardForm $form = null
    ): void {
        $this->tpl->setContent(
            $this->buildPrivacySettingsForm($form)
                . $this->buildPublicProfilePresentation()
                . $this->buildChatJsTemplate($this->tpl)
        );
    }

    public function saveCmd(): void
    {
        $form = $this->initForm()->withRequest($this->request);
        $form_data = $form->getData();

        $this->user_settings->

        $old_chat_data = [
            $this->user->get
        ];

        $this->user = $this->user_settings->saveForm(
            $form_data,
            [AvailablePages::PrivacySettings],
            Context::User,
            $this->user
        );

        if ($preferences_updated) {
            $this->event->raise(
                'components/ILIAS/Chatroom',
                'chatSettingsChanged',
                [
                    'user' => $this->user
                ]
            );
        }

        $this->user = $this->checklist_status->setStepSucessOnUser(
            ChecklistStatus::STEP_VISIBILITY_OPTIONS,
            $this->user
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirectByClass(self::class, '');
    }

    private function buildPrivacySettingsForm(
        ?StandardForm $form
    ): string {
        if ($form === null) {
            $form = $this->initForm();
        }
        return $this->ui_renderer->render($form);
    }

    private function buildPublicProfilePresentation(): string
    {
        if ($this->profile_mode->isEnabled()) {
            $pub_profile_legacy = $this->ui_factory->legacy()->content(
                (new PublicProfileGUI(
                    $this->user->getId()
                ))->getEmbeddable()
            );
            return $this->ui_renderer->render($this->ui_factory->panel()->standard(
                $this->lng->txt('user_profile_preview'),
                $pub_profile_legacy
            ));
        }

        if (!$this->checklist_status->anyVisibilitySettings()) {
            return $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt('usr_public_profile_disabled')
                )
            );
        }

        return '';
    }

    /**
     * @todo sk 2025-08-20: This is actually in the completely wrong place, but we
     * will leave it here for the time being until a good solution to initialize the
     * javascript without the need for a template is found.
     */
    private function buildChatJsTemplate(
        \ilGlobalTemplateInterface $global_template
    ): string {
        if (!$this->shouldShowOnScreenChatOptions()
            || $this->chat_settings->get('enable_browser_notifications', '0') !== '1') {
            return '';
        }
        $global_template->addJavaScript('assets/js/BrowserNotifications.min.js');
        $this->lng->toJSMap([
            'osc_browser_noti_no_permission_error' => $this->lng->txt('osc_browser_noti_no_permission_error'),
            'osc_browser_noti_no_support_error' => $this->lng->txt('osc_browser_noti_no_support_error'),
            'osc_browser_noti_req_permission_error' => $this->lng->txt('osc_browser_noti_req_permission_error'),
        ], $global_template);

        $tpl = new \ilTemplate('tpl.personal_chat_settings_form.html', true, true, 'components/ILIAS/Chatroom');
        $tpl->setVariable('ALERT_IMAGE_SRC', \ilUtil::getImagePath('standard/icon_alert.svg'));
        $tpl->setVariable('BROWSER_NOTIFICATION_TOGGLE_LABEL', $this->lng->txt('osc_enable_browser_notifications_label'));
        return $tpl->get();
    }

    private function initForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, 'save'),
            $this->user_settings->buildFormInputs(
                [AvailablePages::PrivacySettings],
                Context::User,
                $this->user
            )
        );
    }

    private function shouldShowOnScreenChatOptions(): bool
    {
        return $this->chat_settings->get('enable_osc', '0') &&
            $this->settings->get('usr_settings_hide_chat_osc_accept_msg', '0') !== 1;
    }

    private function shouldShowChatTypingBroadcastOption(): bool
    {
        return $this->settings->get('usr_settings_hide_chat_broadcast_typing', '0') !== '1';
    }

    public function shouldDisplayChatSection(): bool
    {
        return (bool) $this->chat_settings->get('chat_enabled', '0');
    }

    private function shouldShowNotificationOptions(): bool
    {
        return (bool) $this->notification_settings->get('osd_play_sound', '0');
    }

    public function shouldDisplayNotificationSection(): bool
    {
        return (bool) $this->notification_settings->get('enable_osd', '0');
    }
}
